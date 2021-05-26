<?php

namespace Drupal\donl_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Defines the form that processes the result page pagination filters.
 */
class PaginationFiltersForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'donl_search_pagination_filters_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $recordsPerPage = 10, $routeName = NULL, array $routeParams = []): array {
    $form_state->set('routeName', $routeName);
    $form_state->set('routeParams', $routeParams);

    $form['amount'] = [
      '#type' => 'select',
      '#title' => $this->t('Amount per page'),
      '#options' => [10 => 10, 25 => 25, 50 => 50, 100 => 100, 200 => 200],
      '#default_value' => $recordsPerPage,
      '#attributes' => [
        'title' => $this->t('Select the amount of results that will be displayed'),
        'class' => ['pagination-filters-amount'],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Refresh'),
      '#attributes' => [
        'class' => ['pagination-filters-submit'],
      ],
      '#prefix' => Markup::create('<noscript>'),
      '#suffix' => Markup::create('</noscript>'),
    ];

    $form['#attached']['library'][] = 'donl_search/page_pagination_filters';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $route_name = $form_state->get('routeName');

    if (!empty($route_name)) {
      $params = $form_state->get('routeParams');
      $params['page'] = 1;
      $params['recordsPerPage'] = $form_state->getValue('amount', 10);

      $form_state->setRedirectUrl(Url::fromRoute($route_name, $params));
    }
  }

}
