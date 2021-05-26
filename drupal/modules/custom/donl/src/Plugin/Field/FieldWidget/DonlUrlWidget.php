<?php

namespace Drupal\donl\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Field\Plugin\Field\FieldWidget\UriWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'donl_url' widget.
 *
 * @FieldWidget(
 *   id = "donl_url",
 *   module = "donl",
 *   label = @Translation("DONL Url"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class DonlUrlWidget extends UriWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['placeholder'] = 'https://';
    return $settings;
  }

  /**
   * Form element validation handler for #type 'url'.
   */
  public static function validateUri($element, FormStateInterface $form_state, $form) {
    $value = trim($element['#value']);
    $form_state->setValueForElement($element, $value);

    if ($value !== '' && !UrlHelper::isValid($value, TRUE)) {
      $form_state->setError($element, t('The URL %url is not valid.', ['%url' => $value]));
    }
  }

}
