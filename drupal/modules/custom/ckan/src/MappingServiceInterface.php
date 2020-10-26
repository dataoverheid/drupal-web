<?php

namespace Drupal\ckan;

/**
 *
 */
interface MappingServiceInterface {

  /**
   * Return a themeClass name for the given theme URI..
   *
   * @param string $uri
   *
   * @return string
   */
  public function getThemeClass($uri);

  /**
   * Returns the readable name for the given theme URI.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getThemeName($uri);

  /**
   * Returns the facet_theme value for the given theme URI.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getThemeFacetValue($uri);

  /**
   * Returns the readable name for the Status Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getStatusName($uri);

  /**
   * Returns the readable name for the distribution Status Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getDistributiontatusName($uri);

  /**
   * Returns the readable name for the Access rights Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getAccessRightsName($uri);

  /**
   * Returns the readable name for the License Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getLicenseName($uri);

  /**
   * Returns the readable name for the Language Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getLanguageName($uri);

  /**
   * Returns the readable name for the File format Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getFileFormatName($uri);

  /**
   * Returns the readable name for the Media type Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getMediaTypeName($uri);

  /**
   * Returns the readable name for the Source Catalog Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getSourceCatalogName($uri);

  /**
   * Returns the readable name for the Organization Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getOrganizationName($uri);

  /**
   * Returns the readable name for the frequency Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getFrequencyName($uri);

  /**
   * Returns the readable name for the Distribution Type Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getDistributionTypeName($uri);

  /**
   * Returns the readable name for the spatial_scheme_name Uri.
   *
   * @param string $uri
   *
   * @return string
   */
  public function getSpatialSchemeName($uri);

  /**
   * Returns the readable name for the spatial_value Uri.
   *
   * @param string $schemeUri
   *   The uri of the spatial_scheme_name.
   * @param string $valueUri
   *   The uri of the spatial_value.
   *
   * @return string
   */
  public function getSpatialValue($schemeUri, $valueUri);

  /**
   * Returns the qualty string for the given int.
   *
   * @param int $quality
   *
   * @return string
   */
  public function getQualityName($quality);

}
