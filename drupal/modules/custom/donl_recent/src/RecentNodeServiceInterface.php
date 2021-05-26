<?php

namespace Drupal\donl_recent;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines an interface for classes that support the recent node service.
 */
interface RecentNodeServiceInterface {

  /**
   * Get the available 'recent' types.
   *
   * @return array|TranslatableMarkup[]
   */
  public function getTypes(): array;

  /**
   * Returns the title filtered by type.
   *
   * @param string|null $type
   *   The current type of recent items to filter on.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function getTitle(string $type = NULL): TranslatableMarkup;

  /**
   * Returns 'recent' node teasers filtered by type.
   *
   * @param string|null $type
   *   The current type of recent items to filter on.
   *
   * @return array
   *   A render array for the entities, indexed by the same keys as the
   *   entities array passed in $entities.
   */
  public function getNodeTeasers(string $type = NULL): array;

  /**
   * Returns 'recent' nodes filtered by type.
   *
   * @param string|null $type
   *   The current type of recent items to filter on.
   *
   * @param int|null $start
   *   Optional start to nodes.
   *
   * @param int|null $end
   *   Optional end to nodes.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function getNodes(string $type = NULL, int $start = 0, $end = NULL): array;

}
