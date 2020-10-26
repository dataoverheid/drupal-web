<?php

namespace Drupal\donl_api\Controller;

/**
 * The Api endpoint for group.
 */
class GroupApiController extends BaseEntityApiController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'group';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSkipFields(): array {
    $fields = parent::getSkipFields();
    $fields[] = 'group_image';
    return $fields;
  }

}
