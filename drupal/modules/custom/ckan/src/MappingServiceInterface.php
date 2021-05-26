<?php

namespace Drupal\ckan;

/**
 * Mapping service interface.
 */
interface MappingServiceInterface {

  /**
   * Return a themeClass name for the given theme URI.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getThemeClass(?string $uri): string;

  /**
   * Returns the readable name for the given theme URI.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getThemeName(?string $uri): string;

  /**
   * Returns the facet_theme value for the given theme URI.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getThemeFacetValue(?string $uri): string;

  /**
   * Returns the readable name for the Status Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getStatusName(?string $uri): string;

  /**
   * Returns the readable name for the distribution Status Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getDistributiontatusName(?string $uri): string;

  /**
   * Returns the readable name for the Access rights Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getAccessRightsName(?string $uri): string;

  /**
   * Returns the readable name for the License Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getLicenseName(?string $uri): string;

  /**
   * Returns the readable name for the Language Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getLanguageName(?string $uri): string;

  /**
   * Returns the readable name for the File format Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getFileFormatName(?string $uri): string;

  /**
   * Returns the readable name for the Media type Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getMediaTypeName(?string $uri): string;

  /**
   * Returns the readable name for the Source Catalog Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getSourceCatalogName(?string $uri): string;

  /**
   * Returns the readable name for the Organization Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getOrganizationName(?string $uri): string;

  /**
   * Returns the readable name for the frequency Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getFrequencyName(?string $uri): string;

  /**
   * Returns the readable name for the Distribution Type Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getDistributionTypeName(?string $uri): string;

  /**
   * Returns the readable name for the spatial_scheme_name Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getSpatialSchemeName(?string $uri): string;

  /**
   * Returns the readable name for the wobuitzondering Uri.
   *
   * @param string|null $uri
   *
   * @return string
   */
  public function getWobExceptionName(?string $uri): string;
  
  /**
   * Returns the readable name for the spatial_value Uri.
   *
   * @param string|null $schemeUri
   *   The uri of the spatial_scheme_name.
   * @param string|null $valueUri
   *   The uri of the spatial_value.
   *
   * @return string
   */
  public function getSpatialValue(?string $schemeUri, ?string $valueUri): string;

  /**
   * Returns the quality string for the given int.
   *
   * @param int|null $quality
   *
   * @return string
   */
  public function getQualityName(?int $quality): string;

}
