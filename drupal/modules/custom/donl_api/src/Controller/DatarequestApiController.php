<?php

namespace Drupal\donl_api\Controller;

/**
 * The Api endpoint for datarequests.
 */
class DatarequestApiController extends BaseEntityApiController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSkipFields(): array {
    $fields = parent::getSkipFields();
    $fields[] = 'url_dataset';
    // Old fields to be removed.
    $fields[] = 'datasets';
    return $fields;
  }

}
