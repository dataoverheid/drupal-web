<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function donl_form_form_node_dataservice_form_alter(&$form, FormStateInterface $form_state) {
  $form['#attributes']['class'][] = 'donl-form-create';
  $form['#title'] = t('Create new @type', ['@type' => 'dataservice']);

  $cancelUrl = Url::fromRoute('donl_search.search.dataservice');
  $form['actions']['cancel'] = Link::fromTextAndUrl(t('Cancel'), $cancelUrl)->toRenderable();

  $form['actions']['submit']['#submit'][] = 'donl_form_general_form_alter_submit';

  donl_form_dataserivce($form, $form_state);
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function donl_form_form_node_dataservice_edit_form_alter(&$form, FormStateInterface $form_state) {
  $form['#attributes']['class'][] = 'donl-form-edit';
  $form['#title'] = t('Edit @type', ['@type' => 'dataservice']);

  $cancelUrl = Url::fromRoute('donl.dataservice', ['dataservice' => $form_state->getFormObject()->getEntity()->id()]);
  $form['actions']['cancel'] = Link::fromTextAndUrl(t('Cancel'), $cancelUrl)->toRenderable();

  donl_form_dataserivce($form, $form_state);
}

/**
 * Generic form_alter implementation for the dataservice forms.
 */
function donl_form_dataserivce(&$form, FormStateInterface $form_state) {
  donl_form_general($form, $form_state);
  $valueList = \Drupal::service('donl.value_list');

  $valueListFields = [
    'dataserivce_state' => 'overheid:datasetStatus',
    'dataservice_supplier' => 'donl:organization',
    'dataservice_owner' => 'donl:organization',
    'dataservice_license' => 'overheid:license',
    'dataservice_api_output' => 'iana:mediatype',
  ];
  foreach ($valueListFields as $field => $list) {
    $form[$field]['widget'][0]['value']['#type'] = 'select';
    $form[$field]['widget'][0]['value']['#options'] = $valueList->getList($list);
    $form[$field]['widget'][0]['value']['#empty_option'] = t('- Select item -');
    $form[$field]['widget'][0]['value']['#size'] = 1;
    $form[$field]['widget'][0]['value']['#attributes']['class'][] = 'select2';
  }

  // @todo find a way to automatically add this attribute for default values.
  $form['dataservice_costs_for_use']['widget']['#attributes']['data-default-value'] = 'unknown';

  $form['relation_dataservice_dataset']['#states'] = [
    'visible' => [
      ':input[name="dataservice_location_datasets"]' => ['value' => '3'],
    ],
  ];
  $form['relation_dataservice_dataset']['widget'][0]['value']['#states'] = [
    'required' => [
      ':input[name="dataservice_location_datasets"]' => ['value' => '3'],
    ],
  ];

  $form['dataservice_explanation_costs']['#states'] = [
    'visible' => [
      ':input[name="dataservice_costs_for_use"]' => ['value' => 'yes'],
    ],
  ];
  $form['dataservice_explanation_costs']['widget'][0]['value']['#states'] = [
    'required' => [
      ':input[name="dataservice_costs_for_use"]' => ['value' => 'yes'],
    ],
  ];

  $form['header'] = [
    '#theme' => 'donl_form_header',
    '#type' => 'dataservice',
    '#summary' => [
      '#theme' => 'donl_form_summary',
      '#title' => t('Dataservice'),
      '#step_title' => t('Create new @type', ['@type' => 'dataservice']),
      '#fields' => [
        'title' => ['title' => t('Title'), 'field' => 'title[0][value]'],
      ],
    ],
    '#steps' => [
      'create' => [
        '#theme' => 'donl_form_step',
        '#title' => t('Create new @type', ['@type' => 'dataservice']),
        '#short_title' => t('dataservice'),
        '#icon' => 'icon-title',
        '#active' => TRUE,
        '#sub_steps' => [],
      ],
      'finish' => [
        '#theme' => 'donl_form_step',
        '#title' => t('Wrap up'),
        '#short_title' => t('Wrap up'),
        '#icon' => 'icon-connected-globe',
      ],
    ],
  ];

  $form['sidebar'] = [
    '#theme' => 'donl_form_sidebar',
  ];
}
