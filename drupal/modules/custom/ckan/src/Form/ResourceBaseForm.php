<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\CkanRequestInterface;
use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\Entity\Resource;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\donl_value_list\ValueListInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
abstract class ResourceBaseForm extends BaseForm {

  /**
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(CkanRequestInterface $ckanRequest, ValueListInterface $valueList, MessengerInterface $messenger, EntityTypeManagerInterface $entityManager, FileUsageInterface $fileUsage, RequestStack $request, ConfigFactoryInterface $configFactory) {
    parent::__construct($ckanRequest, $valueList, $messenger, $entityManager, $request);

    $this->fileUsage = $fileUsage;
    $this->fileStorage = $entityManager->getStorage('file');
    $this->config = $configFactory->get('ckan.request.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request'),
      $container->get('donl.value_list'),
      $container->get('messenger'),
      $container->get('entity.manager'),
      $container->get('file.usage'),
      $container->get('request_stack'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL, Resource $resource = NULL): array {
    if ($resource) {
      $form['id'] = [
        '#type' => 'hidden',
        '#value' => $resource->getId(),
      ];
    }

    $form['package_id'] = [
      '#type' => 'hidden',
      '#value' => $dataset !== NULL ? $dataset->getId() : NULL,
    ];

    $hasStorageAccess = $this->getUser()->hasStorageAccess();
    $fileId = $this->getFileId($resource);

    if (!$hasStorageAccess || !$fileId) {
      $form['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Url'),
        '#default_value' => $resource !== NULL ? $resource->getUrl() : NULL,
        '#required' => FALSE,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#description' => $this->t('Enter the URL where the data source can be found.'),
      ];
    }

    if ($hasStorageAccess) {
      $extensions = implode(' ', $this->config->get('allowed_file_extensions')) ?? 'doc docx txt pdf xls xlsx csv zip ppsx xml html ods';
      $form['upload'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Upload'),
        '#upload_location' => 'public://dataset/' . ($dataset !== NULL ? $dataset->getId() . '/resources/' : 'resources/'),
        '#required' => FALSE,
        '#multiple' => FALSE,
        '#upload_validators' => [
          'file_validate_extensions' => [$extensions],
        ],
        '#default_value' => $fileId,
        '#description' => $this->t('Allowed file extensions') . ': ' . $extensions,
      ];

      // Keep original file id if file is deleted.
      $form['old_upload'] = [
        '#type' => 'hidden',
        '#default_value' => $fileId,
      ];
    }

    $form['download_url'] = $this->buildFormWrapper($this->t('Download url'), 'download-url');
    $form['download_url']['#description'] = $this->t('If the data source is directly downloadable, you can indicate this in this field. The re-user can then directly download the data source.');
    $downloadUrl = $resource !== NULL ? $resource->getDownloadUrl() : [];
    $downloadUrlCount = $form_state->get('downloadUrlCount');
    if (empty($downloadUrlCount)) {
      $downloadUrlCount = (\is_array($downloadUrl) ? \count($downloadUrl) : 0) + 1;
      $form_state->set('downloadUrlCount', $downloadUrlCount);
    }
    for ($i = 0; $i < $downloadUrlCount; $i++) {
      $form['download_url'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Download url') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $downloadUrl[$i] ?? NULL,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      ];
    }

    $form['download_url']['addDownloadUrl'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('url')]),
      '#submit' => ['::addOneDownloadUrl'],
      '#ajax' => [
        'callback' => '::addMoreDownloadUrlCallback',
        'wrapper' => 'download-url-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $resource !== NULL ? $resource->getName() : NULL,
      '#maxlength' => 256,
      '#required' => TRUE,
      '#description' => $this->t('Give a clear title to your data source, so that the data source is easy to find for re-users. The title preferably consists of one or a few words and if possible a year.'),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $resource !== NULL ? $resource->getDescription() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Give a clear explanation of your data source here. Consider, for example, the content of the data source, year (s), the format, possible indications for reusing the data source, the manner in which the data was obtained and the quality of the data source.'),
    ];

    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#options' => $this->valueList->getList('donl:language'),
      '#multiple' => TRUE,
      '#default_value' => $resource !== NULL ? $resource->getLanguage() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Select from the list of values in which language the data source can be reused.'),
    ];

    $form['metadata_language'] = [
      '#type' => 'select',
      '#title' => $this->t('Metadata language'),
      '#options' => $this->valueList->getList('donl:language'),
      '#default_value' => $resource !== NULL ? $resource->getMetadataLanguage() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Select from the value list in which language the metadata was entered.'),
    ];

    $form['license'] = [
      '#type' => 'select',
      '#title' => $this->t('License'),
      '#options' => $this->valueList->getList('overheid:license'),
      '#default_value' => $resource !== NULL ? $resource->getLicenseId() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('With a license you indicate what kind of user rights there are on this data source. For example, Public Domain, CC-0, CC-BY or CC-BY-SA. Click here for more information about the different licenses: https://data.overheid.nl/licenties-voor-hergebruik.'),
    ];

    $form['rights'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Explanation of the usage restrictions'),
      '#maxlength' => 256,
      '#default_value' => $resource !== NULL ? $resource->getRights() : NULL,
      '#description' => $this->t('If there is an addition to the license of this dataset you can describe it here, for example how you want to be mentioned if there is a CC-BY license on these dates.'),
    ];

    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('State of the source'),
      '#options' => $this->valueList->getList('adms:distributiestatus'),
      '#default_value' => $resource !== NULL ? $resource->getStatus() : NULL,
      '#description' => $this->t('Indicate here, if possible, the life phase of the source.'),
    ];

    $form['format'] = [
      '#type' => 'select',
      '#title' => $this->t('File type'),
      '#options' => $this->valueList->getList('mdr:filetype_nal'),
      '#default_value' => $resource !== NULL ? $resource->getFormat() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Select from the list of values which file format the data source consists of. If the format does not appear in the list, choose the nearest format.'),
    ];

    $distributiontypes = [];
    foreach($this->valueList->getList('donl:distributiontype', FALSE) as $k => $v) {
      $distributiontypes[$k] = $this->t($v);
    }
    $form['distribution_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Distribution type'),
      '#options' => $distributiontypes,
      '#empty_option' => $this->t('- Select item -'),
      '#default_value' => $resource !== NULL ? $resource->getDistributionType() : NULL,
    ];

    $form['size'] = [
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
      '#title' => $this->t('File size'),
      '#default_value' => $resource !== NULL ? $resource->getSize() : NULL,
      '#description' => $this->t('Indicate here how big the file is (in KB).'),
      '#attributes' => [
        'placeholder' => $this->t('Size in KB'),
      ],
    ];

    $form['mimetype'] = [
      '#type' => 'select',
      '#title' => $this->t('Mimetype'),
      '#options' => $this->valueList->getList('iana:mediatypes'),
      '#default_value' => $resource !== NULL ? $resource->getMimetype() : NULL,
      '#description' => $this->t('Select from the list of values which media type the data source consists of. If the type does not appear in the list, choose the nearest type. More information can be found here: https://dcat-ap-donl.readthedocs.io/en/latest/'),
    ];

    $form['release_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Release date'),
      '#default_value' => $resource !== NULL ? $resource->getReleaseDate() : NULL,
      '#description' => $this->t('Enter the date here when the data source has been prepared for availability'),
    ];

    $form['modification_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Modification date'),
      '#default_value' => $resource !== NULL ? $resource->getModificationDate() : NULL,
      '#description' => $this->t('Specify the last modified date here when the data source is muted.'),
    ];

    $form['linked_schemas'] = $this->buildFormWrapper($this->t('Linked schemas'), 'linked-schemas');
    $form['linked_schemas']['#description'] = $this->t('To show how useful a dataset is, data.overheid.nl uses Linked schemes and Linked Data stars. Look here for more information: https://data.overheid.nl/linked-data-sterren');
    $linkedSchemas = $resource !== NULL ? $resource->getLinkedSchemas() : [];
    $linkedSchemasCount = $form_state->get('linkedSchemasCount');
    if (empty($linkedSchemasCount)) {
      $linkedSchemasCount = (\is_array($linkedSchemas) ? \count($linkedSchemas) : 0) + 1;
      $form_state->set('linkedSchemasCount', $linkedSchemasCount);
    }
    for ($i = 0; $i < $linkedSchemasCount; $i++) {
      $form['linked_schemas'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Linked schema') . ' ' . $i,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#title_display' => 'invisible',
        '#default_value' => $linkedSchemas[$i] ?? NULL,
      ];
    }

    $form['linked_schemas']['addLinkedSchemas'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('linked schema')]),
      '#submit' => ['::addOneLinkedSchemas'],
      '#ajax' => [
        'callback' => '::addMoreLinkedSchemasCallback',
        'wrapper' => 'linked-schemas-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['documentation'] = $this->buildFormWrapper($this->t('Documentation'), 'documentation');
    $form['documentation']['#description'] = $this->t('If you have specific documentation about the data source available, you can enter the URL to the documentation in this field.');
    $documentation = $resource !== NULL ? $resource->getDocumentation() : [];
    $documentationCount = $form_state->get('documentationCount');
    if (empty($documentationCount)) {
      $documentationCount = (\is_array($documentation) ? \count($documentation) : 0) + 1;
      $form_state->set('documentationCount', $documentationCount);
    }
    for ($i = 0; $i < $documentationCount; $i++) {
      $form['documentation'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Documentation') . ' ' . $i,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#title_display' => 'invisible',
        '#default_value' => $documentation[$i] ?? NULL,
      ];
    }

    $form['documentation']['addDocumentation'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another documentation'),
      '#submit' => ['::addOneDocumentation'],
      '#ajax' => [
        'callback' => '::addMoreDocumentationCallback',
        'wrapper' => 'documentation-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['checksum'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Checksum'),
      '#tree' => FALSE,
      '#prefix' => '<div class="form__element"><div class="well">',
      '#suffix' => '</div></div>',
    ];

    $form['checksum']['hash_algorithm'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Algorithm'),
      '#maxlength' => 128,
      '#default_value' => $resource !== NULL ? $resource->getHashAlgorithm() : NULL,
      '#description' => $this->t('If you know the algorithm about the data source, you can indicate this here.'),
    ];

    $form['checksum']['hash'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hash'),
      '#maxlength' => 256,
      '#default_value' => $resource !== NULL ? $resource->getHash() : NULL,
      '#description' => $this->t('If you know the hash about the data source, you can indicate this here.'),
    ];

    $form['container_save']['container']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
        // Do not change this as it will be used to trigger the submit handler.
        'data-submit' => 'true',
      ],
    ];

    return $form;
  }

  /**
   * Return all the values as a Resource object.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\ckan\Entity\Resource
   */
  protected function getValues(FormStateInterface $form_state): Resource {
    $resource = new Resource();
    if ($form_state->getValue('id')) {
      $resource = $this->ckanRequest->getResource($form_state->getValue('id'));
    }

    // Set the License (Required).
    $resource->setLicenseId($form_state->getValue('license'));

    // Required values.
    $resource->setPackageId($form_state->getValue('package_id'));
    $resource->setName($form_state->getValue('name'));
    $resource->setDescription($form_state->getValue('description'));
    $resource->setMetadataLanguage($form_state->getValue('metadata_language'));
    $resource->setLanguage(array_filter(array_values($form_state->getValue('language'))));
    $resource->setFormat($form_state->getValue('format'));

    // Set URL (From upload or custom URL).
    if ($fid = $form_state->getValue(['upload', '0'])) {
      if ($file = File::load($fid)) {
        $fileUri = file_create_url($file->getFileUri());
        $resource->setUrl($fileUri);
        $this->fileUsage->add($file, 'ckan', 'Resource', $resource->id ?? '-');
      }
    }
    else {
      $resource->setUrl($form_state->getValue('url'));
    }

    // Remove old file if there is one and it is not the same as the current
    // file.
    if (($oldFid = $form_state->getValue('old_upload')) && $oldFid !== $fid) {
      if ($oldFile = File::load($oldFid)) {
        $this->fileUsage->delete($oldFile, 'ckan', 'Resource', $resource->id);
        $oldFile->delete();
      }
    }

    // Optional values.
    $resource->setLinkedSchemas($this->getMultiValue($form_state->getValue('linked_schemas')));
    $resource->setHash($form_state->getValue('hash'));
    $resource->setHashAlgorithm($form_state->getValue('hash_algorithm'));
    $resource->setSize($form_state->getValue('size'));
    $resource->setMimetype($form_state->getValue('mimetype'));
    $resource->setStatus($form_state->getValue('status'));
    $resource->setRights($form_state->getValue('rights'));
    $resource->setDocumentation($this->getMultiValue($form_state->getValue('documentation')));
    $resource->setDownloadUrl($this->getMultiValue($form_state->getValue('download_url')));
    $resource->setReleaseDate($this->getDateValue($form_state->getValue('release_date')));
    $resource->setModificationDate($this->getDateValue($form_state->getValue('modification_date')));
    $resource->setDistributionType($form_state->getValue('distribution_type'));

    return $resource;
  }

