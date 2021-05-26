<?php

namespace Drupal\donl_community\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\donl_community\Entity\Community;
use Drupal\donl_search\Form\SearchForm;

/**
 * Community search form.
 */
class CommunitySearchForm extends SearchForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_community_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Community $community = NULL, $showTags = FALSE) {
    if ($community === NULL) {
      return NULL;
    }

    $form = parent::buildForm($form, $form_state);

    // For now we override the values as a community can't search on everything.
    $form['searchbar']['type_select']['#options'] = $this->donlEntitiesService->getEntitiesAsOptionsList('community_search');

    // Get tag cloud.
    if ($showTags && $tagCloud = $this->solrRequest->getTagCloud($community->getIdentifier())) {
      $form['tag_cloud'] = [
        '#type' => 'container',
        '#title' => $this->t('Tag cloud'),
        '#display_title' => 'invisible',
      ];

      $tags = \array_slice($tagCloud, 0, 10);
      $form['tag_cloud'][] = [
        '#type' => 'markup',
        '#markup' => '<span>' . $this->t('Suggestions') . '</span>',
      ];
      foreach ($tags as $tag) {
        $form['tag_cloud'][] = [
          '#type' => 'markup',
          '#markup' => Link::createFromRoute($tag, 'donl_community.search.dataset', [
            'community' => $community->getMachineName(),
          ], ['attributes' => ['class' => ['label']]])->toString(),
        ];
      }
    }

    $form['searchbar']['submit-wrapper']['submit']['#value'] = $this->t('Find');
    $form['#attached']['drupalSettings']['donl_search']['community_sys_name'] = $community->getMachineName();
    $form['#attached']['drupalSettings']['donl_search']['suggestor_url'] = '/suggest/community/';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $options = [];
    if ($search = $form_state->getValue('search')) {
      $options['query']['search'] = $search;
    }

    $params = [
      'page' => 1,
      'recordsPerPage' => 10,
    ];

    /** @var \Drupal\donl_community\Entity\Community $community */
    if ($community = $form_state->getBuildInfo()['args'][0] ?? NULL) {
      $params['community'] = $community->getMachineName();
    }

    // Get the correct redirect route based on the selected type.
    $route = $this->getSearchRoute($form_state->getValue('type_select'), TRUE);
    $form_state->setRedirect($route, $params, $options);
  }

}
