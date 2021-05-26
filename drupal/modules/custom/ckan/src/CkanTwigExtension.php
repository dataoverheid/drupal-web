<?php

namespace Drupal\ckan;

use Drupal\ckan\Entity\Dataset;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\field\Entity\FieldConfig;
use NumberFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class CkanTwigExtension.
 *
 * The twig version of the MappingService.
 *
 * @see \Drupal\ckan\MappingService
 */
class CkanTwigExtension extends AbstractExtension {

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
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\ckan\SortDatasetResourcesServiceInterface
   */
  protected $sortDatasetResourcesService;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * CkanTwigExtension Constructor.
   *
   * @param \Drupal\ckan\MappingServiceInterface $mappingService
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\donl_search\SearchUrlServiceInterface $searchUrlService
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   * @param \Drupal\ckan\SortDatasetResourcesServiceInterface $sortDatasetResourcesService
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(MappingServiceInterface $mappingService, LanguageManagerInterface $languageManager, EntityTypeManagerInterface $entityTypeManager, SearchUrlServiceInterface $searchUrlService, SolrRequestInterface $solrRequest, SortDatasetResourcesServiceInterface $sortDatasetResourcesService, DateFormatterInterface $dateFormatter, ConfigFactoryInterface $configFactory) {
    $this->mappingService = $mappingService;
    $this->language = $languageManager->getCurrentLanguage();
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->searchUrlService = $searchUrlService;
    $this->solrRequest = $solrRequest;
    $this->sortDatasetResourcesService = $sortDatasetResourcesService;
    $this->dateFormatter = $dateFormatter;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('ckan_link', [$this, 'getLink']),
      new TwigFunction('ckan_format_number', [$this, 'formatNumber']),
      new TwigFunction('ckan_markdown', [$this, 'formatMarkdown']),
      new TwigFunction('ckan_format_date', [$this, 'formatDate']),
      new TwigFunction('ckan_mapping_get_theme_class', [$this, 'getThemeClass']),
      new TwigFunction('ckan_mapping_get_theme_name', [$this, 'getThemeName']),
      new TwigFunction('ckan_mapping_get_status_name', [$this, 'getStatusName']),
      new TwigFunction('ckan_mapping_get_distribution_status_name', [$this, 'getDistributiontatusName']),
      new TwigFunction('ckan_mapping_get_access_rights_name', [$this, 'getAccessRightsName']),
      new TwigFunction('ckan_mapping_get_wob_exception_name', [$this, 'getWobExceptionName']),
      new TwigFunction('ckan_mapping_get_license_name', [$this, 'getLicenseName']),
      new TwigFunction('ckan_mapping_get_language_name', [$this, 'getLanguageName']),
      new TwigFunction('ckan_mapping_get_file_format_name', [$this, 'getFileFormatName']),
      new TwigFunction('ckan_mapping_get_media_type_name', [$this, 'getMediaTypeName']),
      new TwigFunction('ckan_mapping_get_source_catalog_name', [$this, 'getSourceCatalogName']),
      new TwigFunction('ckan_mapping_get_organization_name', [$this, 'getOrganizationName']),
      new TwigFunction('ckan_mapping_get_frequency_name', [$this, 'getFrequencyName']),
      new TwigFunction('ckan_mapping_get_distribution_type_name', [$this, 'getDistributionTypeName']),
      new TwigFunction('ckan_mapping_get_spatial_scheme_name', [$this, 'getSpatialSchemeName']),
      new TwigFunction('ckan_mapping_get_spatial_value', [$this, 'getSpatialValue']),
      new TwigFunction('ckan_link_tag', [$this, 'getLinkTag']),
      new TwigFunction('ckan_link_data_owner', [$this, 'getLinkDataOwner']),
      new TwigFunction('ckan_get_select_key', [$this, 'getSelectKey']),
      new TwigFunction('ckan_get_search_link_datasets', [$this, 'getSearchLinkDatasets']),
      new TwigFunction('get_dataset_link_by_identifier', [$this, 'getDatasetLinkByIdentifier']),
      new TwigFunction('ckan_dataset_get_sorted_resources', [$this, 'getSortedResources']),
      new TwigFunction('replace_line_breaks_with_new_line', [$this, 'replaceLineBreaksWithNewLine'])
    ];
  }

  /**
   * Replaces line breaks with a '\n'.
   *
   * @param string $text
   *   The text.
   *
   * @return string|string[]
   *   The replaced text.
   */
  public function replaceLineBreaksWithNewLine(string $text) {
    return str_replace(["\r\n", "\r", "\n"], '\n', $text);
  }

  /**
   * Make an url clickable (if possible).
   *
   * @param mixed $value
   *
   * @return \Drupal\Core\Link|string
   */
  public function getLink($value) {
    if (empty($value)) {
      return '';
    }
    $value = trim((string) $value);

    if (UrlHelper::isValid($value, TRUE)) {
      $options = [];

      try {
        // Add a target "_blank" to external URL's.
        if (UrlHelper::isExternal($value)) {
          $options = ['attributes' => ['target' => '_blank']];
        }

        if ($url = Url::fromUri($value, $options)) {
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
   * Format the number.
   *
   * @param mixed $number
   *
   * @return string
   */
  public function formatNumber($number): string {
    if (is_numeric($number)) {
      $numberFormatter = new NumberFormatter($this->language->getId(), NumberFormatter::DECIMAL);
      if ($formattedValue = $numberFormatter->format($number)) {
        return $formattedValue;
      }
    }

    return '0';
  }

  /**
   * Format markdown text.
   *
   * @param mixed $value
   * @param bool $allowFilteredHtml
   *
   * @return \Drupal\Core\Render\Markup|string
   *   Markup if html is allowed, a string otherwise.
   */
  public function formatMarkdown($value, bool $allowFilteredHtml = TRUE) {
    if (empty($value)) {
      return '';
    }

    // Run the value through the markdown text format.
    $value = (string) check_markup((string) trim($value), 'markdown');

    // Even if html is allowed we still want to remove any <a> tags as we can't trust external datasets.
    if ($allowFilteredHtml) {
      if ($allowedTags = $this->configFactory->get('filter.format.markdown')->get('filters.filter_html.settings.allowed_html')) {
        preg_match_all('/<([a-z0-9]+)[^a-z0-9]/i', $allowedTags, $out);
        if (($key = array_search('a', $out[1], TRUE)) !== FALSE) {
          unset($out[1][$key]);
        }
        $value = Xss::filter($value, $out[1]);
      }
    }
    else {
      $value = Xss::filter($value, []);
    }

    return Markup::create($value);
  }

  /**
   * Format a CKAN date string.
   *
   * @param mixed $value
   *   The given date string
   *
   * @return string
   *   The formatted date.
   */
  public function formatDate($value): string {
    if (empty($value)) {
      return '';
    }

    preg_match('/^(\d{2,4})-(\d{1,2})-(\d{1,2})/', (string) trim($value), $matches);
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
  public function getSelectKey($bundle, $fieldName, $value): string {
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
  public function getThemeClass($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getThemeClass((string) $uri);
  }

  /**
   *
   */
  public function getThemeName($uri, $glue = ', '): string {
    if (empty($uri)) {
      return '';
    }

    if (is_array($uri)) {
      $return = [];
      foreach ($uri as $v) {
        $return[] = $this->mappingService->getThemeName((string) $v);
      }
      return implode($glue, $return);
    }

    return $this->mappingService->getThemeName((string) $uri);
  }

  /**
   *
   */
  public function getStatusName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getStatusName((string) $uri);
  }

  /**
   *
   */
  public function getDistributiontatusName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getDistributiontatusName((string) $uri);
  }

  /**
   *
   */
  public function getAccessRightsName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getAccessRightsName((string) $uri);
  }

  /**
   *
   */
  public function getWobExceptionName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getWobExceptionName((string) $uri);
  }

  /**
   *
   */
  public function getLicenseName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getLicenseName((string) $uri);
  }

  /**
   *
   */
  public function getLanguageName($uri, $glue = ', '): string {
    if (is_array($uri)) {
      $return = [];
      foreach ($uri as $v) {
        $return[] = $this->mappingService->getLanguageName((string) $v);
      }
      return implode($glue, $return);
    }

    return $this->mappingService->getLanguageName((string) $uri);
  }

  /**
   *
   */
  public function getFileFormatName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getFileFormatName((string) $uri);
  }

  /**
   *
   */
  public function getMediaTypeName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getMediaTypeName((string) $uri);
  }

  /**
   *
   */
  public function getSourceCatalogName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getSourceCatalogName((string) $uri);
  }

  /**
   *
   */
  public function getOrganizationName($uri, $addCategory = TRUE): string {
    if (empty($uri)) {
      return '';
    }

    $category = NULL;
    if ($addCategory) {
      $nodes = $this->nodeStorage->loadByProperties([
        'type' => 'organization',
        'identifier' => (string) $uri,
      ]);
      if ($nodes && ($node = $this->nodeStorage->load(key($nodes))) && ($term = $node->get('organization_type_term')->referencedEntities()[0] ?? NULL)) {
        $category = $term->get('name')->getString();
      }
    }

    return $this->mappingService->getOrganizationName((string) $uri) . (($addCategory && $category) ? ' (' . $category . ')' : '');
  }

  /**
   *
   */
  public function getFrequencyName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getFrequencyName((string) $uri);
  }

  /**
   *
   */
  public function getDistributionTypeName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getDistributionTypeName((string) $uri);
  }

  /**
   *
   */
  public function getSpatialSchemeName($uri): string {
    if (empty($uri)) {
      return '';
    }
    return $this->mappingService->getSpatialSchemeName((string) $uri);
  }

  /**
   *
   */
  public function getSpatialValue($schemeUri, $valueUri): string {
    if (empty($schemeUri) || empty($valueUri)) {
      return '';
    }
    return $this->mappingService->getSpatialValue((string) $schemeUri, (string) $valueUri);
  }

  /**
   *
   */
  public function getLinkTag($value): Link {
    $value = trim((string) $value);
    return $this->getSearchLinkDatasets(['facet_keyword' => [$value]], $value);
  }

  /**
   *
   */
  public function getLinkDataOwner($value, $title = NULL, $class = NULL) {
    if ($value) {
      $title = $title ?? $value;
      $nodes = $this->nodeStorage->loadByProperties([
        'type' => 'organization',
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
    }

    return $title;
  }

  /**
   *
   */
  public function getSearchLinkDatasets(array $activeFacets, $title, $class = NULL): Link {
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
    if (empty($uri)) {
      return '';
    }

    if ($result = $this->solrRequest->getDatasetResultByIdentifier((string) $uri)) {
      return Link::fromTextAndUrl($result->title, $result->url);
    }
    return $uri;
  }

  /**
   *
   */
  public function getSortedResources(Dataset $dataset): array {
    return $this->sortDatasetResourcesService->getSortedResources($dataset);
  }

}
