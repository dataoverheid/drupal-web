<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function donl_form_form_node_appliance_form_alter(&$form, FormStateInterface $form_state) {
  $form['#title'] = t('Create new @type', ['@type' => 'application']);

  $cancelUrl = Url::fromRoute('donl_search.search.application');
  $form['actions']['cancel'] = Link::fromTextAndUrl(t('Cancel'), $cancelUrl)->toRenderable();

  $form['actions']['submit']['#submit'][] = 'donl_form_general_form_alter_submit';

  donl_form_appliance($form, $form_state);
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function donl_form_form_node_appliance_edit_form_alter(&$form, FormStateInterface $form_state) {
  $form['#title'] = t('Edit @type', ['@type' => 'application']);

  $cancelUrl = Url::fromRoute('donl.application', ['application' => $form_state->getFormObject()->getEntity()->id()]);
  $form['actions']['cancel'] = Link::fromTextAndUrl(t('Cancel'), $cancelUrl)->toRenderable();

  donl_form_appliance($form, $form_state);
}

/**
 * Generic form_alter implementation for the appliance forms.
 */
function donl_form_appliance(&$form, FormStateInterface $form_state) {
  donl_form_general($form, $form_state);

  $form['header'] = [
    '#theme' => 'donl_form_header',
    '#type' => 'application',
    '#summary' => [
      '#theme' => 'donl_form_summary',
      '#title' => t('Application'),
      '#step_title' => t('Create new @type', ['@type' => 'application']),
      '#fields' => [
        'title' => ['title' => t('Title'), 'field' => 'title[0][value]'],
      ],
    ],
    '#steps' => [
      'create' => [
        '#theme' => 'donl_form_step',
        '#title' => t('Create new @type', ['@type' => 'application']),
        '#short_title' => t('application'),
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
