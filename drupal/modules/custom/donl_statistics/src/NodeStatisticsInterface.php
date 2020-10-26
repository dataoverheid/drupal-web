<?php

namespace Drupal\donl_statistics;

/**
 *
 */
interface NodeStatisticsInterface {

  /**
   * Get the number of applications.
   *
   * @return int
   */
  public function getApplicationCount(): int;

  /**
   * Get the number of data requests with the requested state.
   *
   * @param string $state
   *
   * @return int
   */
  public function getNumberOfDatarequests(string $state): int;

  /**
   * Get all community identifiers.
   *
   * @return array
   */
  public function getCommunityIdentifiers(): array;

  /**
   * Get all organization layers with the organization identifiers.
   *
   * @return array
   */
  public function getOrganizationLayers(): array;

}
