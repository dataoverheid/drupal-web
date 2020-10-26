<?php

/**
 * @file
 * Contains form alter callback for install_configure_form.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Form alter callback for install_configure_form.
 *
 * @param array $form
 *   An associative array containing the structure of the form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form.
 *
 * @see indicia_profile_form_install_configure_form_alter()
 */
function indicia_profile_install_configure_form_alter(array &$form, FormStateInterface $form_state) {
  // Pre-populate site information.
  $form['site_information']['site_name']['#default_value'] = 'Indicia';
  $form['site_information']['site_mail']['#default_value'] = 'drupal@indicia.nl';

  // Pre-populate account settings.
  $form['admin_account']['account']['name']['#default_value'] = 'Indicia';
  $form['admin_account']['account']['mail']['#default_value'] = 'drupal@indicia.nl';

  // Pre-populate regional settings.
  $form['regional_settings']['site_default_country']['#default_value'] = 'NL';
  $form['regional_settings']['date_default_timezone']['#default_value'] = 'Europe/Amsterdam';

  // Pre-populate update notifications.
  $form['update_notifications']['#access'] = FALSE;
  $form['update_notifications']['enable_update_status_module']['#value'] = NULL;
  $form['update_notifications']['enable_update_status_emails']['#value'] = NULL;
}
