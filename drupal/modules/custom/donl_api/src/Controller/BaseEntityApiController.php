<?php

namespace Drupal\donl_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeFieldItemList;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Base class for a JSON API entity endpoint.
 */
abstract class BaseEntityApiController extends ControllerBase {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The solr request.
   *
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * BaseJsonEntityApiController constructor.
   *
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   *   The solr request.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(SolrRequestInterface $solrRequest, RequestStack $requestStack) {
    $this->nodeStorage = $this->entityTypeManager()->getStorage('node');
    $this->solrRequest = $solrRequest;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('request_stack')
    );
  }

  /**
   * Get the content type.
   *
   * @return string
   *   The type.
   */
  abstract protected function getType(): string;

  /**
   * Get the search type.
   *
   * @return string
   *   The type.
   */
  protected function getSearchType(): string {
    return $this->getType();
  }

  /**
   * Return a single entity as json.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getEntity(): JsonResponse {
    $data = [];

    if ($node = $this->loadNode($this->getType())) {
      $data = $this->normalizeNodeValues($node);
    }
    return $this->jsonResponse($data);
  }

  /**
   * Return a search request as json.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function search(): JsonResponse {
    $values = $this->request->query->all();
    $page = (int) ($values['page'] ?? 1);
    $recordsPerPage = (int) ($values['records_per_page'] ?? 10);
    $recordsPerPage = ($recordsPerPage > 200 ? 200 : $recordsPerPage);
    $sort = $values['sort'] ?? 'sys_modified desc';
    $search = isset($values['search']) ? trim($values['search']) : NULL;
    unset($values['sort'], $values['search'], $values['spellcheck'], $values['page'], $values['records_per_page']);

    $data = [];
    if ($result = $this->solrRequest->search($page, $recordsPerPage, $search, $sort, $this->getSearchType(), $values)) {
     $rows = [];
     foreach ($result['rows'] ?? [] as $row) {
       $rows[] = [
         'id' => $row->id,
         'name' => $row->name,
         'title' => $row->title,
         'uri' => $row->uri,
         'description' => $row->description,
         'theme' => $row->theme,
         'created' => $row->metadata_created,
         'modified' => $row->metadata_modified,
       ];
     }

      $data = [
        'count' => $result['numFound'] ?? 0,
        'results' => $rows,
      ];
    }

    return $this->jsonResponse($data);
  }

  /**
   * Helper function to output a JSON response.
   *
   * @param array $data
   *   The data.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  protected function jsonResponse(array $data): JsonResponse {
    if ($data) {
      return new JsonResponse([
        'success' => TRUE,
        'result' => $data,
      ]);
    }

    return new JsonResponse([
      'success' => FALSE,
      'error' => [
        'message' => 'Not found',
        '__type' => 'Not Found Error',
      ],
    ]);
  }

  /**
   * Normalizes the node values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A Drupal node.
   *
   * @return array
   *   The normalized values.
   */
  protected function normalizeNodeValues(NodeInterface $node): array {
    $result = [];
    foreach ($node->getFields() as $key => $fieldItemList) {
      if ($fieldItemList->access('view') && !in_array($key, $this->getSkipFields(), TRUE)) {
        if (($value = $this->normalizeField($key, $fieldItemList)) !== NULL) {
          $result[$key] = $value;
        }
        else {
          $value = $this->getValue($fieldItemList);
          $result[$key] = $value;

          if (in_array($key, $this->getIntegerFields(), TRUE)) {
            $result[$key] = (int) $value;
          }
        }
      }
    }

    return $result;
  }

  /**
   * Get a list of fields that shouldn't be show.
   *
   * @return array
   *   A list of fields.
   */
  protected function getSkipFields(): array {
    return [
      'content_translation_source',
      'content_translation_outdated',
      'default_langcode',
      'menu_link',
      'metatag',
      'promote',
      'revision_default',
      'revision_log',
      'revision_translation_affected',
      'revision_timestamp',
      'revision_uid',
      'rh_action',
      'rh_redirect',
      'rh_redirect_response',
      'status',
      'sticky',
      'uid',
    ];
  }

  /**
   * Get a list of fields that should be type casted to int.
   *
   * @return array
   *   A list of fields.
   */
  protected function getIntegerFields(): array {
    return [
      'changed',
      'created',
      'nid',
      'vid',
    ];
  }

  /**
   * Helper function to allow the normalization of specific fields.
   *
   * @return mixed|null
   *   The normalized value or null if the field wasn't normalized.
   */
  protected function normalizeField(string $key, FieldItemListInterface $fieldItemList) {
    if ($key === 'type') {
      return $fieldItemList->getValue()[0]['target_id'] ?? '';
    }

    if ($key === 'path') {
      return $fieldItemList->getValue()[0]['alias'] ?? '';
    }

    if ($key === 'datasets') {
      $datasets = [];
      foreach ($fieldItemList->getValue() as $value) {
        $datasets[] = [
          'type' => 'dataset',
          'identifier' => $value['value']
        ];
      }
      return $datasets;
    }

    return NULL;
  }

  /**
   * Load the node object.
   *
   * @param string $type
   *   The content type.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node.
   */
  protected function loadNode(string $type): ?NodeInterface {
    if ($id = $this->request->query->get('id')) {
      if (($node = $this->nodeStorage->load($id)) && $node->getType() === $type) {
        return $node;
      }
    }

    if ($name = $this->request->query->get('name')) {
      $properties = [
        'machine_name' => $name,
        'type' => $type,
      ];
      if (($nodes = $this->nodeStorage->loadByProperties($properties)) && ($node = reset($nodes)) && $node->getType() === $type) {
        return $node;
      }
    }

    return NULL;
  }

  /**
   * Get the normalized value.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $fieldItemList
   *   The field.
   *
   * @return mixed
   *   The value.
   */
  private function getValue(FieldItemListInterface $fieldItemList) {
    $fieldDefinitions = $fieldItemList->getFieldDefinition();
    $cardinality = $fieldDefinitions->getFieldStorageDefinition()->getCardinality();
    $value = $fieldItemList->getValue();

    if ($fieldItemList instanceof DateRangeFieldItemList && isset($value[0]['value'])) {
      $return = [];
      foreach ($value as $v) {
        $return[] = [
          'start' => $v['value'],
          'end' => $v['end_value'] ?? NULL,
        ];
      }
      return ($cardinality === 1 ? $return[0] : $return);
    }

    if (in_array($fieldDefinitions->getType(), ['list_string', 'list_integer', 'list_float'])) {
      $allowedValues = $fieldDefinitions->getFieldStorageDefinition()->getSetting('allowed_values');
      if (isset($value[0]['value'])) {
        $return = [];
        foreach ($value as $v) {
          $return[] = $allowedValues[$v['value']];
        }
        return ($cardinality === 1 ? $return[0] : $return);
      }
    }

    if (isset($value[0]['value'])) {
      $return = [];
      foreach ($value as $v) {
        $return[] = $v['value'];
      }
      return ($cardinality === 1 ? $return[0] : $return);
    }

    return $value;
  }

}
