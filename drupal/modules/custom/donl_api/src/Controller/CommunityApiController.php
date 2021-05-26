<?php

namespace Drupal\donl_api\Controller;

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
    // Old fields to be removed.
    $fields[] = 'community_applications';
    $fields[] = 'community_datarequests';
    $fields[] = 'community_datasets';
    $fields[] = 'community_organisations';
    $fields[] = 'groups';
    $fields[] = 'linked_recent';
    return $fields;
  }

}
