<?php

namespace Drupal\ckan;

use Drupal\ckan\Entity\Dataset;
use Drupal\donl_value_list\ValueListInterface;

/**
 * Class SortDatasetResourcesService.
 */
class SortDatasetResourcesService implements SortDatasetResourcesServiceInterface {

  /**
   * @var array
   */
  private $distributionTypes;

  /**
   * @param \Drupal\donl_value_list\ValueListInterface $valueList
   */
  public function __construct(ValueListInterface $valueList) {
    $this->distributionTypes = $valueList->getList('donl:distributiontype', FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getSortedResources(Dataset $dataset): array {
    $sortedResources = [];
    if ($resources = $dataset->getResources()) {
      foreach ($resources as $resource) {
        $resourceType = $this->distributionTypes[$resource->getResourceType()] ?? ($this->distributionTypes['https://data.overheid.nl/distributiontype/download'] ?? 'Download');
        $sortedResources[$resourceType][] = $resource;
      }
    }

    return $sortedResources;
  }

}
