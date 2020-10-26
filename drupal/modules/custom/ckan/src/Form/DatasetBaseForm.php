<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\Entity\Tag;
use Drupal\Core\Form\FormStateInterface;
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
    $user = $this->getUser();
    if ($dataset) {
      if ($user->isAdministrator() || $user->getCkanId() === $dataset->getCreatorUserId()) {
        $form['editLinks'] = [
          '#type' => 'inline_template',
          '#template' => '<div class="container"><div class="buttonswitch">{% for editLink in editLinks %}{{ editLink }}{% endfor %}</div></div>',
          '#context' => [
            'editLinks' => [
              'view' => [
                '#type' => 'link',
                '#title' => $this->t('View'),
                '#url' => Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getName()]),
                '#attributes' => [
                  'class' => ['buttonswitch__button'],
                ],
              ],
              'edit' => [
                '#type' => 'link',
                '#title' => $this->t('Edit'),
                '#url' => Url::fromRoute('ckan.dataset.edit', ['dataset' => $dataset->getName()]),
                '#attributes' => [
                  'class' => ['buttonswitch__button', 'is-active'],
                ],
              ],
              'delete' => [
                '#type' => 'link',
                '#title' => $this->t('Delete'),
                '#url' => Url::fromRoute('ckan.dataset.delete', ['dataset' => $dataset->getId()]),
                '#attributes' => [
                  'class' => ['buttonswitch__button'],
                ],
              ],
              'data-sources' => [
                '#type' => 'link',
                '#title' => $this->t('Manage data sources'),
                '#url' => Url::fromRoute('ckan.dataset.datasources', ['dataset' => $dataset->getId()]),
                '#attributes' => [
                  'class' => ['buttonswitch__button'],
                ],
              ],
            ],
          ],
        ];
      }

      $form['id'] = [
        '#type' => 'hidden',
        '#value' => $dataset->getId(),
      ];
    }

    $form['dataset_data'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dataset data'),
    ];

    $form['dataset_data']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#maxlength' => 256,
      '#default_value' => $dataset !== NULL ? $dataset->getTitle() : NULL,
      '#description' => $this->t('Give a good title to your dataset, give the dataset easy to find for re-users. The title consists of one or more words and, if possible, a year.'),
    ];

    $form['dataset_data']['identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifier'),
      '#default_value' => $dataset !== NULL ? $dataset->getIdentifier() : NULL,
      '#required' => FALSE,
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#description' => $this->t('It must be indicated here, via a URL, where the dataset is originally to be found.'),
    ];

    $form['dataset_data']['alternate_identifier'] = $this->buildFormWrapper($this->t('Alternative identifier'), 'alternate-identifier');
    $form['dataset_data']['alternate_identifier']['#description'] = $this->t('If there is another alternative location where the dataset is shown, this can be entered here.');
    $alternateIdentifier = $dataset !== NULL ? $dataset->getAlternateIdentifier() : [];
    $alternateIdentifierCount = $form_state->get('alternateIdentifierCount');
    if (empty($alternateIdentifierCount)) {
      $alternateIdentifierCount = \count($alternateIdentifier) + 1;
      $form_state->set('alternateIdentifierCount', $alternateIdentifierCount);
    }
    for ($i = 0; $i < $alternateIdentifierCount; $i++) {
      $form['dataset_data']['alternate_identifier'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Alternative identifier') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $alternateIdentifier[$i] ?? NULL,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      ];
    }

    $form['dataset_data']['alternate_identifier']['addAlternateIdentifier'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('identifier')]),
      '#submit' => ['::addOneAlternateIdentifier'],
      '#ajax' => [
        'callback' => '::addMoreAlternateIdentifierCallback',
        'wrapper' => 'alternate-identifier-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['dataset_data']['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#options' => $this->valueList->getList('donl:language'),
      '#multiple' => TRUE,
      '#default_value' => $dataset !== NULL ? $dataset->getLanguage() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Select from the list of values in which language the dataset can be reused.'),
    ];

    $form['dataset_data']['metadata_language'] = [
      '#type' => 'select',
      '#title' => $this->t('Metadata language'),
      '#options' => $this->valueList->getList('donl:language'),
      '#default_value' => $dataset !== NULL ? $dataset->getMetadataLanguage() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Select from the value list in which language the metadata was entered.'),
    ];

    $form['dataset_data']['notes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => $dataset !== NULL ? $dataset->getNotes() : NULL,
      '#description' => $this->t('Give a clear and clear explanation of your dataset. Consider, for example, the contents of the dataset, year(s), the format, possible indications for reusing the dataset, the way in which the data was obtained and the quality of the dataset.'),
    ];

    $form['dataset_data']['tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tags'),
      '#maxlength' => 512,
      '#default_value' => $dataset !== NULL ? implode(', ', $dataset->getTags()) : NULL,
      '#description' => $this->t('You can give multiple tags to this dataset. Tags are keywords that make the data easier to find. We recommend using about 5 tags per dataset.'),
    ];

    $form['dataset_data']['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Themes'),
      '#options' => ['' => $this->t('- Select item -')] + $this->valueList->getPreparedHierarchicalThemeList(),
      '#multiple' => TRUE,
      '#default_value' => $dataset !== NULL ? $dataset->getTheme() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('Click with your mouse on the field and choose a theme that fits the dataset.'),
      '#attributes' => ['style' => 'height: 300px'],
    ];

    $form['dataset_data']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL Landingspage'),
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#default_value' => $dataset !== NULL ? $dataset->getUrl() : NULL,
      '#description' => $this->t('Here you can place a link for more information about (the use) of this dataset.'),
    ];

    $form['dataset_data']['documentation'] = $this->buildFormWrapper($this->t('Documentation'), 'documentation');
    $form['dataset_data']['documentation']['#description'] = $this->t('If you have documentation on the use of the dataset, you can indicate this by placing a link to the documentation here.');
    $documentation = $dataset !== NULL ? $dataset->getDocumentation() : [];
    $documentationCount = $form_state->get('documentationCount');
    if (empty($documentationCount)) {
      $documentationCount = \count($documentation) + 1;
      $form_state->set('documentationCount', $documentationCount);
    }
    for ($i = 0; $i < $documentationCount; $i++) {
      $form['dataset_data']['documentation'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Documentation') . ' ' . $i,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#title_display' => 'invisible',
        '#default_value' => $documentation[$i] ?? NULL,
      ];
    }

    $form['dataset_data']['documentation']['addDocumentation'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('documentation')]),
      '#submit' => ['::addOneDocumentation'],
      '#ajax' => [
        'callback' => '::addMoreDocumentationCallback',
        'wrapper' => 'documentation-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['dataset_data']['sample'] = $this->buildFormWrapper($this->t('Sample'), 'sample');
    $form['dataset_data']['sample']['#description'] = $this->t('If the dataset is used in an application, you can place a link to this application here. In this way you can gain insight into the use of the data.');
    $sample = $dataset !== NULL ? $dataset->getSample() : [];
    $sampleCount = $form_state->get('sampleCount');
    if (empty($sampleCount)) {
      $sampleCount = \count($sample) + 1;
      $form_state->set('sampleCount', $sampleCount);
    }
    for ($i = 0; $i < $sampleCount; $i++) {
      $form['dataset_data']['sample'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Sample') . ' ' . $i,
        '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
        '#title_display' => 'invisible',
        '#default_value' => $sample[$i] ?? NULL,
      ];
    }

    $form['dataset_data']['sample']['addSample'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('sample')]),
      '#submit' => ['::addOneSample'],
      '#ajax' => [
        'callback' => '::addMoreSampleCallback',
        'wrapper' => 'sample-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['dataset_data']['provenance'] = $this->buildFormWrapper($this->t('Provenance'), 'provenance');
    $form['dataset_data']['provenance']['#description'] = $this->t('You can indicate here the provenance of this dataset.');
    $provenance = $dataset !== NULL ? $dataset->getProvenance() : [];
    $provenanceCount = $form_state->get('provenanceCount');
    if (empty($provenanceCount)) {
      $provenanceCount = \count($provenance) + 1;
      $form_state->set('provenanceCount', $provenanceCount);
    }
    for ($i = 0; $i < $provenanceCount; $i++) {
      $form['dataset_data']['provenance'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Dataset') . ' ' . $i,
        '#maxlength' => 256,
        '#title_display' => 'invisible',
        '#default_value' => $provenance[$i] ?? NULL,
      ];
    }

    $form['dataset_data']['provenance']['addProvenance'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('provenance')]),
      '#submit' => ['::addOneProvenance'],
      '#ajax' => [
        'callback' => '::addMoreProvenanceCallback',
        'wrapper' => 'provenance-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['dataset_data']['data_classification'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Data classification'),
      '#tree' => FALSE,
      '#prefix' => '<div class="form__element"><div class="well">',
      '#suffix' => '</div></div>',
    ];

    $form['dataset_data']['data_classification']['high_value'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('High value dataset'),
      '#default_value' => $dataset !== NULL ? $dataset->getHighValue() : 0,
      '#description' => $this->t('Indicate whether this dataset is a high value dataset or not. A high value dataset is data that contributes to a transparent and open government or data that has socio-economic added value for society. More information about high value datasets can be found here: https://data.overheid.nl/high-value-datasets'),
    ];

    $form['dataset_data']['data_classification']['base_register'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Base register'),
      '#default_value' => $dataset !== NULL ? $dataset->getBaseRegister() : 0,
      '#description' => $this->t('Indicate whether this dataset is a basic registration or not. A basic registration is a registration officially designated by the government with information that is compulsory for all government institutions when performing public law tasks. More information can be found here: https://www.digitaleoverheid.nl/dossiers/basisregistraties/'),
    ];

    $form['dataset_data']['data_classification']['reference_data'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reference data'),
      '#default_value' => $dataset !== NULL ? $dataset->getReferenceData() : 0,
      '#description' => $this->t('Indicate whether this dataset is a reference dataset or not. A reference dataset is essential for the use of government data. More information can be found here: https://data.overheid.nl/referencedatasets'),
    ];

    $form['dataset_data']['data_classification']['national_coverage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('National coverage'),
      '#default_value' => $dataset !== NULL ? $dataset->getNationalCoverage() : 0,
      '#description' => $this->t('Indicate whether this dataset is national covered.'),
    ];

    // $form['dataset_data']['spatial'] = $this->buildFormWrapper($this->t('Spatial data'), 'spatial');
    $form['dataset_data']['spatial'] = [];
    $form['dataset_data']['spatial']['#description'] = $this->t('Describe the location the data is about. For example, the Netherlands, your province or your municipality etc.');
    $spatial_scheme = $dataset !== NULL ? $dataset->getSpatialScheme() : [];
    $spatial_value = $dataset !== NULL ? $dataset->getSpatialValue() : [];
    $spatialCount = $form_state->get('spatialCount');
    if (empty($spatialCount)) {
      $spatialCount = \count($spatial_scheme) + 1;
      $form_state->set('spatialCount', $spatialCount);
    }
    for ($i = 0; $i < $spatialCount; $i++) {
      $form['dataset_data']['spatial'][$i]['spatial_scheme'] = [
        // Hidden for now.
        '#type' => 'hidden',
        '#options' => $this->valueList->getList('overheid:spatial_scheme'),
        '#title' => $this->t('Type of data'),
        '#default_value' => $spatial_scheme[$i] ?? NULL,
        '#prefix' => Markup::create('<div style="overflow: hidden;"><div style="width: 49%; float: left;">'),
        '#suffix' => '</div>',
      ];

      $form['dataset_data']['spatial'][$i]['spatial_value'] = [
        // Hidden for now.
        '#type' => 'hidden',
        '#title' => $this->t('Value'),
        '#maxlength' => 256,
        '#default_value' => $spatial_value[$i] ?? NULL,
        '#prefix' => Markup::create('<div style="width: 49%; float: left;">'),
        '#suffix' => '</div></div>',
      ];
    }

    $form['dataset_data']['spatial']['addSpatial'] = [
      // Hidden for now.
      '#type' => 'hidden',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('spatial data')]),
      '#submit' => ['::addOneSpatial'],
      '#ajax' => [
        'callback' => '::addMoreSpatialCallback',
        'wrapper' => 'spatial-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['dataset_data']['version_fields'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Version'),
    ];

    $form['dataset_data']['version_fields']['issued'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Create date'),
      '#default_value' => $dataset !== NULL ? $dataset->getIssued() : NULL,
      '#description' => $this->t('Choose here for the creation date on which this dataset was created on data.overheid.nl.'),
    ];

    $form['dataset_data']['version_fields']['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Version'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getVersion() : NULL,
      '#description' => $this->t('Use this field if a new version of the dataset is regularly placed online. You can, for example, choose version 1.0, 2.0 etc.'),
    ];

    $form['dataset_data']['version_fields']['version_notes'] = $this->buildFormWrapper($this->t('Version notes'), 'version-notes');
    $form['dataset_data']['version_fields']['version_notes']['#description'] = $this->t('If there is a specific reason for updating the dataset (such a corrected error), you can state this here.');
    $versionNotes = $dataset !== NULL ? $dataset->getVersionNotes() : [];
    $versionNotesCount = $form_state->get('versionNotesCount');
    if (empty($versionNotesCount)) {
      $versionNotesCount = \count($versionNotes) + 1;
      $form_state->set('versionNotesCount', $versionNotesCount);
    }
    for ($i = 0; $i < $versionNotesCount; $i++) {
      $form['dataset_data']['version_fields']['version_notes'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Version notes') . ' ' . $i,
        '#maxlength' => 256,
        '#title_display' => 'invisible',
        '#default_value' => $versionNotes[$i] ?? NULL,
      ];
    }

    $form['dataset_data']['version_fields']['version_notes']['addVersionNotes'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('version note')]),
      '#submit' => ['::addOneVersionNotes'],
      '#ajax' => [
        'callback' => '::addMoreVersionNotesCallback',
        'wrapper' => 'version-notes-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['dataset_data']['version_fields']['dataset_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Dataset state'),
      '#options' => $this->valueList->getList('overheid:datasetStatus'),
      '#default_value' => $dataset !== NULL ? $dataset->getDatasetStatus() : NULL,
      '#description' => $this->t('Select the current status of the dataset here. For example: the dataset is available, planned, under investigation or not available.'),
    ];

    $form['dataset_data']['version_fields']['date_planned'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Expected publication date'),
      '#default_value' => $dataset !== NULL ? $dataset->getDatePlanned() : NULL,
      '#description' => $this->t('If you have filled in the status of the dataset in research or planned. Please indicate here the expected publication date on which the dataset is opened or more information can be given about the research whether it can be opened or not.'),
    ];

    $form['dataset_data']['version_fields']['frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Frequency of changes'),
      '#options' => $this->valueList->getList('overheid:frequency'),
      '#default_value' => $dataset !== NULL ? $dataset->getFrequency() : NULL,
      '#description' => $this->t('You can choose from this list how often an update of this dataset takes place. If the desired change frequency is not a selection option in the list, select the option that is irregular or the option closest to the change frequency.'),
    ];

    $form['dataset_data']['temporal'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Time coverage'),
      '#tree' => FALSE,
      '#prefix' => '<div class="form__element"><div class="well">',
      '#suffix' => '</div></div>',
    ];

    $form['dataset_data']['temporal']['temporal_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of the coverage period'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getTemporalLabel() : NULL,
      '#description' => $this->t('If applicable, please describe here the name of the coverage period of this dataset.'),
    ];

    $form['dataset_data']['temporal']['temporal_start'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start coverage period'),
      '#default_value' => $dataset !== NULL ? $dataset->getTemporalStart() : NULL,
      '#description' => $this->t('If applicable, describe here the start and end date of this dataset.'),
    ];

    $form['dataset_data']['temporal']['temporal_end'] = [
      '#type' => 'datetime',
      '#title' => $this->t('End coverage period'),
      '#default_value' => $dataset !== NULL ? $dataset->getTemporalEnd() : NULL,
      '#description' => $this->t('If applicable, describe here the start and end date of this dataset.'),
    ];

    $form['information_about_provider'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Information about the provider'),
    ];

    $options = $this->valueList->getList('donl:organization');
    if ($user && $authority = $user->getAuthority()) {
      $options = array_intersect_key($options, [$authority => 1]);
    }
    $form['information_about_provider']['authority'] = [
      '#type' => 'select',
      '#title' => $this->t('Data owner'),
      '#options' => $options,
      '#default_value' => $dataset !== NULL ? $dataset->getAuthority() : NULL,
      '#attributes' => ['class' => ['chosen']],
      '#required' => TRUE,
      '#description' => $this->t('Select the data owner of the dataset here. The data owner is the organization responsible for the content of the dataset. The data owner will also ask or handle data requests.'),
    ];

    $options = $this->valueList->getList('donl:organization');
    if ($user && $publisher = $user->getPublisher()) {
      $options = array_intersect_key($options, [$publisher => 1]);
    }
    $form['information_about_provider']['publisher'] = [
      '#type' => 'select',
      '#title' => $this->t('Publishing organization'),
      '#options' => $options,
      '#default_value' => $dataset !== NULL ? $dataset->getPublisher() : NULL,
      '#required' => TRUE,
      '#attributes' => ['class' => ['chosen']],
      '#description' => $this->t('The publishing organization (former provider) is an optional field in which an organization is appointed that is responsible for the delivery of the data. It is important to fill this in if it deviates from the organization that is the data owner. If this is the same as the data owner, it will suffice to refer to the data owner himself.'),
    ];

    $form['information_about_provider']['contact_point'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Contact point'),
      '#tree' => FALSE,
      '#prefix' => '<div class="form__element"><div class="well">',
      '#suffix' => '</div></div>',
    ];

    $form['information_about_provider']['contact_point']['contact_point_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointTitle() : NULL,
    ];

    $form['information_about_provider']['contact_point']['contact_point_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department / organization'),
      '#required' => TRUE,
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointName() : NULL,
      '#description' => $this->t('Enter the department / organization where you can be contacted. It is advisable to use a general name in connection with showing the data.'),
    ];

    $form['information_about_provider']['contact_point']['contact_point_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Emailaddress'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointEmail() : NULL,
      '#description' => $this->t('Enter the e-mail address where you can be contacted. It is advisable to use a general e-mail address.'),
    ];

    $form['information_about_provider']['contact_point']['contact_point_website'] = [
      '#type' => 'url',
      '#title' => $this->t('Website'),
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#placeholder' => 'https://data.overheid.nl/',
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointWebsite() : NULL,
      '#description' => $this->t('Enter the website here that gives more information about the organization.'),
    ];

    $form['information_about_provider']['contact_point']['contact_point_phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointPhone() : NULL,
      '#description' => $this->t('Enter the phone details where you can be contacted. It is advisable to use a general number in connection with showing the data.'),
    ];

    $form['information_about_provider']['contact_point']['contact_point_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address data'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getContactPointAddress() : NULL,
      '#description' => $this->t('Enter the address details where contact can be entered. It is advisable to use the general address in connection with showing the data.'),
    ];

    $form['rights_and_visibility'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Rights and visibility'),
    ];

    $form['rights_and_visibility']['license'] = [
      '#type' => 'select',
      '#title' => $this->t('License'),
      '#options' => $this->valueList->getList('overheid:license'),
      '#default_value' => $dataset !== NULL ? $dataset->getLicenseId() : NULL,
      '#required' => TRUE,
      '#description' => $this->t('With a license you indicate what kind of user rights there are on this dataset. For example, Public Domain, CC-0, CC-BY or CC-BY-SA. Click here for more information about the different licenses: https://data.overheid.nl/licenties-voor-hergebruik.'),
    ];

    $form['rights_and_visibility']['restrictions_statement'] = [
      '#type' => 'textarea',
      '#title' => $this->t('License explanation'),
      '#default_value' => $dataset !== NULL ? $dataset->getRestrictionsStatement() : NULL,
    ];

    $form['rights_and_visibility']['access_rights'] = [
      '#type' => 'select',
      '#title' => $this->t('Publicity level'),
      '#options' => $this->valueList->getList('overheid:openbaarheidsniveau'),
      '#default_value' => $dataset !== NULL ? $dataset->getAccessRights() : NULL,
      '#description' => $this->t('Choose whether the access to this dataset is public, restricted or closed. This indicates how this data can be reused. For example: a dataset for which the re-user must log in or register must have restricted access. A dataset that is completely open has access to it.'),
    ];

    $form['rights_and_visibility']['legal_foundation'] = [
      '#type' => 'fieldset',
      // '#title' => $this->t('Legal foundation'),
      '#tree' => FALSE,
      // '#prefix' => '<div class="form__element"><div class="well">',
      //      '#suffix' => '</div></div>',
    ];

    $form['rights_and_visibility']['legal_foundation']['legal_foundation_label'] = [
      // Hidden for now.
      '#type' => 'hidden',
      '#title' => $this->t('Quote title'),
      '#maxlength' => 128,
      '#default_value' => $dataset !== NULL ? $dataset->getLegalFoundationLabel() : NULL,
      '#description' => $this->t('Enter the legal title (if applicable). More information about the reference to legislation and regulations can be found here: https://www.overheid.nl/help/wet-en-regelgeving/verwijzen-naar-wet-en-reggeving'),
    ];

    $form['rights_and_visibility']['legal_foundation']['legal_foundation_uri'] = [
      // Hidden for now.
      '#type' => 'hidden',
      '#title' => $this->t('Link'),
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#default_value' => $dataset !== NULL ? $dataset->getLegalFoundationUri() : NULL,
      '#description' => $this->t('In this field you can link to the scheme that forms the basis for these data. More information about the reference to legislation and regulations can be found here: https://www.overheid.nl/help/wet-en-regelgeving/verwijzen-naar-wet-en-reggeving'),
    ];

    $form['rights_and_visibility']['legal_foundation']['legal_foundation_ref'] = [
      // Hidden for now.
      '#type' => 'hidden',
      '#title' => $this->t('Juriconnect reference'),
      '#maxlength' => self::MAXLENGTH_TEXTFIELD_URL,
      '#default_value' => $dataset !== NULL ? $dataset->getLegalFoundationRef() : NULL,
      '#description' => $this->t("Making a correct Juriconnect reference requires some technical knowledge. At https://wetten.overheid.nl you are therefore helped to compile such a reference. With each control item (an article, a chapter, etc.) you can choose via the right drop-down menu to create a 'Permanent link'. More information about the reference to legislation and regulations can be found here: https://www.overheid.nl/help/wet-en-regelgeving/verwijzen-naar-wet-en-reggeving"),
    ];

    $form['relationships_and_references'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Relationships and references'),
    ];

    $form['relationships_and_references']['related_resource'] = $this->buildFormWrapper($this->t('Related resources'), 'related-resource');
    $form['relationships_and_references']['related_resource']['#description'] = $this->t('If there are a number of related datasets on this dataset, you can indicate this here.');
    $relatedResource = $dataset !== NULL ? $dataset->getRelatedResource() : [];
    $relatedResourceCount = $form_state->get('relatedResourceCount');
    if (empty($relatedResourceCount)) {
      $relatedResourceCount = \count($relatedResource) + 1;
      $form_state->set('relatedResourceCount', $relatedResourceCount);
    }
    for ($i = 0; $i < $relatedResourceCount; $i++) {
      $form['relationships_and_references']['related_resource'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Related resource') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $relatedResource[$i] ?? NULL,
      ];
    }

    $form['relationships_and_references']['related_resource']['addRelatedResource'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('related resource')]),
      '#submit' => ['::addOneRelatedResource'],
      '#ajax' => [
        'callback' => '::addMoreRelatedResourceCallback',
        'wrapper' => 'related-resource-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['relationships_and_references']['conforms_to'] = $this->buildFormWrapper($this->t('Conforms to the following standards'), 'conforms-to');
    $form['relationships_and_references']['conforms_to']['#description'] = $this->t('If a certain standard is used in this dataset (such as juriconnect or an ISO standard), you can indicate this here. Please use the url to the standard.');
    $conformsTo = $dataset !== NULL ? $dataset->getConformsTo() : [];
    $conformsToCount = $form_state->get('conformsToCount');
    if (empty($conformsToCount)) {
      $conformsToCount = \count($conformsTo) + 1;
      $form_state->set('conformsToCount', $conformsToCount);
    }
    for ($i = 0; $i < $conformsToCount; $i++) {
      $form['relationships_and_references']['conforms_to'][$i] = [
        '#type' => 'url',
        '#title' => $this->t('Standard') . ' ' . $i,
        '#title_display' => 'invisible',
        '#placeholder' => 'https://www.iso.org/obp/ui/#iso:std:iso:22886:dis:ed-1:v1:en',
        '#default_value' => $conformsTo[$i] ?? NULL,
      ];
    }

    $form['relationships_and_references']['conforms_to']['addConformsTo'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('standard')]),
      '#submit' => ['::addOneConformsTo'],
      '#ajax' => [
        'callback' => '::addMoreConformsToCallback',
        'wrapper' => 'conforms-to-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $options = $this->valueList->getList('donl:catalogs');
    if ($user && $catalogs = $user->getCatalogs()) {
      $options = array_intersect_key($options, $catalogs);
    }
    $form['relationships_and_references']['source_catalog'] = [
      '#type' => 'select',
      '#title' => $this->t('Source Catalog'),
      '#options' => $options,
      '#default_value' => $dataset !== NULL ? $dataset->getSourceCatalog() : 'https://data.overheid.nl',
      '#required' => TRUE,
      '#description' => $this->t('Choose here for the source in which catalog the dataset is included.'),
    ];

    $form['relationships_and_references']['source'] = $this->buildFormWrapper($this->t('This dataset is based on'), 'source');
    $form['relationships_and_references']['source']['#description'] = $this->t('If there are datasets that are based on this dataset, you can indicate this here.');
    $source = $dataset !== NULL ? $dataset->getSource() : [];
    $sourceCount = $form_state->get('sourceCount');
    if (empty($sourceCount)) {
      $sourceCount = \count($source) + 1;
      $form_state->set('sourceCount', $sourceCount);
    }
    for ($i = 0; $i < $sourceCount; $i++) {
      $form['relationships_and_references']['source'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Dataset') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $source[$i] ?? NULL,
      ];
    }

    $form['relationships_and_references']['source']['addSource'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('dataset')]),
      '#submit' => ['::addOneSource'],
      '#ajax' => [
        'callback' => '::addMoreSourceCallback',
        'wrapper' => 'source-wrapper',
      ],
      '#limit_validation_errors' => [],
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
   * Return all the values as a Dataset object.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\ckan\Entity\Dataset
   */
  protected function getValues(FormStateInterface $form_state): Dataset {
    $dataset = new Dataset();
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
    $dataset->setNotes($form_state->getValue('notes'));
    $dataset->setMetadataLanguage($form_state->getValue('metadata_language'));
    $dataset->setTheme(array_filter(array_values($form_state->getValue('theme'))));
    $dataset->setHighValue($form_state->getValue('high_value'));
    $dataset->setBaseRegister($form_state->getValue('base_register'));
    $dataset->setReferenceData($form_state->getValue('reference_data'));
    $dataset->setNationalCoverage($form_state->getValue('national_coverage'));

    // Set the name if it is a new dataset.
    if (!$form_state->getValue('id')) {
      $dataset->setName($this->checkAvailability($this->generateName($dataset->getTitle())));
    }

    // Set the identifier of the dataset.
    if ($form_state->getValue('identifier')) {
      $dataset->setIdentifier($form_state->getValue('identifier'));
    }
    else {
      $dataset->setIdentifier(\Drupal::request()->getSchemeAndHttpHost() . '/dataset/' . $dataset->getName());
    }

    // Optional values.
    $dataset->setUrl($form_state->getValue('url'));
    $dataset->setContactPointWebsite($form_state->getValue('contact_point_website'));
    $dataset->setContactPointEmail($form_state->getValue('contact_point_email'));
    $dataset->setContactPointAddress($form_state->getValue('contact_point_address'));
    $dataset->setContactPointPhone($form_state->getValue('contact_point_phone'));
    $dataset->setContactPointTitle($form_state->getValue('contact_point_title'));
    $dataset->setAccessRights($form_state->getValue('access_rights'));
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
    return $form['dataset_data']['alternate_identifier'];
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
    return $form['dataset_data']['spatial'];
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
    return $form['relationships_and_references']['source'];
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
    return $form['relationships_and_references']['related_resource'];
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
    return $form['relationships_and_references']['conforms_to'];
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
    return $form['dataset_data']['provenance'];
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
    return $form['dataset_data']['sample'];
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
    return $form['dataset_data']['version_fields']['version_notes'];
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
    return $form['dataset_data']['documentation'];
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