  /**
   * Get the file id of a resource (if the resource has an upload).
   *
   * @param \Drupal\ckan\Entity\Resource|null $resource
   *
   * @return array|null
   */
  public function getFileId(Resource $resource = NULL): ?array {
    if ($resource && $file = $this->getFile($resource)) {
      return [$file->id()];
    }

    return NULL;
  }

  /**
   * Get the file of a resource (if the resource has an upload).
   *
   * @param \Drupal\ckan\Entity\Resource|null $resource
   *
   * @return \Drupal\file\Entity\File|null
   */
  public function getFile(Resource $resource = NULL): ?File {
    if ($resource) {
      $parsedUrl = parse_url($resource->getUrl());
      // If this is the current host (an upload), get the file id.
      if (preg_match('~/sites/default/files/~', $parsedUrl['path']) !== FALSE) {
        $upload = ['public://' . preg_replace('~/sites/default/files/~', '', urldecode($parsedUrl['path']))];
        if ($files = $this->fileStorage->loadByProperties([
          'uri' => $upload,
        ])) {
          return current($files);
        }
      }
    }

    return NULL;
  }

  /**
   *
   */
  public function addOneDocumentation(array &$form, FormStateInterface $form_state): void {
    $form_state->set('documentationCount', $form_state->get('documentationCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreDocumentationCallback(array &$form, FormStateInterface $form_state) {
    return $form['documentation'];
  }

  /**
   *
   */
  public function addOneLinkedSchemas(array &$form, FormStateInterface $form_state): void {
    $form_state->set('linkedSchemasCount', $form_state->get('linkedSchemasCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreLinkedSchemasCallback(array &$form, FormStateInterface $form_state) {
    return $form['linked_schemas'];
  }

  /**
   *
   */
  public function addOneDownloadUrl(array &$form, FormStateInterface $form_state): void {
    $form_state->set('downloadUrlCount', $form_state->get('downloadUrlCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreDownloadUrlCallback(array &$form, FormStateInterface $form_state) {
    return $form['download_url'];
  }

}
