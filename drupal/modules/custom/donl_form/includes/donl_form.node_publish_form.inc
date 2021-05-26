<?php

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function donl_form_donl_form_node_publish_form_alter(&$form, FormStateInterface $form_state) {
  donl_form_general($form, $form_state);

  /** @var \Drupal\node\NodeInterface $node */
  $node = \Drupal::routeMatch()->getParameter('node');

  if ($node->getType() === 'appliance') {
    $form['header'] = [
      '#theme' => 'donl_form_header',
      '#type' => 'application',
      '#summary' => [
        '#theme' => 'donl_form_summary',
        '#title' => t('Application'),
        '#step_title' => t('Unpublish @type', ['@type' => 'application']),
        '#fields' => [
          'title' => ['title' => t('Title'), 'value' => $node->getTitle()],
        ],
      ],
      '#steps' => [
        'create' => [
          '#theme' => 'donl_form_step',
          '#title' => t('Create new @type', ['@type' => 'application']),
          '#short_title' => t('application'),
          '#completed' => TRUE,
        ],
        'finish' => [
          '#theme' => 'donl_form_step',
          '#title' => t('Wrap up'),
          '#short_title' => t('Wrap up'),
          '#icon' => 'icon-connected-globe',
          '#active' => TRUE,
        ],
      ],
    ];
  }
  elseif ($node->getType() === 'dataservice') {
    $form['header'] = [
      '#theme' => 'donl_form_header',
      '#type' => 'dataservice',
      '#summary' => [
        '#theme' => 'donl_form_summary',
        '#title' => t('Dataservice'),
        '#step_title' => t('Unpublish @type', ['@type' => 'dataservice']),
        '#fields' => [
          'title' => ['title' => t('Title'), 'value' => $node->getTitle()],
        ],
      ],
      '#steps' => [
        'create' => [
          '#theme' => 'donl_form_step',
          '#title' => t('Create new @type', ['@type' => 'dataservice']),
          '#short_title' => t('dataservice'),
          '#completed' => TRUE,
        ],
        'finish' => [
          '#theme' => 'donl_form_step',
          '#title' => t('Wrap up'),
          '#short_title' => t('Wrap up'),
          '#icon' => 'icon-connected-globe',
          '#active' => TRUE,
        ],
      ],
    ];
  }
}
