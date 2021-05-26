<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function donl_form_form_node_dataservice_delete_form_alter(&$form, FormStateInterface $form_state) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $form_state->getFormObject()->getEntity();

  $form['#theme'] = 'donl_form';
  $form['#attributes']['class'][] = 'donl-form';
  $form['#attached']['library'][] = 'donl_form/donl-form-content-type';

  unset($form['actions']);

  $form['description'] = [
    '#type' => 'html_tag',
    '#tag' => 'p',
    '#value' => t('Are you sure you want to delete the @entityType %title? This action cannot be undone.', [
      '@entityType' => t('dataservice'),
      '%title' => $node->getTitle(),
    ]),
  ];

  $form['submit'] = [
    '#type' => 'submit',
    '#value' => t('Delete'),
    '#attributes' => ['class' => ['button', 'button--primary']],
  ];

  $form['cancel'] = [
    '#title' => t('Cancel'),
    '#type' => 'link',
    '#url' =>  Url::fromRoute('donl.dataservice', ['dataservice' => $node->id()]),
  ];

  $form['header'] = [
    '#theme' => 'donl_form_header',
    '#type' => 'dataservice',
    '#summary' => [
      '#theme' => 'donl_form_summary',
      '#title' => t('Dataservice'),
      '#step_title' => ucfirst((string) t('delete @type', ['@type' => t('dataservice')])),
      '#fields' => [
        'title' => ['title' => t('Title'), 'value' => $node->getTitle()],
      ],
    ],
    '#steps' => [
      'delete' => [
        '#theme' => 'donl_form_step',
        '#title' => t('Delete'),
        '#short_title' => t('Delete'),
        '#icon' => 'icon-bin',
        '#active' => TRUE,
      ],
    ],
  ];
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function donl_form_form_node_appliance_delete_form_alter(&$form, FormStateInterface $form_state) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $form_state->getFormObject()->getEntity();

  $form['#theme'] = 'donl_form';
  $form['#attributes']['class'][] = 'donl-form';
  $form['#attached']['library'][] = 'donl_form/donl-form-content-type';

  unset($form['actions']);

  $form['description'] = [
    '#type' => 'html_tag',
    '#tag' => 'p',
    '#value' => t('Are you sure you want to delete the @entityType %title? This action cannot be undone.', [
      '@entityType' => t('application'),
      '%title' => $node->getTitle(),
    ]),
  ];

  $form['submit'] = [
    '#type' => 'submit',
    '#value' => t('Delete'),
    '#attributes' => ['class' => ['button', 'button--primary']],
  ];

  $form['cancel'] = [
    '#title' => t('Cancel'),
    '#type' => 'link',
    '#url' =>  Url::fromRoute('donl.application', ['application' => $node->id()]),
  ];

  $form['header'] = [
    '#theme' => 'donl_form_header',
    '#type' => 'application',
    '#summary' => [
      '#theme' => 'donl_form_summary',
      '#title' => t('Application'),
      '#step_title' => ucfirst((string) t('delete @type', ['@type' => t('application')])),
      '#fields' => [
        'title' => ['title' => t('Title'), 'value' => $node->getTitle()],
      ],
    ],
    '#steps' => [
      'delete' => [
        '#theme' => 'donl_form_step',
        '#title' => t('Delete'),
        '#short_title' => t('Delete'),
        '#icon' => 'icon-bin',
        '#active' => TRUE,
      ],
    ],
  ];
}
