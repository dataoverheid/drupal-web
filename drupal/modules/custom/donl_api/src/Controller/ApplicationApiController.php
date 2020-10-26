<?php

namespace Drupal\donl_api\Controller;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * The Api endpoint for application.
 */
class ApplicationApiController extends BaseEntityApiController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'appliance';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSearchType(): string {
    return 'application';
  }

  /**
   * {@inheritdoc}
   */
  protected function normalizeField(string $key, FieldItemListInterface $fieldItemList) {
    if ($key === 'field_link_application') {
      return $fieldItemList->getValue()[0]['uri'] ?? '';
    }

    if ($key === 'field_tags') {
      $termStorage = $this->entityTypeManager()->getStorage('taxonomy_term');
      $tags = [];
      foreach ($fieldItemList->getValue() as $v) {
        if ($term = $termStorage->load($v['target_id'])) {
          $tags[] = $term->label();
        }
      }
      return $tags;
    }

    return parent::normalizeField($key, $fieldItemList);
  }

  /**
   * {@inheritdoc}
   */
  protected function getSkipFields(): array {
    $fields = parent::getSkipFields();
    $fields[] = 'field_appliance_logo';
    return $fields;
  }

}
