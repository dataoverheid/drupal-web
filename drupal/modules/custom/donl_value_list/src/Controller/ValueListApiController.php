<?php

namespace Drupal\donl_value_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\field\Entity\FieldConfig;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class ValueListApiController extends ControllerBase {

  /**
   * @var \Drupal\taxonomy\TermStorage
   */
  protected $termStorage;

  /**
   * @var \Drupal\taxonomy\VocabularyStorage
   */
  protected $vocabularyStorage;

  /**
   *
   */
  public function __construct() {
    $this->termStorage = $this->entityTypeManager()->getStorage('taxonomy_term');
    $this->vocabularyStorage = $this->entityTypeManager()->getStorage('taxonomy_vocabulary');
  }

  /**
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function overview() {
    // Get the available vocabulary and return their link.
    $links = array_map(static function (Vocabulary $vocabulary) {
      return Link::createFromRoute($vocabulary->get('name'), 'donl_value_list.value_list', ['vid' => $vocabulary->get('vid')])->toString();
    }, $this->vocabularyStorage->loadMultiple());

    // Allow other modules to add data.
    $this->moduleHandler()->alter('value_list_service', $links);

    // Some HTML for displaying a nice list.
    asort($links);
    $html = '<div class="item-list"><h3>Waardelijsten</h3><ul><li>' . implode('</li><li>', $links) . '</li></ul>';

    return new Response($html);
  }

  /**
   * @param string $vid
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function taxonomyList($vid) {
    $data = [];

    // Get the terms data for the JSON response.
    if ($terms = $this->termStorage->loadTree($vid, 0, 1, TRUE)) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($terms as $term) {
        $data[] = [
          'name' => $term->getName(),
          'description' => $term->getDescription(),
        ] + $this->getCustomFields($term) + $this->getChildTerms($vid, $term);
      }
    }

    return new JsonResponse($data);
  }

  /**
   * @param \Drupal\taxonomy\TermInterface $term
   *
   * @return array
   */
  private function getCustomFields(TermInterface $term) {
    $fields = [];

    // Get the custom fields (FieldDefinition == FieldConfig) and get the value.
    foreach ($term->getFields() as $name => $fieldItemList) {
      if ($fieldItemList->getFieldDefinition() instanceof FieldConfig) {
        $fields[$name] = $fieldItemList->getValue()[0]['value'];
      }
    }

    return $fields;
  }

  /**
   * @param string $vid
   * @param \Drupal\taxonomy\TermInterface $term
   *
   * @return array
   */
  private function getChildTerms($vid, TermInterface $term) {
    $data = [];

    // Are there children? If so, get the data recursive.
    if ($terms = $this->termStorage->loadTree($vid, $term->id(), 1, TRUE)) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($terms as $t) {
        $data[] = [
          'name' => $t->getName(),
          'description' => $t->getDescription(),
        ] + $this->getCustomFields($t) + $this->getChildTerms($vid, $t);
      }
    }

    return !empty($data) ? ['children' => $data] : [];
  }

}
