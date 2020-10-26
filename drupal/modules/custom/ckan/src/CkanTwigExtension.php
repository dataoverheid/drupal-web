<?php

namespace Drupal\ckan;

use Drupal\ckan\Entity\Dataset;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\field\Entity\FieldConfig;
use League\CommonMark\CommonMarkConverter;
use NumberFormatter;

/**
 * Class CkanTwigExtension.
 *
 * The twig version of the MappingService.
 *
 * @package Drupal\ckan
 *
 * @see \Drupal\ckan\MappingService
 */
class CkanTwigExtension extends \Twig_Extension {

  /**
   * @var \Drupal\ckan\MappingServiceInterface
   */
  protected $mappingService;

  /**
   * @var \Drupal\core\Language\LanguageInterface
   */
  protected $language;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * @var \Drupal\ckan\SortDatasetResourcesServiceInterface
   */
  protected $sortDatasetResourcesService;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * CkanTwigExtension Constructor.
   *
   * @param \Drupal\ckan\MappingServiceInterface $mappingService
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\donl_search\SearchUrlServiceInterface $searchUrlService
   * @param \Drupal\ckan\CkanRequestInterface $ckanRequest
   * @param \Drupal\ckan\SortDatasetResourcesServiceInterface $sortDatasetResourcesService
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   */
  public function __construct(MappingServiceInterface $mappingService, LanguageManagerInterface $languageManager, EntityTypeManagerInterface $entityTypeManager, SearchUrlServiceInterface $searchUrlService, CkanRequestInterface $ckanRequest, SortDatasetResourcesServiceInterface $sortDatasetResourcesService, DateFormatterInterface $dateFormatter) {
    $this->mappingService = $mappingService;
    $this->language = $languageManager->getCurrentLanguage();
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->searchUrlService = $searchUrlService;
    $this->ckanRequest = $ckanRequest;
    $this->sortDatasetResourcesService = $sortDatasetResourcesService;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('ckan_link', [$this, 'getLink']),
      new \Twig_SimpleFunction('ckan_format_number', [$this, 'formatNumber']),
      new \Twig_SimpleFunction('ckan_markdown', [$this, 'formatMarkdown']),
      new \Twig_SimpleFunction('ckan_format_date', [$this, 'formatDate']),
      new \Twig_SimpleFunction('ckan_mapping_get_theme_class', [$this, 'getThemeClass']),
      new \Twig_SimpleFunction('ckan_mapping_get_theme_name', [$this, 'getThemeName']),
      new \Twig_SimpleFunction('ckan_mapping_get_status_name', [$this, 'getStatusName']),
      new \Twig_SimpleFunction('ckan_mapping_get_distribution_status_name', [$this, 'getDistributiontatusName']),
      new \Twig_SimpleFunction('ckan_mapping_get_access_rights_name', [$this, 'getAccessRightsName']),
      new \Twig_SimpleFunction('ckan_mapping_get_license_name', [$this, 'getLicenseName']),
      new \Twig_SimpleFunction('ckan_mapping_get_language_name', [$this, 'getLanguageName']),
      new \Twig_SimpleFunction('ckan_mapping_get_file_format_name', [$this, 'getFileFormatName']),
      new \Twig_SimpleFunction('ckan_mapping_get_media_type_name', [$this, 'getMediaTypeName']),
      new \Twig_SimpleFunction('ckan_mapping_get_source_catalog_name', [$this, 'getSourceCatalogName']),
      new \Twig_SimpleFunction('ckan_mapping_get_organization_name', [$this, 'getOrganizationName']),
      new \Twig_SimpleFunction('ckan_mapping_get_frequency_name', [$this, 'getFrequencyName']),
      new \Twig_SimpleFunction('ckan_mapping_get_distribution_type_name', [$this, 'getDistributionTypeName']),
      new \Twig_SimpleFunction('ckan_mapping_get_spatial_scheme_name', [$this, 'getSpatialSchemeName']),
      new \Twig_SimpleFunction('ckan_mapping_get_spatial_value', [$this, 'getSpatialValue']),
      new \Twig_SimpleFunction('ckan_link_tag', [$this, 'getLinkTag']),
      new \Twig_SimpleFunction('ckan_link_data_owner', [$this, 'getLinkDataOwner']),
      new \Twig_SimpleFunction('ckan_get_select_key', [$this, 'getSelectKey']),
      new \Twig_SimpleFunction('ckan_get_search_link_datasets', [$this, 'getSearchLinkDatasets']),
      new \Twig_SimpleFunction('get_dataset_link_by_identifier', [$this, 'getDatasetLinkByIdentifier']),
      new \Twig_SimpleFunction('ckan_dataset_get_sorted_resources', [$this, 'getSortedResources']),
    ];
  }

  /**
   * Make an url clickable (if possible).
   */
  public function getLink($value) {
    if (($value = trim((string) $value)) && UrlHelper::isValid($value, TRUE)) {
      try {
        if ($url = Url::fromUri($value, ['attributes' => ['target' => '_blank']])) {
          return Link::fromTextAndUrl($value, $url);
        }
      }
      catch (\Exception $e) {
        // We don't log errors here as we don't really care if it goes wrong.
      }
    }
    return $value;
  }

  /**
   *
   */
  public function formatNumber($number) {
    if (is_numeric($number)) {
      $numberFormatter = new NumberFormatter($this->language->getId(), NumberFormatter::DECIMAL);
      return $numberFormatter->format($number);
    }

    return 0;
  }

  /**
   * Format markdown text.
   *
   * @param string $value
   * @param bool $allowFilteredHtml
   *
   * @return array|string
   *   A render array if html is allowed, a string otherwise.
   */
  public function formatMarkdown(string $value, bool $allowFilteredHtml = TRUE) {
    $value = nl2br($value);

    $converter = new CommonMarkConverter();
    $value = $converter->convertToHtml($value);

    if ($allowFilteredHtml) {
      $htmlTags = ['b', 'br', 'div', 'em', 'h2', 'h3', 'h4', 'hr', 'li', 'ol', 'p', 'pre', 'span', 'strong', 'ul'];
      $value = Xss::filter($value, $htmlTags);
      return ['#markup' => $value];
    }

    return Xss::filter($value, []);
  }

  /**
   * Format a CKAN date string.
   *
   * @param string $value
   *   The given date string
   *
   * @return string
   *   The formatted date.
   */
  public function formatDate(string $value): string {
    if (!$value) {
      return '';
    }

    preg_match('/^(\d{2,4})-(\d{1,2})-(\d{1,2})/', $value, $matches);
    $timestamp = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
    return $this->dateFormatter->format($timestamp, 'short');
  }

  /**
   * Return the human readable name of the select list value.
   *
   * @param string $bundle
   * @param string $fieldName
   * @param string $value
   *
   * @return string
   */
  public function getSelectKey($bundle, $fieldName, $value) {
    if (!empty($bundle) && !empty($fieldName) && !empty($value)) {
      if ($field = FieldConfig::loadByName('node', $bundle, $fieldName)) {
        $allowedValues = $field->getSetting('allowed_values');
        return $allowedValues[$value] ?? $value;
      }
    }

    return '';
  }

  /**
   *
   */
  public function getThemeClass($value) {
    return $this->mappingService->getThemeClass($value);
  }

  /**
   *
   */
  public function getThemeName($uri, $glue = ', ') {
    if (is_array($uri)) {
      $return = [];
      foreach ($uri as $v) {
        $return[] = $this->mappingService->getThemeName($v);
      }
      return implode($glue, $return);
    }

    return $this->mappingService->getThemeName($uri);
  }

  /**
   *
   */
  public function getStatusName($uri) {
    return $this->mappingService->getStatusName($uri);
  }

  /**
   *
   */
  public function getDistributiontatusName($uri) {
    return $this->mappingService->getDistributiontatusName($uri);
  }

  /**
   *
   */
  public function getAccessRightsName($uri) {
    return $this->mappingService->getAccessRightsName($uri);
  }

  /**
   *
   */
  public function getLicenseName($uri) {
    return $this->mappingService->getLicenseName($uri);
  }

  /**
   *
   */
  public function getLanguageName($uri, $glue = ', ') {
    if (is_array($uri)) {
      $return = [];
      foreach ($uri as $v) {
        $return[] = $this->mappingService->getLanguageName($v);
      }
      return implode($glue, $return);
    }

    return $this->mappingService->getLanguageName($uri);
  }

  /**
   *
   */
  public function getFileFormatName($uri) {
    return $this->mappingService->getFileFormatName($uri);
  }

  /**
   *
   */
  public function getMediaTypeName($uri) {
    return $this->mappingService->getMediaTypeName($uri);
  }

  /**
   *
   */
  public function getSourceCatalogName($uri) {
    return $this->mappingService->getSourceCatalogName($uri);
  }

  /**
   *
   */
  public function getOrganizationName($uri) {
    return $this->mappingService->getOrganizationName($uri);
  }

  /**
   *
   */
  public function getFrequencyName($uri) {
    return $this->mappingService->getFrequencyName($uri);
  }

  /**
   *
   */
  public function getDistributionTypeName($uri) {
    return $this->mappingService->getDistributionTypeName($uri);
  }

  /**
   *
   */
  public function getSpatialSchemeName($uri) {
    return $this->mappingService->getSpatialSchemeName($uri);
  }

  /**
   *
   */
  public function getSpatialValue($schemeUri, $valueUri) {
    return $this->mappingService->getSpatialValue($schemeUri, $valueUri);
  }

  /**
   *
   */
  public function getLinkTag($value) {
    return $this->getSearchLinkDatasets(['facet_keyword' => [$value]], $value);
  }

  /**
   *
   */
  public function getLinkDataOwner($value, $title = NULL, $class = NULL) {
    if (!isset($title)) {
      $title = $value;
    }

    $nodes = $this->nodeStorage->loadByProperties([
      'identifier' => $value,
    ]);
    if ($nodes) {
      $node = $this->nodeStorage->load(key($nodes));
      $machineName = $node->get('machine_name')->getValue()[0]['value'];

      $options = [];
      if (isset($class)) {
        $options['attributes']['class'] = $class;
      }

      return Link::createFromRoute($title, 'donl_search.organization.view', ['organization' => $machineName], $options);
    }

    return $title;
  }

  /**
   *
   */
  public function getSearchLinkDatasets(array $activeFacets, $title, $class = NULL) {
    $options = [];
    if (isset($class)) {
      $options['attributes']['class'] = $class;
    }

    $url = $this->searchUrlService->simpleSearchUrlWithRouteParams('donl_search.search.dataset', $activeFacets, $options);
    return Link::fromTextAndUrl($title, $url);
  }

  /**
   *
   */
  public function getDatasetLinkByIdentifier($uri) {
    /** @var \Drupal\ckan\Entity\Dataset $dataset */
    if ($dataset = $this->ckanRequest->getDatasetByIdentifier($uri)) {
      return Link::createFromRoute($dataset->getTitle(), 'ckan.dataset.view', ['dataset' => $dataset->getName()]);
    }
    return $uri;
  }

  /**
   *
   */
  public function getSortedResources(Dataset $dataset) {
    return $this->sortDatasetResourcesService->getSortedResources($dataset);
  }

}
