<?php

namespace Drupal\ckan;

use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\Entity\Catalog;
use Drupal\ckan\Entity\Resource;
use Drupal\ckan\Entity\Tag;
use Drupal\ckan\Entity\User;
use Drupal\ckan\User\CkanUserInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Session\AccountProxyInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 *
 */
class CkanRequest implements CkanRequestInterface {

  /**
   * @var string
   */
  private $baseUrl;

  /**
   * @var string
   */
  private $apiKey;

  /**
   * @var array
   */
  private $errorResponse;

  /**
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private $logger;

  /**
   * @var \Drupal\ckan\User\CkanUserInterface
   */
  private $ckanUser;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeStorage;

  /**
   * @var int
   */
  private $previewFunctionality;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $userStorage;

  /**
   *
   */
  public function __construct(Client $httpClient, ConfigFactoryInterface $configFactory, LoggerChannelFactory $loggerChannelFactory, CacheBackendInterface $cacheBackend, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->client = $httpClient;
    $this->logger = $loggerChannelFactory->get('ckan_request');
    $this->cacheBackend = $cacheBackend;
    $this->currentUser = $currentUser;

    // Set base URL (if exists in the config).
    if ($config = $configFactory->get('ckan.request.settings')) {
      $this->previewFunctionality = $config->get('preview_functionality');
      if ($ckanUrl = $config->get('ckan_url')) {
        $this->baseUrl = $ckanUrl;
      }
      if ($ckanApiKey = $config->get('ckan_api_key')) {
        $this->apiKey = $ckanApiKey;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setCkanUser(CkanUserInterface $ckanUser = NULL) {
    $this->ckanUser = $ckanUser;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataset($datasetId): ?Dataset {
    $dataset = &drupal_static('dataset:' . $datasetId);
    if (!$dataset) {
      $options['headers']['Content-Type'] = 'application/json';

      if ($user = $this->userStorage->load($this->currentUser->id())) {
        $options['headers']['Authorization'] = $user->getApiKey('dataset');
      }

      if ($result = $this->execute('get', 'package_show?id=' . $datasetId, $options)) {
        $dataset = $this->resultToDataset($result);
      }
    }
    return $dataset;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasetAsRdf($datasetId) {
    if ($result = $this->execute('get', 'rdf_package_show?id=' . $datasetId)) {
      return $result;
    }

    return NULL;
  }

  /**
   * Find a dataset based on the identifier.
   *
   * Preferably we want to use the SOLR implementation, but in case you'll need
   * the full dataset you must use this function.
   *
   * @param string $identifier
   *
   * @return Dataset|null
   *
   * @see \Drupal\donl_search\SolrRequestInterface::getDatasetResultByIdentifier
   */
  public function getDatasetByIdentifier($identifier): ?Dataset {
    $query = [
      'q' => '* AND identifier:"' . $identifier . '"',
      'rows' => 1,
      'facet' => 'false',
    ];

    if ($response = $this->execute('get', 'package_search?' . http_build_query($query))) {
      if (isset($response->results[0])) {
        return $this->resultToDataset($response->results[0]);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasetByUser(string $userId, int $page = NULL, int $recordsPerPage = NULL): array {
    $query = [
      'q' => 'creator_user_id:' . $userId,
    ];

    $datasets = [];
    if ($result = $this->execute('get', 'package_search?' . http_build_query($query))) {
      foreach ($result->results as $v) {
        $datasets[] = $this->resultToDataset($v);
      }
    }

    return $datasets;
  }

  /**
   * {@inheritdoc}
   */
  public function searchDatasets($page, $recordsPerPage, $search, $sort, array $activeFacets, array $extraFacets = []) {
    $count = 0;
    $datasets = [];
    $facets = [];

    $fq = [];
    foreach ($activeFacets as $k => $values) {
      if (\is_array($values)) {
        foreach ($values as $v) {
          $fq[] = $k . ':"' . $v . '"';
        }
      }
    }

    $query = [
      'q' => $search . (!empty($search) && !empty($fq) ? ' AND ' : '') . implode(' AND ', $fq),
      'sort' => $sort,
      'rows' => $recordsPerPage,
      'start' => $recordsPerPage * ($page - 1),
      'facet' => 'true',
      'facet.limit' => -1,
    ];

    if (!empty($extraFacets)) {
      $query['facet.field'] = '["' . implode('","', $extraFacets) . '"]';
      $query['facet.mincount'] = 0;
    }

    if ($result = $this->execute('get', 'package_search?' . http_build_query($query))) {
      $count = $result->count;

      foreach ($result->results as $v) {
        $datasets[] = $this->resultToDataset($v);
      }

      foreach ($result->facets as $k => $v) {
        $facets[$k] = (array) $v;
        // We want the facets with the most results on top. For some reason CKAN
        // gives us back the list in a random order.
        arsort($facets[$k]);
      }

      // Remove facet communities from list (different design).
      unset($facets['facet_communities']);

      // CKAN doesn't remove active facets from the list, so we'll do it for
      // them.
      foreach ($activeFacets as $k => $values) {
        if (\is_array($values)) {
          foreach ($values as $v) {
            unset($facets[$k][$v]);
          }
        }
      }
    }

    return [
      'count' => $count,
      'datasets' => $datasets,
      'facets' => $facets,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function totalDatasets(): int {
    $cid = 'ckan.total.datasets';

    // Return cached version.
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }

    $query = [
      'rows' => 0,
      'start' => 0,
      'facet' => 'false',
    ];

    $count = 0;
    if ($result = $this->execute('get', 'package_search?' . http_build_query($query))) {
      $count = $result->count ?? 0;
      $this->cacheBackend->set($cid, $count, strtotime('+5 minutes'));
    }

    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemes($communityIdentifier = NULL): array {
    $query = [
      'rows' => 0,
      'start' => 0,
      'facet' => 'true',
      'facet.limit' => -1,
    ];

    // If there is a community identifier given, use it.
    if ($communityIdentifier !== NULL) {
      $query['q'] = 'facet_communities:"' . $communityIdentifier . '"';
    }

    $themes = [];

    if ($result = $this->execute('get', 'package_search?' . http_build_query($query))) {
      if (isset($result->facets->facet_theme)) {
        $resultThemes = (array) $result->facets->facet_theme;

        foreach ($resultThemes as $themeLabels => $themeCount) {
          $themeLabels = explode('|', $themeLabels);

          if (\count($themeLabels)) {
            if (!isset($themes[$themeLabels[0]])) {
              $themes[$themeLabels[0]] = [
                'count' => 0,
                'children' => [],
              ];
            }

            if (\count($themeLabels) > 1) {
              $themes[$themeLabels[0]]['children'][$themeLabels[1]]['count'] = $themeCount;
            }
            else {
              $themes[$themeLabels[0]]['count'] = $themeCount;
            }
          }
        }

        // Sort themes.
        uasort($themes, static function ($a, $b) {
          return $b['count'] <=> $a['count'];
        });

        // Sort subthemes.
        foreach ($themes as &$theme) {
          uasort($theme['children'], static function ($a, $b) {
            return $b['count'] <=> $a['count'];
          });
        }
      }
    }

    return $themes;
  }

  /**
   * {@inheritdoc}
   */
  public function createDataset(Dataset $dataset) {
    if ($this->ckanUser) {
      $ownerOrg = NULL;

      // The default owner_org is data.overheid itself.
      if ($organizationId = $this->getOrganizationIdFromCatalog('https://data.overheid.nl')) {
        if ($organization = $this->getCatalog($organizationId)) {
          $ownerOrg = $organization->getId();
        }
      }

      // Now attempt to set the owner_org to the selected source_catalog.
      if (($catalog = $dataset->getSourceCatalog()) && !empty($this->ckanUser->getCatalogs()[$catalog])) {
        if ($organizationId = $this->getOrganizationIdFromCatalog($catalog)) {
          if ($organization = $this->getCatalog($organizationId)) {
            $ownerOrg = $organization->getId();
          }
        }
      }

      if ($ownerOrg) {
        $dataset->setOwnerOrg($ownerOrg);
      }
    }

    // Remove all NULL values.
    $body = json_encode(array_filter($dataset->toArray()));

    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('dataset'),
      ],
      'body' => $body,
    ];

    return $this->execute('post', 'package_create', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function updateDataset(Dataset $dataset) {
    $dataArray = array_filter($dataset->toArray());
    $dataArray['private'] = $dataset->getPrivate();
    // Remove all NULL values.
    $body = json_encode($dataArray);

    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('dataset'),
      ],
      'body' => $body,
    ];
    return $this->execute('post', 'package_update', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDataset($datasetId) {
    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('dataset'),
      ],
      'body' => json_encode(['id' => $datasetId]),
    ];
    return $this->execute('post', 'package_delete', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getResource($resourceId) {
    $resource = &drupal_static('resource:' . $resourceId);
    if (!$resource) {
      $options['headers']['Content-Type'] = 'application/json';

      if ($user = $this->userStorage->load($this->currentUser->id())) {
        $options['headers']['Authorization'] = $user->getApiKey('dataset');
      }

      if ($result = $this->execute('get', 'resource_show?id=' . $resourceId, $options)) {
        $resource = $this->resultToResource($result);
      }
    }
    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public function createResource(Resource $resource) {
    // Remove all NULL values.
    $body = json_encode(array_filter($resource->toArray()));

    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('dataset'),
      ],
      'body' => $body,
    ];
    return $this->execute('post', 'resource_create', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function updateResource(Resource $resource) {
    // Remove all NULL values.
    $body = json_encode(array_filter($resource->toArray()));

    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('dataset'),
      ],
      'body' => $body,
    ];
    return $this->execute('post', 'resource_update', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteResource($resourceId) {
    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('dataset'),
      ],
      'body' => json_encode(['id' => $resourceId]),
    ];
    return $this->execute('post', 'resource_delete', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getCatalog($catalogId) {
    if ($result = $this->execute('get', 'organization_show?id=' . $catalogId)) {
      return $this->resultToCatalog($result);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getUser($userId): ?User {
    if ($result = $this->execute('get', 'user_show?id=' . $userId)) {
      return $this->resultToUser($result);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function updateUser(User $user) {
    // Remove all NULL values.
    $body = json_encode(array_filter($user->toArray()));

    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('user'),
      ],
      'body' => $body,
    ];

    if ($result = $this->execute('post', 'user_update', $options)) {
      $user->setId($result->id);
      $user->setApikey($result->apikey);
      $user->setState($result->state);
    }

    return $result ? $user : $result;
  }

  /**
   * {@inheritdoc}
   */
  public function createUser(User $user) {
    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('user'),
      ],
      'body' => json_encode([
        'fullname' => $user->fullName,
        'name' => $user->name,
        'email' => $user->email,
        'password' => user_password(18),
      ]),
    ];
    if ($result = $this->execute('post', 'user_create', $options)) {
      $user->setId($result->id);
      $user->setApikey($result->apikey);
      $user->setState($result->state);
    }

    return $result ? $user : $result;
  }

  /**
   * {@inheritdoc}
   */
  public function isErrorUserAlreadyExists(): bool {
    return isset($this->errorResponse['name'][0]) && $this->errorResponse['name'][0] === 'That login name is not available.';
  }

  /**
   * {@inheritdoc}
   */
  public function deleteUser(User $user) {
    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('user'),
      ],
      'body' => json_encode([
        'id' => $user->id,
      ]),
    ];

    return $this->execute('post', 'user_delete', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function activateUser(User $user, $catalog = NULL) {
    $organizations = [];

    // Get default data overheid organization.
    if ($organization = $this->getCatalog('data-overheid-nl')) {
      $organizations['data-overheid-nl'] = $organization;
    }

    // Get organization by catalog parameter.
    if ($catalog && ($organizationId = $this->getOrganizationIdFromCatalog($catalog))) {
      if ($organization = $this->getCatalog($organizationId)) {
        $organizations[$organizationId] = $organization;
      }
    }

    // Add memberships.
    foreach ($organizations as $organization) {
      $this->organizationAddMember($user, $organization);
    }

    // Activate user if needed.
    $user->setState('active');
    $user->setDeleted(FALSE);

    return $this->updateUser($user);
  }

  /**
   * {@inheritdoc}
   */
  public function blockUser(User $user) {
    // Get all organization the user is member of and remove the membership.
    foreach ($this->getUserOrganizations($user) as $organization) {
      $this->organizationRemoveMember($user, $organization);
    }

    // Block user.
    $user->setState('deleted');
    $user->setDeleted(TRUE);

    return $this->updateUser($user);
  }

  /**
   * {@inheritdoc}
   */
  public function getUserOrganizations(User $user): array {
    $organizations = [];
    if ($result = $this->execute('get', 'organization_list_for_user?id=' . $user->id . '&include_dataset_count=true')) {
      foreach ($result as $v) {
        $organizations[] = $this->resultToCatalog($v);
      }
    }

    return $organizations;
  }

  /**
   * {@inheritdoc}
   */
  public function organizationAddMember(User $user, Catalog $organization, $role = 'admin') {
    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('user'),
      ],
      'body' => json_encode([
        'id' => $organization->id,
        'username' => $user->name,
        'role' => $role,
      ]),
    ];

    $this->execute('post', 'organization_member_create', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function organizationRemoveMember(User $user, Catalog $organization) {
    $options = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => $this->getApiKey('user'),
      ],
      'body' => json_encode([
        'id' => $organization->id,
        'username' => $user->name,
      ]),
    ];

    $this->execute('post', 'organization_member_delete', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors(): array {
    return $this->errorResponse;
  }

  /**
   * Transform the CKAN json response into a Dataset object.
   *
   * @param object $result
   *
   * @return \Drupal\ckan\Entity\Dataset
   */
  private function resultToDataset(\stdClass $result): Dataset {
    $dataset = new Dataset();

    // Required fields which can't be empty.
    $dataset->setId($result->id);
    $dataset->setOwnerOrg($result->owner_org);
    $dataset->setIdentifier($result->identifier);
    $dataset->setLanguage($result->language);
    $dataset->setAuthority($result->authority);
    $dataset->setPublisher($result->publisher);
    $dataset->setContactPointName($result->contact_point_name);
    $dataset->setName($result->name);
    $dataset->setTitle($result->title);
    $dataset->setNotes($result->notes);
    $dataset->setMetadataLanguage($result->metadata_language);
    $dataset->setMetadataModified($result->metadata_modified);
    $dataset->setTheme($result->theme);
    $dataset->setModified($result->modified);
    $dataset->setLicenseId($result->license_id);
    $dataset->setPrivate((bool) $result->private);
    $dataset->setHighValue(isset($result->high_value) && strtolower($result->high_value) === 'true');
    $dataset->setBaseRegister(isset($result->basis_register) && strtolower($result->basis_register) === 'true');
    $dataset->setReferenceData(isset($result->referentie_data) && strtolower($result->referentie_data) === 'true');
    $dataset->setNationalCoverage(isset($result->national_coverage) && strtolower($result->national_coverage) === 'true');
    $dataset->setSectorRegistrations(isset($result->sector_registrations) && strtolower($result->sector_registrations) === 'true');
    $dataset->setLocalRegistrations(isset($result->local_registrations) && strtolower($result->local_registrations) === 'true');

    // Resources (optional).
    $resources = [];
    if (!empty($result->resources)) {
      foreach ($result->resources as $v) {
        $resources[] = $this->resultToResource($v);
      }
    }
    $dataset->setResources($resources);

    // Tags (optional).
    $tags = [];
    if (!empty($result->tags)) {
      foreach ($result->tags as $v) {
        $tag = new Tag();
        $tag->setId($v->id);
        $tag->setName($v->name);
        $tags[] = $tag;
      }
    }
    $dataset->setTags($tags);

    if (!empty($result->organization)) {
      $catalog = $this->resultToCatalog($result->organization);
      $dataset->setCatalog($catalog);
    }

    // Optional fields.
    $dataset->setAlternateIdentifier($result->alternate_identifier ?? []);
    $dataset->setSourceCatalog($result->source_catalog ?? NULL);
    $dataset->setContactPointAddress($result->contact_point_address ?? NULL);
    $dataset->setContactPointEmail($result->contact_point_email ?? NULL);
    $dataset->setContactPointPhone($result->contact_point_phone ?? NULL);
    $dataset->setContactPointWebsite($result->contact_point_website ?? NULL);
    $dataset->setContactPointTitle($result->contact_point_title ?? NULL);
    $dataset->setAccessRights($result->access_rights ?? NULL);
    $dataset->setAccessRightsReason($result->access_rights_reason ?? NULL);
    $dataset->setUrl($result->url ?? NULL);
    $dataset->setConformsTo($result->conforms_to ?? []);
    $dataset->setRelatedResource($result->related_resource ?? []);
    $dataset->setSource($result->source ?? []);
    $dataset->setVersion($result->version ?? NULL);
    $dataset->setHasVersion($result->has_verison ?? []);
    $dataset->setIsVersionOf($result->is_version_of ?? []);
    $dataset->setLegalFoundationRef($result->legal_foundation_ref ?? NULL);
    $dataset->setLegalFoundationUri($result->legal_foundation_uri ?? NULL);
    $dataset->setLegalFoundationLabel($result->legal_foundation_label ?? NULL);
    $dataset->setFrequency($result->frequency ?? NULL);
    $dataset->setProvenance($result->provenance ?? []);
    $dataset->setSample($result->sample ?? []);
    $dataset->setSpatialScheme($result->spatial_scheme ?? []);
    $dataset->setSpatialValue($result->spatial_value ?? []);
    $dataset->setTemporalLabel($result->temporal_label ?? NULL);
    $dataset->setTemporalStart($result->temporal_start ?? NULL);
    $dataset->setTemporalEnd($result->temporal_end ?? NULL);
    $dataset->setDatasetStatus($result->dataset_status ?? NULL);
    $dataset->setDatePlanned($result->date_planned ?? NULL);
    $dataset->setVersionNotes($result->version_notes ?? []);
    $dataset->setIssued($result->issued ?? NULL);
    $dataset->setDocumentation($result->documentation ?? []);
    $dataset->setDatasetQuality($result->dataset_quality ?? NULL);
    $dataset->setCreatorUserId($result->creator_user_id ?? NULL);
    $dataset->setRestrictionsStatement($result->restrictions_statement ?? NULL);

    return $dataset;
  }

  /**
   * Transform the CKAN json response into a Resource object.
   *
   * @param object $result
   *
   * @return \Drupal\ckan\Entity\Resource
   */
  private function resultToResource(\stdClass $result): Resource {
    $resource = new Resource();

    // Required fields which can't be empty.
    $resource->setId($result->id);
    $resource->setPackageId($result->package_id);
    $resource->setUrl($result->url);
    $resource->setName($result->name);
    $resource->setDescription($result->description);
    $resource->setMetadataLanguage($result->metadata_language);
    // This should always be an array so this check shouldn't be required.
    $resource->setLanguage((is_array($result->language) ? $result->language : [$result->language]));
    $resource->setFormat($result->format);
    $resource->setLicenseId($result->license_id);

    // Optional fields.
    $resource->setPosition($result->position ?? NULL);
    $resource->setSize($result->size ?? NULL);
    $resource->setDownloadUrl($result->download_url ?? NULL);
    $resource->setMimetype($result->mimetype ?? NULL);
    $resource->setReleaseDate($result->release_date ?? NULL);
    $resource->setRights($result->rights ?? NULL);
    $resource->setStatus($result->status ?? NULL);
    $resource->setLinkStatus(isset($result->link_status) && $result->link_status == 1);
    $resource->setLinkStatusLastChecked($result->link_status_last_checked ?? NULL);
    $resource->setModificationDate($result->modification_date ?? NULL);
    $resource->setLinkedSchemas($result->linked_schemas ?? NULL);
    $resource->setHash($result->hash ?? NULL);
    $resource->setHashAlgorithm($result->hash_algorithm ?? NULL);
    $resource->setDocumentation($result->documentation ?? NULL);
    $resource->setCreated($result->created ?? NULL);
    $resource->setDistributionType($result->distribution_type ?? NULL);
    if ($this->previewFunctionality) {
      $this->checkPreviewUrl($resource);
    }

    return $resource;
  }

  /**
   * Transform the CKAN json response into a Organization object.
   *
   * @param object $result
   *
   * @return \Drupal\ckan\Entity\Catalog
   */
  private function resultToCatalog(\stdClass $result): Catalog {
    $catalog = new Catalog();

    // Required fields which can't be empty.
    $catalog->setId($result->id);
    $catalog->setName($result->name);
    $catalog->setDescription($result->description);
    $catalog->setTitle($result->title);

    return $catalog;
  }

  /**
   * @param object $result
   *
   * @return \Drupal\ckan\Entity\User
   */
  private function resultToUser(\stdClass $result): User {
    $user = new User();

    $user->setId($result->id);
    $user->setName($result->name);
    $user->setFullName($result->fullname ?? NULL);
    $user->setState($result->state);

    return $user;
  }

  /**
   * Execute the request and format the result.
   *
   * @param string $type
   * @param string $action
   * @param array $options
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  private function execute($type, $action, array $options = []) {
    if (!$this->baseUrl) {
      $this->logger->error('Error: Base URL is not set');
      return FALSE;
    }

    $this->errorResponse = [];
    try {
      $options = array_merge_recursive($options, [
        'headers' => [
          'Accept' => 'application/json',
        ],
        'timeout' => 5,
      ]);
      $url = $this->baseUrl . $action;

      switch ($type) {
        case 'post':
          $response = $this->client->post($url, $options);
          break;

        case 'get':
        default:
          $response = $this->client->get($url, $options);
          break;
      }

      $response = json_decode($response->getBody());
    }
    catch (BadResponseException $e) {
      if ($errorResponse = $e->getResponse()) {
        if ($errorResponse->getStatusCode() !== 404) {
          $result = json_decode($errorResponse->getBody()->getContents(), FALSE);
          if (isset($result->error)) {
            $error = (array) $result->error;
            unset($error['__type']);
            $this->errorResponse = $error;
            $this->logger->error('Error: ' . json_encode($error));
          }
        }
        else {
          $this->logger->warning('Warning: ' . $e->getMessage());
        }
      }
      else {
        $this->logger->error('Error: ' . $e->getMessage());
      }
      return FALSE;
    }

    // Check if the call was successful according to CKAN.
    if (isset($response->success, $response->result) && $response->success) {
      return $response->result;
    }

    if (isset($response->error)) {
      $error = (array) $response->error;
      unset($error['__type']);
      $this->errorResponse = $error;
      $this->logger->error('Error: ' . json_encode($error));
    }

    return FALSE;
  }

  /**
   * Return the API key for the current logged in user.
   *
   * @param string|null $entityType
   *   The entityType on which we are trying to preform the action.
   *
   * @return null|string
   */
  private function getApiKey(?string $entityType = NULL) {
    if ($this->apiKey && $this->ckanUser) {
      if ($entityType === 'dataset' && $this->ckanUser->hasPermission('manage all datasets')) {
        return $this->apiKey;
      }
      elseif ($entityType === 'user' && $this->ckanUser->hasPermission('administer users')) {
        return $this->apiKey;
      }
    }

    if ($this->ckanUser) {
      return $this->ckanUser->getApiKey();
    }

    return NULL;
  }

  /**
   * Checks if the preview url is valid.
   *
   * @param \Drupal\ckan\Entity\Resource $resource
   */
  private function checkPreviewUrl(Resource $resource): void {
    if ($resource->getUrl() && $resource->getFormat() === 'http://publications.europa.eu/resource/authority/file-type/CSV' && strpos($resource->getUrl(), '.csv') > -1) {
      $resource->setPreviewUrl(preg_replace('(^https?:)', '', $resource->getUrl()));
    }
  }

  /**
   * Temporary mapping function for catalog to organisation id.
   *
   * @param string $catalog
   *
   * @return null|string
   */
  private function getOrganizationIdFromCatalog($catalog) {
    if (empty($catalog)) {
      return '';
    }

    $mapping = ['https://data.overheid.nl' => 'data-overheid-nl'];
    $nodes = $this->nodeStorage->loadByProperties([
      'type' => 'catalog',
      'status' => 1,
    ]);
    /** @var \Drupal\node\Entity\Node $node */
    foreach ($nodes as $node) {
      if ($node->hasField('identifier') && $node->hasField('ckan_organization_mapping')) {
        $identifier = $node->get('identifier')->getValue()[0]['value'] ?? NULL;
        $value = $node->get('ckan_organization_mapping')->getValue()[0]['value'] ?? NULL;
        if ($identifier && $value) {
          $mapping[$identifier] = $value;
        }
      }
    }

    return $mapping[$catalog] ?? NULL;
  }

}
