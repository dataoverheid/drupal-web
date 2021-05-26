<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\CkanRequestInterface;
use Drupal\ckan\DataClassificationsInterface;
use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\Entity\Resource;
use Drupal\ckan\MappingServiceInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\donl_dcat_validation\DcatValidationServiceInterface;
use Drupal\donl_value_list\ValueListInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
   * The mapping service.
   *
   * @var \Drupal\ckan\MappingServiceInterface
   */
  protected $mappingService;

  /**
   * {@inheritdoc}
   */
  public function __construct(CkanRequestInterface $ckanRequest, ValueListInterface $valueList, MessengerInterface $messenger, EntityTypeManagerInterface $entityTypeManager, RequestStack $request, DcatValidationServiceInterface $dcatValidationService, FileUsageInterface $fileUsage, ConfigFactoryInterface $configFactory, MappingServiceInterface $mappingService, DataClassificationsInterface $dataClassifications) {
    parent::__construct($ckanRequest, $valueList, $messenger, $entityTypeManager, $request, $dcatValidationService, $mappingService, $dataClassifications);
    $this->fileUsage = $fileUsage;
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->config = $configFactory->get('ckan.dataset.settings');
    $this->mappingService = $mappingService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request'),
      $container->get('donl.value_list'),
      $container->get('messenger'),
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('donl_dcat_validation.validation_service'),
      $container->get('file.usage'),
      $container->get('config.factory'),
      $container->get('ckan.mapping'),
      $container->get('ckan.data_classifications'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL, Resource $resource = NULL): array {
    if (!$dataset) {
      throw new NotFoundHttpException();
    }

    $ckanUser = $this->getUser();
    if ($ckanUser && ($ckanUser->isAdministrator() || $ckanUser->getCkanId() === $dataset->getCreatorUserId())) {
      $form['editLinks'] = [
        '#type' => 'inline_template',
        '#template' => '<div class="container"><div class="buttonswitch">{% for editLink in editLinks %}{{ editLink }}{% endfor %}</div></div>',
        '#weight' => -50,
        '#context' => [
          'editLinks' => $this->getEditLinks($dataset, $ckanUser, 'data-sources'),
        ],
      ];
    }

    $form['package_id'] = [
      '#type' => 'hidden',
      '#value' => $dataset->getId(),
    ];

    $form['#attributes']['class'] = ['donl-form', 'step-form'];
    $form['#attached']['library'][] = 'ckan/dataset-form';

    $form['full_form_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['full-form-wrapper'],
        'class' => [$form_state->getValue('advanced') ? 'advanced' : ''],
      ],
    ];

    $form['full_form_wrapper']['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'main-wrapper',
        ],
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'main',
          $form_state->getValue('advanced') ? 'advanced' : '',
        ],
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['basic']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Data data source'),
    ];

    $hasStorageAccess = ($ckanUser && $ckanUser->hasStorageAccess());
    $fileId = $this->getFileId($resource);

    $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['distribution_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Distribution type'),
      '#options' => $this->valueList->getList('donl:distributiontype'),
      '#default_value' => $resource !== NULL ? $resource->getDistributionType() : 'https://data.overheid.nl/distributiontype/download',
      '#required' => TRUE,
      '#attributes' => ['class' => ['select2']],
    ];

    if (!$hasStorageAccess || !$fileId) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Url'),
        '#default_value' => $resource !== NULL ? $resource->getUrl() : NULL,
        '#required' => TRUE,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#description' => $this->t('Enter the URL where the data source can be found.'),
      ];
    }

    if ($hasStorageAccess) {
      $extensions = implode(' ', $this->config->get('resource.allowed_file_extensions')) ?? 'doc docx txt pdf xls xlsx csv zip ppsx xml html ods json';
      $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['upload'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Upload'),
        '#upload_location' => 'public://dataset/' . $dataset->getId() . '/resources/',
        '#required' => FALSE,
        '#multiple' => FALSE,
        '#upload_validators' => [
          'file_validate_extensions' => [$extensions],
        ],
        '#default_value' => $fileId,
        '#description' => $this->t('Allowed file extensions') . ': ' . $extensions,
      ];

      // Keep original file id if file is deleted.
      $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['old_upload'] = [
        '#type' => 'hidden',
        '#default_value' => $fileId,
      ];
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['download_url'] = $this->buildFormWrapper($this->t('Download url'), 'download-url');
    $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['download_url']['#description'] = $this->t('If the data source is directly downloadable, you can indicate this in this field. The re-user can then directly download the data source.');
    $downloadUrl = $resource !== NULL ? $resource->getDownloadUrl() : [];
    $downloadUrlCount = $form_state->get('downloadUrlCount');
    if (empty($downloadUrlCount)) {
      $downloadUrlCount = (\is_array($downloadUrl) ? \count($downloadUrl) : 0) + 1;
      $form_state->set('downloadUrlCount', $downloadUrlCount);
    }
    for ($i = 0; $i < $downloadUrlCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['download_url'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Download url') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $downloadUrl[$i] ?? NULL,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      ];
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['download_url']['addDownloadUrl'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('url')]),
      '#submit' => ['::addOneDownloadUrl'],
      '#ajax' => [
        'callback' => '::addMoreDownloadUrlCallback',
        'wrapper' => 'download-url-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['description'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['description']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['description'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Description data source'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['description']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $resource !== NULL ? $resource->getName() : NULL,
      '#maxlength' => 256,
      '#required' => TRUE,
      '#description' => $this->t('Give a clear title to your data source, so that the data source is easy to find for re-users. The title preferably consists of one or a few words and if possible a year.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['description']['description'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['markdown'],
      '#format' => 'markdown',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => $resource !== NULL ? $resource->getDescription() : NULL,
      '#description' => $this->t('Give a clear explanation of your data source here. Consider, for example, the content of the data source, year (s), the format, possible indications for reusing the data source, the manner in which the data was obtained and the quality of the data source.'),
      '#after_build' => ['::textFormatAfterBuild'],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['description']['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#options' => $this->valueList->getList('donl:language'),
      '#multiple' => TRUE,
      '#default_value' => $resource !== NULL ? $resource->getLanguage() : $dataset->getLanguage(),
      '#required' => TRUE,
      '#description' => $this->t('Select from the list of values in which language the data source can be reused.'),
      '#attributes' => ['class' => ['select2']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['description']['metadata_language'] = [
      '#type' => 'select',
      '#title' => $this->t('Metadata language'),
      '#options' => $this->valueList->getList('donl:language', TRUE),
      '#default_value' => $resource !== NULL ? $resource->getMetadataLanguage() : $dataset->getMetadataLanguage(),
      '#required' => TRUE,
      '#description' => $this->t('Select from the value list in which language the metadata was entered.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['description']['license'] = [
      '#type' => 'select',
      '#title' => $this->t('License'),
      '#options' => $this->valueList->getList('overheid:license', TRUE),
      '#default_value' => $resource !== NULL ? $resource->getLicenseId() : $dataset->getLicenseId(),
      '#required' => TRUE,
      '#description' => $this->t('With a license you indicate what kind of user rights there are on this data source. For example, Public Domain, CC-0, CC-BY or CC-BY-SA. Click here for more information about the different licenses: https://data.overheid.nl/licenties-voor-hergebruik.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['description']['format'] = [
      '#type' => 'select',
      '#title' => $this->t('File type'),
      '#options' => $this->valueList->getList('mdr:filetype_nal', TRUE),
      '#default_value' => $resource !== NULL ? $resource->getFormat() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Select from the list of values which file format the data source consists of. If the format does not appear in the list, choose the nearest format.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['advanced']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->buildAdvancedTitle($this->t('Additional information')),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['rights'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Explanation of the usage restrictions'),
      '#maxlength' => 256,
      '#default_value' => $resource !== NULL ? $resource->getRights() : NULL,
      '#description' => $this->t('If there is an addition to the license of this dataset you can describe it here, for example how you want to be mentioned if there is a CC-BY license on these dates.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['status'] = [
      '#type' => 'select',
      '#title' => $this->t('State of the source'),
      '#options' => $this->valueList->getList('adms:distributiestatus', TRUE),
      '#default_value' => $resource !== NULL ? $resource->getStatus() : NULL,
      '#description' => $this->t('Indicate here, if possible, the life phase of the source.'),
    ];


    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['size'] = [
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

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['mimetype'] = [
      '#type' => 'select',
      '#title' => $this->t('Mimetype'),
      '#options' => $this->valueList->getList('iana:mediatypes', TRUE),
      '#default_value' => $resource !== NULL ? $resource->getMimetype() : NULL,
      '#description' => $this->t('Select from the list of values which media type the data source consists of. If the type does not appear in the list, choose the nearest type. More information can be found here: https://dcat-ap-donl.readthedocs.io/en/latest/'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['release_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Release date'),
      '#default_value' => $resource !== NULL ? $resource->getReleaseDate() : NULL,
      '#description' => $this->t('Enter the date here when the data source has been prepared for availability'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['modification_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Modification date'),
      '#default_value' => $resource !== NULL ? $resource->getModificationDate() : NULL,
      '#description' => $this->t('Specify the last modified date here when the data source is muted.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['linked_schemas'] = $this->buildFormWrapper($this->t('Linked schemas'), 'linked-schemas');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['linked_schemas']['#description'] = $this->t('To show how useful a dataset is, data.overheid.nl uses Linked schemes and Linked Data stars. Look here for more information: https://data.overheid.nl/linked-data-sterren');
    $linkedSchemas = $resource !== NULL ? $resource->getLinkedSchemas() : [];
    $linkedSchemasCount = $form_state->get('linkedSchemasCount');
    if (empty($linkedSchemasCount)) {
      $linkedSchemasCount = (\is_array($linkedSchemas) ? \count($linkedSchemas) : 0) + 1;
      $form_state->set('linkedSchemasCount', $linkedSchemasCount);
    }
    for ($i = 0; $i < $linkedSchemasCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['linked_schemas'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Linked schema') . ' ' . $i,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#title_display' => 'invisible',
        '#default_value' => $linkedSchemas[$i] ?? NULL,
      ];
    }

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['linked_schemas']['addLinkedSchemas'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('linked schema')]),
      '#submit' => ['::addOneLinkedSchemas'],
      '#ajax' => [
        'callback' => '::addMoreLinkedSchemasCallback',
        'wrapper' => 'linked-schemas-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['documentation'] = $this->buildFormWrapper($this->t('Documentation'), 'documentation');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['documentation']['#description'] = $this->t('If you have specific documentation about the data source available, you can enter the URL to the documentation in this field.');
    $documentation = $resource !== NULL ? $resource->getDocumentation() : [];
    $documentationCount = $form_state->get('documentationCount');
    if (empty($documentationCount)) {
      $documentationCount = (\is_array($documentation) ? \count($documentation) : 0) + 1;
      $form_state->set('documentationCount', $documentationCount);
    }
    for ($i = 0; $i < $documentationCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['documentation'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Documentation') . ' ' . $i,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#title_display' => 'invisible',
        '#default_value' => $documentation[$i] ?? NULL,
      ];
    }

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['documentation']['addDocumentation'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another documentation'),
      '#submit' => ['::addOneDocumentation'],
      '#ajax' => [
        'callback' => '::addMoreDocumentationCallback',
        'wrapper' => 'documentation-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['checksum'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Checksum'),
      '#tree' => FALSE,
      '#prefix' => '<div class="form__element"><div class="well">',
      '#suffix' => '</div></div>',
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['checksum']['hash_algorithm'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Algorithm'),
      '#maxlength' => 128,
      '#default_value' => $resource !== NULL ? $resource->getHashAlgorithm() : NULL,
      '#description' => $this->t('If you know the algorithm about the data source, you can indicate this here.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['checksum']['hash'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hash'),
      '#maxlength' => 256,
      '#default_value' => $resource !== NULL ? $resource->getHash() : NULL,
      '#description' => $this->t('If you know the hash about the data source, you can indicate this here.'),
    ];

    $subSteps = $this->getSubSteps($form, 'basic');
    $subSteps[] = $this->getSubSteps($form, 'advanced', count($subSteps));

    $form['full_form_wrapper']['header'] = [
      '#weight' => -45,
      '#theme' => 'donl_form_header',
      '#type' => 'dataset',
      '#summary' => [
        '#theme' => 'donl_form_summary',
        '#title' => $this->t('Dataset'),
        '#step_title' => $this->t('Manage data sources'),
        '#fields' => [
          'title' => [
            'title' => $this->t('Title'),
            'value' => $dataset->getTitle(),
          ],
          'owner' => [
            'title' => $this->t('Owner'),
            'value' => $this->mappingService->getOrganizationName($dataset->getAuthority()),
          ],
          'licence' => [
            'title' => $this->t('Licence'),
            'value' => $this->mappingService->getLicenseName($dataset->getLicenseId()),
          ],
          'changed' => [
            'title' => $this->t('Changed'),
            'value' => $dataset->getModified()->format('d-m-Y H:i'),
          ],
          'status' => [
            'title' => $this->t('Status'),
            'value' => $this->mappingService->getStatusName($dataset->getDatasetStatus()),
          ],
          'published' => [
            'title' => $this->t('Published'),
            'value' => (!$dataset->getPrivate() ? $this->t('Yes') : $this->t('No')),
          ],
        ],
      ],
      '#steps' => [
        'dataset' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Register dataset'),
          '#short_title' => $this->t('Dataset'),
          '#completed' => TRUE,
        ],
        'resource' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Manage data sources'),
          '#short_title' => $this->t('Data source'),
          '#icon' => 'icon-databron',
          '#active' => TRUE,
          '#sub_steps' => $subSteps,
        ],
        'finish' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Wrap up'),
          '#short_title' => $this->t('Wrap up'),
          '#icon' => 'icon-connected-globe',
        ],
      ],
    ];

    $form['full_form_wrapper']['wrapper']['sidebar'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['donl-form-sidebar']],
      '#weight' => -10,
    ];

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'sidebar-nav',
          $form_state->getValue('advanced') ? 'advanced' : '',
        ],
      ],
      'advanced' => [
        '#type' => 'radios',
        '#options' => [
          0 => $this->t('Basic'),
          1 => $this->t('Advanced'),
        ],
        '#default_value' => $form_state->getValue('advanced') ? 1 : 0,
        '#attributes' => ['class' => ['js-edit-advance', '']],
        '#ajax' => [
          'callback' => '::toggleAdvanced',
          'disable-refocus' => FALSE,
          'event' => 'change',
          'wrapper' => 'full-form-wrapper',
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Updating'),
          ],
        ],
        '#limit_validation_errors' => [],
      ],
      'dataset_form_advanced_switch' => [
        '#theme' => 'dataset_form_advanced_switch',
      ],
      'step' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['sub-steps']],
        'sub_steps' => $subSteps,
      ],
    ];

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['sidebar-nav-actions']],
    ];

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#attributes' => [
        'class' => ['hidden', 'js-form-submit'],
        'id' => 'donl-form-submit-button',
        // Do not change this as it will be used to trigger the submit handler.
        'data-submit' => 'true',
      ],
    ];

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['submit_overlay'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->t('Next step'),
      '#attributes' => [
        'class' => ['button', 'button--primary', 'submit-overlay'],
        'data-in-form-text' => $this->t('Next step'),
        'data-next-form-text' => $this->t('Save'),
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
    $resource->setDescription($form_state->getValue('description')['value']);
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
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['documentation'];
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
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['additional']['linked_schemas'];
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
    return $form['full_form_wrapper']['wrapper']['main']['basic']['basic_data']['download_url'];
  }

  public function toggleAdvanced(array &$form, FormStateInterface $form_state) {
    $form_state->set('advanced', !$form_state->get('advanced'));
    $form_state->setRebuild();
    return $form['full_form_wrapper'];
  }

}
