<?php

namespace Drupal\donl_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The Api endpoint for community.
 */
class CommunityApiController extends BaseEntityApiController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'community';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSkipFields(): array {
    $fields = parent::getSkipFields();
    $fields[] = 'colour';
    $fields[] = 'field_background_image';
    $fields[] = 'menu';
    return $fields;
  }

}
