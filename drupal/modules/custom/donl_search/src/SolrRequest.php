<?php

namespace Drupal\donl_search;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\donl_search\Entity\SolrResult;
use Drupal\donl_value_list\ValueListInterface;
use Drupal\path_alias\AliasManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class SolrRequest implements SolrRequestInterface {

  /**
   * @var string
   */
  private $baseUrl;

  /**
   * @var string|null
   */
  private $password;

  /**
   * @var string|null
   */
  private $username;

  /**
   * @var string
   */
  private $searchCore;

  /**
   * @var string
   */
  private $suggestCore;

  /**
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private $logger;

  /**
   * @var string
   */
  private $languageCode;

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  private $valueList;

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private $request;

  /**
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  private $aliasManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeStorage;

  /**
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   *
   */
  public function __construct(Client $httpClient, ConfigFactoryInterface $configFactory, LoggerChannelFactory $loggerChannelFactory, LanguageManagerInterface $languageManager, ValueListInterface $valueList, RequestStack $requestStack, AliasManagerInterface $aliasManager, EntityTypeManagerInterface $entityTypeManager, TimeInterface $time) {
    $this->client = $httpClient;
    $this->logger = $loggerChannelFactory->get('solr_request');
    $this->languageCode = $languageManager->getCurrentLanguage()->getId();
    $this->valueList = $valueList;
    $this->request = $requestStack->getCurrentRequest();
    $this->aliasManager = $aliasManager;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->time = $time;

    // Set base URL (if exists in the config).
    if ($config = $configFactory->get('donl_search.sorl_request.settings')) {
      $this->baseUrl = $config->get('solr_url');
      if (substr($this->baseUrl, -1, 1) !== '/') {
        $this->baseUrl .= '/';
      }
      $this->password = $config->get('solr_password');
      $this->username = $config->get('solr_username');

      $this->searchCore = $config->get('solr_search_core');
      $this->suggestCore = $config->get('solr_suggest_core');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function search($page, $recordsPerPage, $search, $sort, $type = '', array $activeFacets = [], $sendSignals = FALSE): array {
    $query = $this->buildQuery($recordsPerPage, ($recordsPerPage * ($page - 1)), $search, $activeFacets);
    $selectHandler = $this->getSelectHandler($type);
    if ($sort) {
      $query['sort'] = $sort;
    }

    $numFound = 0;
    $rows = [];
    $facets = [];
    $suggestion = NULL;
    if ($result = $this->getRequest($this->searchCore, $selectHandler . '?' . http_build_query($query))) {
      $numFound = (int) $result['response']['numFound'];
      if ($sendSignals) {
        $this->sendSignals($selectHandler, $query, $numFound);
      }

      $rows = [];
      if (!empty($result['response']['docs'])) {
        foreach ($result['response']['docs'] as $doc) {
          $rows[] = new SolrResult($doc);
        }
      }

      if (!empty($result['facet_counts']['facet_fields'])) {
        foreach ($result['facet_counts']['facet_fields'] as $key => $values) {
          $facetValues = [];
          while (count($values)) {
            [$k, $v] = array_splice($values, 0, 2);
            $facetValues[$k] = $v;
          }
          $facets[$key] = $facetValues;
        }
      }

      if (!empty($result['facet_counts']['facet_intervals']['facet_recent'])) {
        $facetValues = [];
        foreach ($result['facet_counts']['facet_intervals']['facet_recent'] as $k => $v) {
          $k = str_replace(['[', ', ', ']'], ['', ' TO ', ''], $k);
          $facetValues[$k] = $v;
        }
        $facets['facet_recent'] = $facetValues;
      }

      if (!empty($result['spellcheck']['collations'])) {
        $collations = $result['spellcheck']['collations'];
        while (count($collations)) {
          $v = array_splice($collations, 0, 2)[1];
          if ($this->getSearchCount($v, $activeFacets)) {
            $suggestion = $v;
          }
        }
      }
    }

    return [
      'numFound' => $numFound,
      'rows' => $rows,
      'facets' => $facets,
      'suggestion' => $suggestion,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchCount($search, array $activeFacets = [],  $type = ''): int {
    $query = $this->buildQuery(0, 0, $search, $activeFacets);
    $query['facet'] = 'off';

    if ($result = $this->getRequest($this->searchCore, $this->getSelectHandler($type) . '?' . http_build_query($query))) {
      $numFound = (int) $result['response']['numFound'];
    }

    return $numFound;
  }

  /**
   *
   */
  public function getDatasetGroups($uri) {
    $query = [
      'q' => 'sys_uri:' . $uri,
      'rows' => 1,
      'start' => 0,
      'fl' => 'relation_group',
      'facet' => 'off',
    ];

    $groups = [];
    if ($result = $this->getRequest($this->searchCore, $this->getSelectHandler('') . '?' . http_build_query($query))) {
      if (!empty($result['response']['docs'][0]['relation_group'])) {
        $base = $this->request->getSchemeAndHttpHost();
        foreach ($result['response']['docs'][0]['relation_group'] as $relationGroup) {
          $path = $this->aliasManager->getPathByAlias(str_replace($base, '', $relationGroup));
          if (preg_match('/node\/(\d+)/', $path, $matches)) {
            /** @var \Drupal\node\Entity\Node $node */
            $node = $this->nodeStorage->load($matches[1]);
            $groups[$node->get('machine_name')->getValue()[0]['value']] = $node->getTitle();
          }
        }
      }
    }

    return $groups;
  }

  /**
   * {@inheritdoc}
   */
  public function getHierarchicalThemeListWithUsages(): array {
    $themes = $this->valueList->getHierarchicalThemeList();
    $query = $this->buildQuery(0, 0, NULL, []);
    $result = $this->getRequest($this->searchCore, $this->getSelectHandler('dataset') . '?' . http_build_query($query));

    if (isset($result['facet_counts']['facet_fields']['facet_theme'])) {
      $values = $result['facet_counts']['facet_fields']['facet_theme'];
      $usages = [];
      while (count($values)) {
        [$k, $v] = array_splice($values, 0, 2);
        $usages[$k] = $v;
      }
    }

    foreach ($themes as $k => $v) {
      $themes[$k]['count'] = $usages[$k] ?? 0;
      foreach ($v['children'] as $kc => $vc) {
        $themes[$k]['children'][$kc]['count'] = $usages[$kc] ?? 0;
      }
    }

    return $themes;
  }

  /**
   * {@inheritdoc}
   */
  public function getCountForSelectedThemes(array $selectedThemes, $communityIdentifier = NULL): array {
    $query = [
      'rows' => 0,
      'start' => 0,
      'facet.limit' => -1,
    ];

    // If there is a community identifier given, use it.
    if ($communityIdentifier !== NULL) {
      $query['q'] = 'facet_community:"' . $communityIdentifier . '"';
    }

    $themes = [];
    foreach ($selectedThemes as $uri) {
      $themes[$uri] = 0;
    }

    $result = $this->getRequest($this->searchCore, $this->getSelectHandler('dataset') . '?' . http_build_query($query));

    if (isset($result['facet_counts']['facet_fields']['facet_theme'])) {
      $values = $result['facet_counts']['facet_fields']['facet_theme'];
      while (count($values)) {
        [$k, $v] = array_splice($values, 0, 2);
        $explode = explode('|', $k);
        $k = end($explode);
        if (isset($themes[$k])) {
          $themes[$k] = $v;
        }
      }
    }

    return $themes;
  }

  /**
   * {@inheritdoc}
   */
  public function getTagCloud($communityIdentifier = NULL): array {
    $activeFacets = [];
    if ($communityIdentifier !== NULL) {
      $activeFacets['facet_community'][] = $communityIdentifier;
    }

    $query = $this->buildQuery(0, 0, NULL, $activeFacets);

    $tags = [];
    if ($result = $this->getRequest($this->searchCore, $this->getSelectHandler('dataset') . '?' . http_build_query($query))) {
      if (isset($result['facet_counts']['facet_fields']['facet_keyword'])) {
        $resultTags = [];
        while (count($result['facet_counts']['facet_fields']['facet_keyword'])) {
          [$k, $v] = array_splice($result['facet_counts']['facet_fields']['facet_keyword'], 0, 2);
          $resultTags[$k] = $v;
        }

        // Sort by count.
        arsort($resultTags);
        foreach ($resultTags as $tag => $number) {
          $tags[] = $tag;
        }
      }
    }

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getComparableData($type, $id): array {
    $matches = [];
    if ($result = $this->getRequest($this->searchCore, 'related_content?q=sys_id:' . $id . '&fq=sys_type:' . $type)) {
      if (!empty($result['response']['docs'])) {
        foreach ($result['response']['docs'] as $doc) {
          if ($type === 'dataset') {
            // If in some way we don't get back a dataset as answer we'll skip
            // it to prevent errors in the code.
            if ($doc['sys_type'] === 'dataset') {
              $matches[$doc['sys_name']] = $doc['title'];
            }
          }
          else {
            $matches[$doc['sys_id']] = $doc['title'];
          }
        }
      }
    }

    return $matches;
  }

  /**
   * {@inheritdoc}
   */
  public function autocomplete($type, $search): array {
    $matches = [];
    if ($response = $this->getRequest($this->searchCore, 'autocomplete_identifier?fq=sys_type:' . $type . '&q=title:' . $search)) {
      if (!empty($response['response']['docs'])) {
        foreach ($response['response']['docs'] as $doc) {
          $matches[] = [
            'value' => $doc['sys_uri'],
            'label' => $doc['title'],
          ];
        }
      }
    }

    return $matches;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchSuggestions($search): array {
    $suggestions = [];
    if ($response = $this->getRequest($this->suggestCore, 'suggest_dataset?q=' . $search . '&suggest.cfq=' . $this->languageCode . '&wt=json')) {
      $suggestions['dataset_suggester'] = $response['suggest']['dataset_suggester'][$search]['suggestions'];
      $suggestions['organization_suggester'] = $response['suggest']['organization_suggester'][$search]['suggestions'];
      $suggestions['theme_suggester'] = $response['suggest']['theme_suggester'][$search]['suggestions'];
    }
    return $suggestions;
  }

  /**
   * {@inheritdoc}
   */
  public function updateIndex($document) {
    // Adding "?commit=true" makes sure the document is index immediately.
    return $this->postRequest($this->searchCore, 'update/json/docs?commit=true', $document);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIndex($id) {
    $body = [
      'delete' => [
        'id' => $id,
      ],
    ];

    // Adding "?commit=true" makes sure the document is index immediately.
    return $this->postRequest($this->searchCore, 'update/?commit=true', json_encode($body));
  }

  /**
   * @param int $rows
   * @param int $start
   * @param string $search
   * @param array $activeFacets
   *
   * @return array
   */
  private function buildQuery($rows, $start, $search, array $activeFacets): array {
    $query = [
      'q' => '*:*',
      'rows' => ($rows >= 0 ? $rows : 0),
      'start' => ($start >= 0 ? $start : 0),
    ];

    if (!empty($search)) {
      $query['q'] = trim($search);
    }

    if (!empty($activeFacets)) {
      $fq = [];

      if (isset($activeFacets['facet_recent']) && is_array($activeFacets['facet_recent'])) {
        foreach ($activeFacets['facet_recent'] as $v) {
          $fq[] = 'sys_modified:{' . $v . '}';
        }
        unset($activeFacets['facet_recent']);
      }

      foreach ($activeFacets as $k => $values) {
        if (is_array($values)) {
          foreach ($values as $v) {
            $fq[] = $k . ':"' . $v . '"';
          }
        }
      }
      $query['fq'] = implode(' AND ', $fq);
    }

    return $query;
  }

  /**
   * Get the correct select handler.
   *
   * @param string $type
   *   The type of data we are searching.
   *
   * @return string
   */
  private function getSelectHandler($type): string {
    switch ($type) {
      case 'application':
        return 'select_appliance';

      case 'catalog':
      case 'community':
      case 'datarequest':
      case 'dataset':
      case 'group':
      case 'news':
      case 'organization':
      case 'support':
        return 'select_' . $type;
    }

    return 'select';
  }

  /**
   * Execute a get request and format the result.
   *
   * @param string $core
   * @param string $action
   * @param array $options
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  private function getRequest($core, $action, array $options = []) {
    $options = array_replace_recursive([
      'headers' => [
        'Accept' => 'application/json',
      ],
      'timeout' => 5,
    ], $options);

    $url = $this->baseUrl . $core . '/' . $action;
    return $this->execute('GET', $url, $options);
  }

  /**
   * Execute a get request and format the result.
   *
   * @param string $core
   * @param string $action
   * @param string $body
   * @param array $options
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  private function postRequest($core, $action, $body, array $options = []) {
    $options = array_replace_recursive([
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ],
      'timeout' => 5,
      'body' => $body,
    ], $options);

    $url = $this->baseUrl . $core . '/' . $action;
    return $this->execute('POST', $url, $options);
  }

  /**
   * Send signals to SOLR
   *
   * @param string $selectHandler
   * @param array $query
   * @param int $numFound
   */
  private function sendSignals(string $selectHandler, array $query, int $numFound): void {
    $body = [
      [
        'signal_type' => 'response',
        'handler' => $selectHandler,
        'query' => $query['q'] ?? '',
        'filters' => $query['fq'] ?? '',
        'start' => $query['start'] ?? '',
        'rows' => $query['rows'] ?? '',
        'result_count' => $numFound,
        'search_timestamp' => date('Y-m-d\TH:i:s\Z', $this->time->getRequestTime()),
      ],
    ];
    $this->postRequest('donl_signals', 'update', json_encode($body), ['timeout' => 1]);
  }

  /**
   * Execute the request and format the result.
   *
   * @param string $method
   * @param string $url
   * @param array $options
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  private function execute($method, $url, array $options) {
    try {
      // Add authentication if set.
      if ($this->username && $this->password) {
        $options['auth'] = [$this->username, $this->password];
      }

      if ($method === 'POST') {
        $response = $this->client->post($url, $options);
      }
      else {
        $response = $this->client->get($url, $options);
      }
      return json_decode($response->getBody(), TRUE);
    }
    catch (BadResponseException $e) {
      $error = json_decode($e->getResponse()->getBody());

      $errorMsg = FALSE;
      if (isset($error->error->msg)) {
        $errorMsg = 'http ' . $e->getCode() . ': ' . $error->error->msg;
      }
      $this->logger->error(($errorMsg ?: 'Unexpected error, could not retrieve error message.'));
    }
    catch (\Exception $e) {
      $this->logger->error('Error: ' . $e->getMessage());
    }

    return FALSE;
  }

}
