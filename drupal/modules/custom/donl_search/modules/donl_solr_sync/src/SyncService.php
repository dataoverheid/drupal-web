<?php

namespace Drupal\donl_solr_sync;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\node\Entity\Node;

/**
 *
 */
abstract class SyncService implements SyncServiceInterface {

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   *
   */
  public function __construct(SolrRequestInterface $solrRequest, EntityTypeManagerInterface $entityTypeManager) {
    $this->solrRequest = $solrRequest;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function sync(Node $node, $action) {
    if ($action === 'update') {
      // We handle the case of an unpublished node as a delete request in SOLR.
      if (!$node->get('status')->getValue()[0]['value']) {
        $this->delete($node);
      }
      else {
        $this->update($node);
      }
    }
    elseif ($action === 'delete') {
      $this->delete($node);
    }
  }

  /**
   * Helper function to get a value from a node object.
   *
   * @param \Drupal\node\Entity\Node $node
   * @param string $key
   *
   * @return mixed|null
   */
  protected function getNodeValue(Node $node, $key) {
    if ($node->hasField($key)) {
      return $node->get($key)->getValue()[0]['value'] ?? NULL;
    }
    return NULL;
  }

  /**
   * Return all values that can be indexed from the paragraphs data.
   *
   * @param \Drupal\node\Entity\Node $node
   * @param string $key
   *
   * @return array
   */
  protected function getParagraphData(Node $node, $key) {
    try {
      $paragraphStorage = $this->entityTypeManager->getStorage('paragrah');
    }
    catch (\Exception $e) {
      return [];
    }

    $summary = [];
    if ($node->hasField($key) && ($paragraphs = $node->get($key)->getValue())) {
      foreach ($paragraphs as $value) {
        if (!empty($value['target_id']) && ($paragraph = $paragraphStorage->load($value['target_id']))) {
          if ($value = $this->cleanupText($paragraph->getSummary())) {
            $summary[] = $value;
          }
        }
      }
    }

    return $summary;
  }

  /**
   * Cleanup the text for a better display.
   *
   * Note this function is not a sanitizer function.
   *
   * @param mixed $text
   * @param string $allowable_tags
   *
   * @return string
   */
  protected function cleanupText($text, $allowable_tags = '<a>') {
    $text = (string) $text;
    $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);
    $text = strip_tags($text, $allowable_tags);
    $text = preg_replace("/[\r\n]+/", "\n", $text);
    return trim($text);
  }

  /**
   * Helper function to get a select list key from a node object.
   *
   * @param \Drupal\node\Entity\Node $node
   * @param string $key
   *
   * @return mixed|null
   */
  protected function getSelectKey(Node $node, $key) {
    try {
      $fieldConfig = $this->entityTypeManager->getStorage('field_config');
    }
    catch (\Exception $e) {
      return NULL;
    }

    if ($value = $this->getNodeValue($node, $key)) {
      if ($field = $fieldConfig->load('node.' . $node->getType() . '.' . $key)) {
        $allowedValues = $field->getSetting('allowed_values');
        return $allowedValues[$value] ?? $value;
      }
    }

    return NULL;
  }

  /**
   * Return the unique solr id for the given node.
   *
   * The unique solr id is a concatenation of the node id with the language
   * code. This way we can always retrieve the right node from a solr id, while
   * also keeping the id's unique within solr.
   *
   * @param \Drupal\node\Entity\Node $node
   *
   * @return string
   */
  protected function getSolrId(Node $node) {
    $langId = $node->language()->getId();
    // If the language is undefined we simply assume its the dutch page.
    if ($langId === 'und') {
      $langId = 'nl';
    }

    return $node->id() . '|' . $langId;
  }

  /**
   * Return the Language URI for the given language code.
   *
   * @todo see if we can do this without hard coding the uri's.
   *
   * @param string $langid
   *
   * @return string
   */
  protected function langidToUri($langid) {
    $languages = [
      'nl' => 'http://publications.europa.eu/resource/authority/language/NLD',
      'en' => 'http://publications.europa.eu/resource/authority/language/ENG',
    ];

    return $languages[$langid] ?? $languages['nl'];
  }

  /**
   * Update the node within the SOLR schema.
   *
   * @param \Drupal\node\Entity\Node $node
   */
  abstract protected function update(Node $node);

  /**
   * Remove the node from the SOLR schema.
   *
   * @param \Drupal\node\Entity\Node $node
   */
  protected function delete(Node $node) {
    $this->solrRequest->deleteIndex($this->getSolrId($node));
  }

}
