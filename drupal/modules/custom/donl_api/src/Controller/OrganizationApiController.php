<?php

namespace Drupal\donl_api\Controller;

/**
 * The Api endpoint for organization.
 */
class OrganizationApiController extends BaseEntityApiController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'organization';
  }

}
