<?php

declare(strict_types = 1);

namespace Drupal\ckan;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface DataClassificationsInterface
 *
 * @package Drupal\ckan
 */
interface DataClassificationsInterface {

  /**
   * The taxonomy vocabulary id for data classifcations.
   */
  public const DATA_CLASSIFICATIONS_TAXONOMY_TREE = 'data_classifications';

  /**
   * Retrieves the data classification taxonomy term.
   *
   * @param string $name
   *   The name of the data classification.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The data classification.
   */
  public function getDataClassification(string $name): ?EntityInterface;

  /**
   * Retrieves all data classification taxonomy terms.
   *
   * @return array
   *   The data classifications.
   */
  public function getDataClassifications(): array;

  /**
   * Retrieves the tooltip for forms of the data classification.
   *
   * @param \Drupal\Core\Entity\EntityInterface $classification
   *   The data classification.
   *
   * @return string
   *   The tooltip.
   */
  public function getTooltipForm(EntityInterface $classification): string;

  /**
   * Retrieves the tooltip for views of the data classification.
   *
   * @param \Drupal\Core\Entity\EntityInterface $classification
   *   The data classification.
   *
   * @return string
   *   The tooltip.
   */
  public function getTooltipViews(EntityInterface $classification): string;

}
