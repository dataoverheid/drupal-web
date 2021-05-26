<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\Entity\Tag;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 *
 */
abstract class DatasetBaseForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL): array {
    $ckanUser = $this->getUser();

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
      '#attributes' => ['class' => ['main']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['basic']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Description'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#maxlength' => 256,
      '#default_value' => $dataset !== NULL ? $dataset->getTitle() : NULL,
      '#description' => $this->t('Give a good title to your dataset, give the dataset easy to find for re-users. The title consists of one or more words and, if possible, a year.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['notes'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['markdown'],
      '#format' => 'markdown',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => $dataset !== NULL ? $dataset->getNotes() : NULL,
      '#description' => $this->t('Give a clear and clear explanation of your dataset. Consider, for example, the contents of the dataset, year(s), the format, possible indications for reusing the dataset, the way in which the data was obtained and the quality of the dataset.'),
      '#after_build' => ['::textFormatAfterBuild'],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Themes'),
      '#options' => $this->valueList->getPreparedHierarchicalThemeList(),
      '#multiple' => TRUE,
      '#default_value' => $dataset !== NULL ? $dataset->getTheme() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Click with your mouse on the field and choose a theme that fits the dataset.'),
      '#attributes' => [
        'class' => ['select2'],
        'placeholder' => $this->t('- Select item -'),
        'data-allow-clear' => 'true',
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tags', [], ['context' => 'dataset']),
      '#maxlength' => 512,
      '#default_value' => $dataset !== NULL ? implode(', ', $dataset->getTags()) : NULL,
      '#description' => $this->t('You can give multiple tags to this dataset. Tags are keywords that make the data easier to find. We recommend using about 5 tags per dataset.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL Landingspage'),
      '#placeholder' => 'https://',
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#default_value' => $dataset !== NULL ? $dataset->getUrl() : NULL,
      '#description' => $this->t('Here you can place a link for more information about (the use) of this dataset.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Classification'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['high_value'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('High value dataset'),
      '#default_value' => $dataset !== NULL ? $dataset->getHighValue() : 0,
      '#description' => $this->t('Indicate whether this dataset is a high value dataset or not. A high value dataset is data that contributes to a transparent and open government or data that has socio-economic added value for society. More information about high value datasets can be found here: <a href=\"https://data.overheid.nl/referencedatasets\">Reference datasets</a>'),
    ];

    if ($highValueClassification = $this->dataClassifications->getDataClassification('High value')) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['high_value']['#description'] = $this->dataClassifications->getTooltipForm($highValueClassification);
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['reference_data'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reference data'),
      '#default_value' => $dataset !== NULL ? $dataset->getReferenceData() : 0,
    ];

    if ($referenceValueClassification = $this->dataClassifications->getDataClassification('Reference data')) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['reference_data']['#description'] = $this->dataClassifications->getTooltipForm($referenceValueClassification);
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['national_coverage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('National coverage'),
      '#default_value' => $dataset !== NULL ? $dataset->getNationalCoverage() : 0,
    ];

    if ($nationalCoverageClassification = $this->dataClassifications->getDataClassification('National coverage')) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['national_coverage']['#description'] = $this->dataClassifications->getTooltipForm($nationalCoverageClassification);
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['base_register'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Base register'),
      '#default_value' => $dataset !== NULL ? $dataset->getBaseRegister() : 0,
    ];

    if ($baseRegisterClassification = $this->dataClassifications->getDataClassification('Base register')) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['base_register']['#description'] = $this->dataClassifications->getTooltipForm($baseRegisterClassification);
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['sector_registrations'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Sector registrations'),
      '#default_value' => $dataset !== NULL ? $dataset->getSectorRegistrations() : 0,
    ];

    if ($sectorRegistrationsClassification = $this->dataClassifications->getDataClassification('Sector registrations')) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['sector_registrations']['#description'] = $this->dataClassifications->getTooltipForm($sectorRegistrationsClassification);
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['local_registrations'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Local registrations'),
      '#default_value' => $dataset !== NULL ? $dataset->getLocalRegistrations() : 0,
    ];

    if ($localRegistrationsClassification = $this->dataClassifications->getDataClassification('Local registrations')) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['dataset_data']['data_classification']['local_registrations']['#description'] = $this->dataClassifications->getTooltipForm($localRegistrationsClassification);
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['information_about_provider'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Information about the provider'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['information_about_provider']['owner_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Owner information'),
    ];

    $options = $this->valueList->getList('donl:organization', TRUE);
    if ($ckanUser && $authority = $ckanUser->getAuthority()) {
      $options = array_intersect_key($options, [$authority => 1]);
    }
    $form['full_form_wrapper']['wrapper']['main']['basic']['information_about_provider']['owner_wrapper'] ['authority'] = [
      '#type' => 'select',
      '#title' => $this->t('Data owner'),
      '#options' => $options,
      '#default_value' => $dataset !== NULL ? $dataset->getAuthority() : NULL,
      '#attributes' => [
        'class' => [
          'select2',
          'js-authority',
        ],
      ],
      '#required' => TRUE,
      '#description' => $this->t('Select the data owner of the dataset here. The data owner is the organization responsible for the content of the dataset. The data owner will also ask or handle data requests.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['information_about_provider']['contact_point'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Contact point'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['information_about_provider']['contact_point']['contact_point_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointName() : NULL,
      '#description' => $this->t('Enter the department / organization where you can be contacted. It is advisable to use a general name in connection with showing the data.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['information_about_provider']['contact_point']['contact_point_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact details (minimal of one)'),
      '#placeholder' => $this->t('Email address'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointEmail() : NULL,
      '#description' => $this->t('Enter the e-mail address where you can be contacted. It is advisable to use a general e-mail address.')
        . '<br><br>' . $this->t('Enter the website here that gives more information about the organization.')
        . '<br><br>' . $this->t('Enter the phone details where you can be contacted. It is advisable to use a general number in connection with showing the data.'),
      '#attributes' => [
        'class' => ['grouped-required'],
        'data-group' => 'contact-point',
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['information_about_provider']['contact_point']['contact_point_website'] = [
      '#type' => 'url',
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#placeholder' => $this->t('Website eg. https://data.overheid.nl'),
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointWebsite() : NULL,
      '#attributes' => [
        'class' => ['grouped-required'],
        'data-group' => 'contact-point',
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['information_about_provider']['contact_point']['contact_point_phone'] = [
      '#type' => 'textfield',
      '#maxlength' => 128,
      '#placeholder' => $this->t('Phone'),
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointPhone() : NULL,

      '#attributes' => [
        'class' => ['grouped-required'],
        'data-group' => 'contact-point',
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['reuse'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Reuse information'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['access_rights'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Access rights'),
      '#options' => $this->valueList->getList('overheid:openbaarheidsniveau', TRUE),
      '#default_value' => $dataset !== NULL ? $dataset->getAccessRights() : 'http://publications.europa.eu/resource/authority/access-right/PUBLIC',
      '#description' => $this->t('Choose whether the access to this dataset is public, restricted or closed. This indicates how this data can be reused. For example: a dataset for which the re-user must log in or register must have restricted access. A dataset that is completely open has access to it.'),
      '#attributes' => [
        'class' => ['select2'],
      ],
    ];
    if (!$dataset) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['access_rights']['#attributes']['data-default-value'] = 'http://publications.europa.eu/resource/authority/access-right/PUBLIC';
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['access_rights_reason'] = [
      '#type' => 'select',
      '#title' => $this->t('Access rights reason'),
      '#options' => $this->valueList->getList('donl:wobuitzondering', TRUE),
      '#default_value' => $dataset !== NULL ? $dataset->getAccessRightsReason() : NULL,
      '#attributes' => [
        'class' => ['select2'],
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['dataset_status'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Status dataset'),
      '#options' => $this->valueList->getList('overheid:datasetStatus', TRUE),
      '#default_value' => $dataset !== NULL ? $dataset->getDatasetStatus() : 'http://data.overheid.nl/status/beschikbaar',
      '#description' => $this->t('Select the current status of the dataset here. For example: the dataset is available, planned, under investigation or not available.'),
      '#attributes' => [
        'class' => ['select2', 'js-date-planned-changer'],
      ],
    ];
    if (!$dataset) {
      $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['dataset_status']['#attributes']['data-default-value'] = 'http://data.overheid.nl/status/beschikbaar';
    }

    $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['date_planned'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Expected date available'),
      '#default_value' => $dataset !== NULL ? $dataset->getDatePlanned() : NULL,
      '#description' => $this->t('If you have filled in the status of the dataset in research or planned. Please indicate here the expected publication date on which the dataset is opened or more information can be given about the research whether it can be opened or not.'),
      '#attributes' => ['class' => ['js-date-planned']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['licence_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Licence & conditions'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['licence_wrapper']['license'] = [
      '#type' => 'select',
      '#title' => $this->t('License'),
      '#options' => $this->valueList->getList('overheid:license'),
      '#default_value' => $dataset !== NULL ? $dataset->getLicenseId() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('With a license you indicate what kind of user rights there are on this dataset. For example, Public Domain, CC-0, CC-BY or CC-BY-SA. Click here for more information about the different licenses: https://data.overheid.nl/licenties-voor-hergebruik.'),
      '#attributes' => [
        'class' => ['select2'],
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['basic']['reuse']['licence_wrapper']['restrictions_statement'] = [
      '#type' => 'textarea',
      '#title' => $this->t('License explanation'),
      '#default_value' => $dataset !== NULL ? $dataset->getRestrictionsStatement() : NULL,
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['advanced']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->buildAdvancedTitle($this->t('Rights and visibility')),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['legal_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Legal ground'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['provenance'] = $this->buildFormWrapper($this->t('Provenance'), 'provenance');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['provenance']['#description'] = $this->t('You can indicate here the provenance of this dataset.');
    $provenance = $dataset !== NULL ? $dataset->getProvenance() : [];
    $provenanceCount = $form_state->get('provenanceCount');
    if (empty($provenanceCount)) {
      $provenanceCount = \count($provenance) + 1;
      $form_state->set('provenanceCount', $provenanceCount);
    }
    for ($i = 0; $i < $provenanceCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['provenance'][$i] = [
        '#type' => 'url',
        '#placeholder' => 'https://',
        '#title' => $this->t('Dataset') . ' ' . $i,
        '#maxlength' => 256,
        '#title_display' => 'invisible',
        '#default_value' => $provenance[$i] ?? NULL,
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['provenance']['addProvenance'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('provenance')]),
      '#submit' => ['::addOneProvenance'],
      '#ajax' => [
        'callback' => '::addMoreProvenanceCallback',
        'wrapper' => 'provenance-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['legal_wrapper']['legal_foundation_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Quote title'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getLegalFoundationLabel() : NULL,
      '#description' => $this->t('Enter the legal title (if applicable). More information about the reference to legislation and regulations can be found here: https://www.overheid.nl/help/wet-en-regelgeving/verwijzen-naar-wet-en-reggeving'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['legal_wrapper']['legal_foundation_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link'),
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#default_value' => $dataset !== NULL ? $dataset->getLegalFoundationUri() : NULL,
      '#description' => $this->t('In this field you can link to the scheme that forms the basis for these data. More information about the reference to legislation and regulations can be found here: https://www.overheid.nl/help/wet-en-regelgeving/verwijzen-naar-wet-en-reggeving'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['legal_wrapper']['legal_foundation_ref'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Juriconnect reference'),
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#default_value' => $dataset !== NULL ? $dataset->getLegalFoundationRef() : NULL,
      '#description' => $this->t("Making a correct Juriconnect reference requires some technical knowledge. At https://wetten.overheid.nl you are therefore helped to compile such a reference. With each control item (an article, a chapter, etc.) you can choose via the right drop-down menu to create a 'Permanent link'. More information about the reference to legislation and regulations can be found here: https://www.overheid.nl/help/wet-en-regelgeving/verwijzen-naar-wet-en-reggeving"),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->buildAdvancedTitle($this->t('Location and temporal')),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['spatial'] = $this->buildFormWrapper($this->t('Spatial data'), 'spatial');
    $spatial_scheme = $dataset !== NULL ? $dataset->getSpatialScheme() : [];
    $spatial_value = $dataset !== NULL ? $dataset->getSpatialValue() : [];
    $spatialCount = $form_state->get('spatialCount');
    if (empty($spatialCount)) {
      $spatialCount = \count($spatial_scheme) + 1;
      $form_state->set('spatialCount', $spatialCount);
    }
    for ($i = 0; $i < $spatialCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['spatial'][$i]['spatial_scheme'] = [
        '#type' => 'select',
        '#options' => $this->valueList->getList('overheid:spatial_scheme', TRUE),
        '#title' => $this->t('Type of data'),
        '#default_value' => $spatial_scheme[$i] ?? NULL,
        '#prefix' => Markup::create('<div style="overflow: hidden;"><div style="width: 49%; float: left;">'),
        '#suffix' => '</div>',
      ];

      $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['spatial'][$i]['spatial_value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value'),
        '#maxlength' => 256,
        '#default_value' => $spatial_value[$i] ?? NULL,
        '#prefix' => Markup::create('<div style="width: 49%; float: left;">'),
        '#suffix' => '</div></div>',
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['spatial']['addSpatial'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('spatial data')]),
      '#submit' => ['::addOneSpatial'],
      '#ajax' => [
        'callback' => '::addMoreSpatialCallback',
        'wrapper' => 'spatial-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['temporal_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Temporal coverage'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['temporal_wrapper']['temporal_start'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start coverage period'),
      '#default_value' => $dataset !== NULL ? $dataset->getTemporalStart() : NULL,
      '#description' => $this->t('If applicable, describe here the start and end date of this dataset.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['temporal_wrapper']['temporal_end'] = [
      '#type' => 'datetime',
      '#title' => $this->t('End coverage period'),
      '#default_value' => $dataset !== NULL ? $dataset->getTemporalEnd() : NULL,
      '#description' => $this->t('If applicable, describe here the start and end date of this dataset.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['temporal_wrapper']['temporal_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of the coverage period'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getTemporalLabel() : NULL,
      '#description' => $this->t('If applicable, please describe here the name of the coverage period of this dataset.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->buildAdvancedTitle($this->t('Relationships and references')),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['related_resource'] = $this->buildFormWrapper($this->t('Related resources'), 'related-resource');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['related_resource']['#description'] = $this->t('If there are a number of related datasets on this dataset, you can indicate this here.');
    $relatedResource = $dataset !== NULL ? $dataset->getRelatedResource() : [];
    $relatedResourceCount = $form_state->get('relatedResourceCount');
    if (empty($relatedResourceCount)) {
      $relatedResourceCount = \count($relatedResource) + 1;
      $form_state->set('relatedResourceCount', $relatedResourceCount);
    }
    for ($i = 0; $i < $relatedResourceCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['related_resource'][$i] = [
        '#type' => 'url',
        '#placeholder' => 'https://',
        '#title' => $this->t('Related resource') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $relatedResource[$i] ?? NULL,
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['related_resource']['addRelatedResource'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('related resource')]),
      '#submit' => ['::addOneRelatedResource'],
      '#ajax' => [
        'callback' => '::addMoreRelatedResourceCallback',
        'wrapper' => 'related-resource-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['source'] = $this->buildFormWrapper($this->t('This dataset is based on'), 'source');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['source']['#description'] = $this->t('If there are datasets that are based on this dataset, you can indicate this here.');
    $source = $dataset !== NULL ? $dataset->getSource() : [];
    $sourceCount = $form_state->get('sourceCount');
    if (empty($sourceCount)) {
      $sourceCount = \count($source) + 1;
      $form_state->set('sourceCount', $sourceCount);
    }
    for ($i = 0; $i < $sourceCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['source'][$i] = [
        '#type' => 'url',
        '#placeholder' => 'https://',
        '#title' => $this->t('Dataset') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $source[$i] ?? NULL,
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['source']['addSource'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('dataset')]),
      '#submit' => ['::addOneSource'],
      '#ajax' => [
        'callback' => '::addMoreSourceCallback',
        'wrapper' => 'source-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['documentation'] = $this->buildFormWrapper($this->t('Documentation'), 'documentation');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['documentation']['#description'] = $this->t('If you have documentation on the use of the dataset, you can indicate this by placing a link to the documentation here.');
    $documentation = $dataset !== NULL ? $dataset->getDocumentation() : [];
    $documentationCount = $form_state->get('documentationCount');
    if (empty($documentationCount)) {
      $documentationCount = \count($documentation) + 1;
      $form_state->set('documentationCount', $documentationCount);
    }
    for ($i = 0; $i < $documentationCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['documentation'][$i] = [
        '#type' => 'url',
        '#placeholder' => 'https://',
        '#title' => $this->t('Documentation') . ' ' . $i,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#title_display' => 'invisible',
        '#default_value' => $documentation[$i] ?? NULL,
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['documentation']['addDocumentation'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('documentation')]),
      '#submit' => ['::addOneDocumentation'],
      '#ajax' => [
        'callback' => '::addMoreDocumentationCallback',
        'wrapper' => 'documentation-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['conforms_to'] = $this->buildFormWrapper($this->t('Conforms to the following standards'), 'conforms-to');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['conforms_to']['#description'] = $this->t('If a certain standard is used in this dataset (such as juriconnect or an ISO standard), you can indicate this here. Please use the url to the standard.');
    $conformsTo = $dataset !== NULL ? $dataset->getConformsTo() : [];
    $conformsToCount = $form_state->get('conformsToCount');
    if (empty($conformsToCount)) {
      $conformsToCount = \count($conformsTo) + 1;
      $form_state->set('conformsToCount', $conformsToCount);
    }
    for ($i = 0; $i < $conformsToCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['conforms_to'][$i] = [
        '#type' => 'url',
        '#title' => $this->t('Standard') . ' ' . $i,
        '#title_display' => 'invisible',
        '#placeholder' => 'https://www.iso.org/obp/ui/#iso:std:iso:22886:dis:ed-1:v1:en',
        '#default_value' => $conformsTo[$i] ?? NULL,
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['conforms_to']['addConformsTo'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('standard')]),
      '#submit' => ['::addOneConformsTo'],
      '#ajax' => [
        'callback' => '::addMoreConformsToCallback',
        'wrapper' => 'conforms-to-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['sample'] = $this->buildFormWrapper($this->t('Sample of data', [], ['context' => 'sample']), 'sample');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['sample']['#description'] = $this->t('If the dataset is used in an application, you can place a link to this application here. In this way you can gain insight into the use of the data.');
    $sample = $dataset !== NULL ? $dataset->getSample() : [];
    $sampleCount = $form_state->get('sampleCount');
    if (empty($sampleCount)) {
      $sampleCount = \count($sample) + 1;
      $form_state->set('sampleCount', $sampleCount);
    }
    for ($i = 0; $i < $sampleCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['sample'][$i] = [
        '#type' => 'url',
        '#placeholder' => 'https://',
        '#title' => $this->t('Sample') . ' ' . $i,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#title_display' => 'invisible',
        '#default_value' => $sample[$i] ?? NULL,
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['sample']['addSample'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('sample')]),
      '#submit' => ['::addOneSample'],
      '#ajax' => [
        'callback' => '::addMoreSampleCallback',
        'wrapper' => 'sample-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->buildAdvancedTitle($this->t('Extra options')),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['version_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Version control'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['version_wrapper']['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Version'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getVersion() : NULL,
      '#description' => $this->t('Use this field if a new version of the dataset is regularly placed online. You can, for example, choose version 1.0, 2.0 etc.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['version_wrapper']['version_notes'] = $this->buildFormWrapper($this->t('Version notes'), 'version-notes');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['version_wrapper']['version_notes']['#description'] = $this->t('If there is a specific reason for updating the dataset (such a corrected error), you can state this here.');
    $versionNotes = $dataset !== NULL ? $dataset->getVersionNotes() : [];
    $versionNotesCount = $form_state->get('versionNotesCount');
    if (empty($versionNotesCount)) {
      $versionNotesCount = \count($versionNotes) + 1;
      $form_state->set('versionNotesCount', $versionNotesCount);
    }
    for ($i = 0; $i < $versionNotesCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['version_wrapper']['version_notes'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Version notes') . ' ' . $i,
        '#maxlength' => 256,
        '#title_display' => 'invisible',
        '#default_value' => $versionNotes[$i] ?? NULL,
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['version_wrapper']['version_notes']['addVersionNotes'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('version note')]),
      '#submit' => ['::addOneVersionNotes'],
      '#ajax' => [
        'callback' => '::addMoreVersionNotesCallback',
        'wrapper' => 'version-notes-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['version_wrapper']['frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Frequency of changes'),
      '#options' => $this->valueList->getList('overheid:frequency', TRUE),
      '#default_value' => $dataset !== NULL ? $dataset->getFrequency() : NULL,
      '#description' => $this->t('You can choose from this list how often an update of this dataset takes place. If the desired change frequency is not a selection option in the list, select the option that is irregular or the option closest to the change frequency.'),
      '#attributes' => ['class' => ['select2']],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['language_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Language settings'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['language_wrapper']['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Data language'),
      '#options' => $this->valueList->getList('donl:language'),
      '#multiple' => TRUE,
      '#default_value' => $dataset !== NULL ? $dataset->getLanguage() : 'http://publications.europa.eu/resource/authority/language/NLD',
      '#required' => TRUE,
      '#description' => $this->t('Select from the list of values in which language the dataset can be reused.'),
      '#attributes' => [
        'class' => ['select2'],
        'placeholder' => $this->t('- Select item -'),
        'data-allow-clear' => 'true',
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['language_wrapper']['metadata_language'] = [
      '#type' => 'select',
      '#title' => $this->t('Metadata language'),
      '#options' => $this->valueList->getList('donl:language'),
      '#default_value' => $dataset !== NULL ? $dataset->getMetadataLanguage() : 'http://publications.europa.eu/resource/authority/language/NLD',
      '#required' => TRUE,
      '#description' => $this->t('Select from the value list in which language the metadata was entered.'),
      '#attributes' => [
        'class' => ['select2'],
        'placeholder' => $this->t('- Select item -'),
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['identifier_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Identifiers'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['identifier_wrapper']['identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifier'),
      '#default_value' => $dataset !== NULL ? $dataset->getIdentifier() : NULL,
      '#required' => FALSE,
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#description' => $this->t('It must be indicated here, via a URL, where the dataset is originally to be found.'),
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['identifier_wrapper']['alternate_identifier'] = $this->buildFormWrapper($this->t('Alternative identifier'), 'alternate-identifier');
    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['identifier_wrapper']['alternate_identifier']['#description'] = $this->t('If there is another alternative location where the dataset is shown, this can be entered here.');
    $alternateIdentifier = $dataset !== NULL ? $dataset->getAlternateIdentifier() : [];
    $alternateIdentifierCount = $form_state->get('alternateIdentifierCount');
    if (empty($alternateIdentifierCount)) {
      $alternateIdentifierCount = \count($alternateIdentifier) + 1;
      $form_state->set('alternateIdentifierCount', $alternateIdentifierCount);
    }
    for ($i = 0; $i < $alternateIdentifierCount; $i++) {
      $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['identifier_wrapper']['alternate_identifier'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Alternative identifier') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $alternateIdentifier[$i] ?? NULL,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      ];
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['identifier_wrapper']['alternate_identifier']['addAlternateIdentifier'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('identifier')]),
      '#submit' => ['::addOneAlternateIdentifier'],
      '#ajax' => [
        'callback' => '::addMoreAlternateIdentifierCallback',
        'wrapper' => 'alternate-identifier-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['publisher_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Publication & catalogues'),
    ];

    $options = $this->valueList->getList('donl:organization');
    if ($ckanUser && $publisher = $ckanUser->getPublisher()) {
      $options = array_intersect_key($options, [$publisher => 1]);
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['publisher_wrapper']['publisher'] = [
      '#type' => 'select',
      '#title' => $this->t('Data publisher'),
      '#options' => $options,
      '#default_value' => $dataset !== NULL ? $dataset->getPublisher() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('The publishing organization (former provider) is an optional field in which an organization is appointed that is responsible for the delivery of the data. It is important to fill this in if it deviates from the organization that is the data owner. If this is the same as the data owner, it will suffice to refer to the data owner himself.'),
      '#attributes' => ['class' => ['select2', 'js-authority-target']],
    ];

    $options = $this->valueList->getList('donl:catalogs');
    if ($ckanUser && $catalogs = $ckanUser->getCatalogs()) {
      $options = array_intersect_key($options, $catalogs);
    }
    $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['publisher_wrapper']['source_catalog'] = [
      '#type' => 'select',
      '#title' => $this->t('Source Catalog'),
      '#options' => $options,
      '#default_value' => $dataset !== NULL ? $dataset->getSourceCatalog() : 'https://data.overheid.nl',
      '#required' => TRUE,
      '#description' => $this->t('Choose here for the source in which catalog the dataset is included.'),
      '#attributes' => ['class' => ['select2']],
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
        '#step_title' => $this->t('Register dataset'),
        '#fields' => [
          'title' => [
            'title' => $this->t('Title'),
            'field' => 'title',
          ],
          'authority' => [
            'title' => $this->t('Owner'),
            'field' => 'authority',
          ],
          'license' => [
            'title' => $this->t('Licence'),
            'field' => 'license',
          ],
          'changed' => [
            'title' => $this->t('Changed'),
            'value' => $dataset !== NULL ? $dataset->getModified()->format('d-m-Y H:i') : date('d-m-Y'),
          ],
          'dataset_status' => [
            'title' => $this->t('Status'),
            'field' => 'dataset_status',
          ],
          'published' => [
            'title' => $this->t('Published'),
            'value' => ($dataset && !$dataset->getPrivate()) ? $this->t('Yes') : $this->t('No'),
          ],
        ],
      ],
      '#steps' => [
        'dataset' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Register dataset'),
          '#short_title' => $this->t('Dataset'),
          '#icon' => 'icon-title',
          '#active' => TRUE,
          '#sub_steps' => $subSteps,
        ],
        'resource' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Manage data sources'),
          '#short_title' => $this->t('Data source'),
          '#icon' => 'icon-databron',
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
      '#attributes' => ['class' => ['sidebar-nav']],
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
      '#value' => $this->t('Next step'),
      '#attributes' => [
        'class' => ['hidden'],
        'id' => 'donl-form-submit-button',
        // Do not change this as it will be used to trigger the submit handler.
        'data-submit' => 'true',
      ],
    ];

    $cancelUrl = $dataset ? Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->id]) :
      Url::fromRoute('donl_search.search.dataset');

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['cancel'] =
      Link::fromTextAndUrl($this->t('Cancel'), $cancelUrl)->toRenderable();

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['submit-overlay-wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['submit-overlay-wrapper']],
    ];

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['submit-overlay-wrapper']['submit_overlay'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->t('Next step'),
      '#attributes' => [
        'type' => 'button',
        'class' => ['button', 'button--primary', 'submit-overlay'],
        'data-in-form-text' => $this->t('Next step'),
        'data-next-form-text' => $this->t('Continue to datasources'),
      ],
    ];

    $form['#attributes'] = [
      'class' => [
        'donl-form',
        'step-form',
        $form_state->getValue('advanced') ? 'advanced' : '',
      ],
    ];
    $form['full_form_wrapper']['#attached']['library'][] = 'ckan/dataset-form';

    return $form;
  }

  /**
   * Return all the values as a Dataset object.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\ckan\Entity\Dataset
   */
  protected function getValues(FormStateInterface $form_state): Dataset {
    $dataset = new Dataset();
    $dataset->setPrivate(TRUE);
    if ($form_state->getValue('id')) {
      $dataset = $this->ckanRequest->getDataset($form_state->getValue('id'));
    }

    // Set the License (Required).
    $dataset->setLicenseId($form_state->getValue('license'));

    // Set the tags (Optional).
    $tags = [];
    if ($value = $form_state->getValue('tags')) {
      foreach (explode(',', $value) as $v) {
        $tag = new Tag();
        $tag->setName(trim($v));
        $tags[] = $tag;
      }
    }
    $dataset->setTags($tags);

    // Required values.
    $dataset->setTitle($form_state->getValue('title'));
    $dataset->setLanguage(array_filter(array_values($form_state->getValue('language'))));
    $dataset->setAuthority($form_state->getValue('authority'));
    $dataset->setPublisher($form_state->getValue('publisher'));
    $dataset->setContactPointName($form_state->getValue('contact_point_name'));
    $dataset->setNotes($form_state->getValue('notes')['value']);
    $dataset->setMetadataLanguage($form_state->getValue('metadata_language'));
    $dataset->setTheme(array_filter(array_values($form_state->getValue('theme'))));
    $dataset->setHighValue($form_state->getValue('high_value'));
    $dataset->setBaseRegister($form_state->getValue('base_register'));
    $dataset->setReferenceData($form_state->getValue('reference_data'));
    $dataset->setNationalCoverage($form_state->getValue('national_coverage'));
    $dataset->setSectorRegistrations($form_state->getValue('sector_registrations'));
    $dataset->setLocalRegistrations($form_state->getValue('local_registrations'));

    // Set the name if it is a new dataset.
    if (!$form_state->getValue('id')) {
      $dataset->setName($this->checkAvailability($this->generateName($dataset->getTitle())));
    }

    // Set the identifier of the dataset.
    if ($form_state->getValue('identifier')) {
      $dataset->setIdentifier($form_state->getValue('identifier'));
    }

    // Optional values.
    $dataset->setUrl($form_state->getValue('url'));
    $dataset->setContactPointWebsite($form_state->getValue('contact_point_website'));
    $dataset->setContactPointEmail($form_state->getValue('contact_point_email'));
    $dataset->setContactPointAddress($form_state->getValue('contact_point_address'));
    $dataset->setContactPointPhone($form_state->getValue('contact_point_phone'));
    $dataset->setContactPointTitle($form_state->getValue('contact_point_title'));
    $dataset->setFrequency($form_state->getValue('frequency'));
    $dataset->setSourceCatalog($form_state->getValue('source_catalog'));
    $dataset->setVersion($form_state->getValue('version'));
    $dataset->setTemporalLabel($form_state->getValue('temporal_label'));
    $dataset->setDatasetStatus($form_state->getValue('dataset_status'));
    $dataset->setLegalFoundationLabel($form_state->getValue('legal_foundation_label'));
    $dataset->setLegalFoundationRef($form_state->getValue('legal_foundation_ref'));
    $dataset->setLegalFoundationUri($form_state->getValue('legal_foundation_uri'));
    $dataset->setRestrictionsStatement($form_state->getValue('restrictions_statement'));
    $dataset->setAlternateIdentifier($this->getMultiValue($form_state->getValue('alternate_identifier')));
    $dataset->setSource($this->getMultiValue($form_state->getValue('source')));
    $dataset->setRelatedResource($this->getMultiValue($form_state->getValue('related_resource')));
    $dataset->setProvenance($this->getMultiValue($form_state->getValue('provenance')));
    $dataset->setSample($this->getMultiValue($form_state->getValue('sample')));
    $dataset->setConformsTo($this->getMultiValue($form_state->getValue('conforms_to')));
    $dataset->setVersionNotes($this->getMultiValue($form_state->getValue('version_notes')));
    $dataset->setDocumentation($this->getMultiValue($form_state->getValue('documentation')));
    $dataset->setTemporalStart($this->getDateValue($form_state->getValue('temporal_start')));
    $dataset->setTemporalEnd($this->getDateValue($form_state->getValue('temporal_end')));
    $dataset->setIssued($this->getDateValue($form_state->getValue('issued')));
    $dataset->setDatePlanned($this->getDateValue($form_state->getValue('date_planned')));

    // We only send the access_rights_reason when access_rights is non_public.
    $accessRights = $form_state->getValue('access_rights');
    $dataset->setAccessRights($accessRights);
    $dataset->setAccessRightsReason(NULL);
    if ($accessRights && $accessRights === 'http://publications.europa.eu/resource/authority/access-right/NON_PUBLIC') {
      $dataset->setAccessRightsReason($form_state->getValue('access_rights_reason'));
    }

    // Optional spatial multi-field.
    $spatial_scheme = [];
    $spatial_value = [];
    foreach ($form_state->getValue('spatial', []) as $k => $v) {
      if (\is_int($k) && !empty($v['spatial_scheme']) && !empty($v['spatial_value'])) {
        $spatial_scheme[] = $v['spatial_scheme'];
        $spatial_value[] = $v['spatial_value'];
      }
    }
    $dataset->setSpatialScheme($spatial_scheme);
    $dataset->setSpatialValue($spatial_value);

    // Set the current timestamp as modification datetime.
    $dataset->setModified(date('Y-m-d') . 'T' . date('H:i:s'));

    return $dataset;
  }

  /**
   *
   */
  public function addOneAlternateIdentifier(array &$form, FormStateInterface $form_state): void {
    $form_state->set('alternateIdentifierCount', $form_state->get('alternateIdentifierCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreAlternateIdentifierCallback(array &$form, FormStateInterface $form_state) {
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['identifier_wrapper']['alternate_identifier'];
  }

  /**
   *
   */
  public function addOneSpatial(array &$form, FormStateInterface $form_state): void {
    $form_state->set('spatialCount', $form_state->get('spatialCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreSpatialCallback(array &$form, FormStateInterface $form_state) {
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['location_and_temporal']['spatial'];
  }

  /**
   *
   */
  public function addOneSource(array &$form, FormStateInterface $form_state): void {
    $form_state->set('sourceCount', $form_state->get('sourceCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreSourceCallback(array &$form, FormStateInterface $form_state) {
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['source'];
  }

  /**
   *
   */
  public function addOneRelatedResource(array &$form, FormStateInterface $form_state): void {
    $form_state->set('relatedResourceCount', $form_state->get('relatedResourceCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreRelatedResourceCallback(array &$form, FormStateInterface $form_state) {
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['related_resource'];
  }

  /**
   *
   */
  public function addOneConformsTo(array &$form, FormStateInterface $form_state): void {
    $form_state->set('conformsToCount', $form_state->get('conformsToCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreConformsToCallback(array &$form, FormStateInterface $form_state) {
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['conforms_to'];
  }

  /**
   *
   */
  public function addOneProvenance(array &$form, FormStateInterface $form_state): void {
    $form_state->set('provenanceCount', $form_state->get('provenanceCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreProvenanceCallback(array &$form, FormStateInterface $form_state) {
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['rights_and_visibility']['provenance'];
  }

  /**
   *
   */
  public function addOneSample(array &$form, FormStateInterface $form_state): void {
    $form_state->set('sampleCount', $form_state->get('sampleCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreSampleCallback(array &$form, FormStateInterface $form_state) {
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['sample'];
  }

  /**
   *
   */
  public function addOneVersionNotes(array &$form, FormStateInterface $form_state): void {
    $form_state->set('versionNotesCount', $form_state->get('versionNotesCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreVersionNotesCallback(array &$form, FormStateInterface $form_state) {
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['extra_options']['version_wrapper']['version_notes'];
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
    return $form['full_form_wrapper']['wrapper']['main']['advanced']['relationships_and_references']['documentation'];
  }

  public function toggleAdvanced(array &$form, FormStateInterface $form_state) {
    $form_state->set('advanced', !$form_state->get('advanced'));
    $form_state->setRebuild();
    return $form['full_form_wrapper'];
  }

  /**
   * Generate a correct CKAN package name.
   *
   * The name of the new dataset, must be between 2 and 100 characters long and
   * contain only lowercase alphanumeric characters, - and _, e.g.
   * 'warandpeace'
   *
   * @param string $title
   *
   * @return string
   */
  protected function generateName($title): string {
    // Replace non letter with -.
    $title = preg_replace('~[^\pL\d_]+~u', '-', $title);

    // Transliterate.
    $title = iconv('utf-8', 'us-ascii//TRANSLIT', $title);

    // Remove unwanted characters.
    $title = preg_replace('~[^-\w]+~', '', $title);

    // Trim.
    $title = trim($title, '-');

    // Remove duplicate -.
    $title = preg_replace('~-+~', '-', $title);

    // Lowercase.
    $title = strtolower($title);

    // Max 100 characters.
    if (mb_strwidth($title, 'UTF-8') > 100) {
      $title = rtrim(mb_substr($title, 0, 100, 'UTF-8'));
    }

    return $title;
  }

  /**
   * Checks availability of the dataset name and returns the (next) available
   * name.
   *
   * @param string $name
   * @param null|int $number
   *
   * @return string
   */
  protected function checkAvailability($name, $number = NULL): string {
    if ($this->ckanRequest->getDataset($name . ($number ?? ''))) {
      return $this->checkAvailability($name, $number ? $number + 1 : 1);
    }
    return $name . ($number ?? '');
  }

}
