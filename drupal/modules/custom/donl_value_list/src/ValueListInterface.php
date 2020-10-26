<?php

namespace Drupal\donl_value_list;

/**
 *
 */
interface ValueListInterface {

  /**
   * Returns the requested value list.
   *
   * @param string $list
   *   The identifier of the list that should be returned.
   * @param bool $addEmptyElement
   *   Should te values array start with an empty element.
   *
   * @return array
   */
  public function getList($list, $addEmptyElement = TRUE);

  /**
   * Returns an array with all theme uri's and there parent.
   *
   * @return array
   */
  public function getParentChildThemeList();

  /**
   * Returns the theme list as hierarchical array.
   *
   * @return array
   */
  public function getHierarchicalThemeList();

  /**
   * Prepares the theme list to displayed in a select.
   *
   * @return array
   * */
  public function getPreparedHierarchicalThemeList(): array;

}
