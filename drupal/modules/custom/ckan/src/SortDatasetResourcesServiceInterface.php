<?php

namespace Drupal\ckan;

use Drupal\ckan\Entity\Dataset;

/**
 * Interface SortDatasetResourcesServiceInterface.
 */
interface SortDatasetResourcesServiceInterface {

  /**
   * @param \Drupal\ckan\Entity\Dataset $dataset
   *
   * @return array
   */
  public function getSortedResources(Dataset $dataset): array;

}
