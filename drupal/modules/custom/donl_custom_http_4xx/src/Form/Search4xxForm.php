<?php

namespace Drupal\donl_custom_http_4xx\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\donl_search\Form\SearchForm;

/**
 * Search form for the 4xx pages.
 */
class Search4xxForm extends SearchForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_search_4xx_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = '') {
    $form = parent::buildForm($form, $form_state);

    $form['searchbar']['type_select']['#default_value'] = '';
    if (isset($form['searchbar']['type_select']['#options'][$type])) {
      $form['searchbar']['type_select']['#default_value'] = $type;
    }

    unset($form['facet_wrapper']);

    return $form;
  }

}
