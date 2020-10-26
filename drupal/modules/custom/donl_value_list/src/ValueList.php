<?php

namespace Drupal\donl_value_list;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;

/**
 *
 */
class ValueList implements ValueListInterface {

  use StringTranslationTrait;

  /**
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * @var array
   */
  private $lists;

  /**
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private $logger;

  /**
   * @var false|int
   */
  private $expireDate;

  /**
   * @var string
   */
  private $languageCode;

  /**
   * @var string[]
   */
  private $valueListLocations;

  /**
   * ValueList constructor.
   *
   * @param \GuzzleHttp\Client $httpClient
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   */
  public function __construct(Client $httpClient, CacheBackendInterface $cacheBackend, ConfigFactoryInterface $configFactory, LoggerChannelFactory $loggerChannelFactory, LanguageManagerInterface $languageManager) {
    $this->client = $httpClient;
    $this->cacheBackend = $cacheBackend;
    $this->valueListLocations = $configFactory->get('donl_value_list.settings')->get('locations') ?? [];
    $this->logger = $loggerChannelFactory->get('donl_value_list');
    $this->languageCode = $languageManager->getCurrentLanguage()->getId();
    $this->expireDate = strtotime(date('Y-m-d 01:00:00', strtotime(date('H') < 1 ? 'today' : 'tomorrow')));
  }

  /**
   * {@inheritdoc}
   */
  public function getList($list, $addEmptyElement = TRUE) {
    $cid = 'donl_value_list:' . $this->languageCode . ':' . $list;

    $values = $this->lists[$cid] ?? [];

    if (empty($values)) {
      // Check if we can get the values out of the cache.
      $cache = $this->cacheBackend->get($cid);
      if ($cache && $cache->valid) {
        $values = $cache->data;
      }
      else {
        try {
          switch ($list) {
            case 'adms:distributiestatus':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('adms_distributiestatus.json'));
              break;

            case 'donl:catalogs':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('donl_catalogs.json'));
              break;

            case 'donl:distributiontype':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('donl_distributiontype.json'), 'en');
              if (isset($values['https://data.overheid.nl/distributiontype/download'])) {
                $values['https://data.overheid.nl/distributiontype/download'] = 'Downloadable files';
              }
              break;

            case 'donl:language':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('donl_language.json'));
              break;

            case 'donl:organization':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('donl_organization.json'));
              break;

            case 'iana:mediatypes':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('iana_mediatypes.json'));
              break;

            case 'mdr:filetype_nal':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('mdr_filetype_nal.json'));
              break;

            case 'overheid:datasetStatus':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_dataset_status.json'));
              break;

            case 'overheid:frequency':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_frequency.json'));
              break;

            case 'overheid:license':
              $values = $this->buildIdTitlelList($this->getValueList('overheid_license.json'));
              break;

            case 'overheid:openbaarheidsniveau':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_openbaarheidsniveau.json'));
              break;

            case 'overheid:spatial_gemeente':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_spatial_gemeente.json'));
              break;

            case 'overheid:spatial_koninkrijksdeel':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_spatial_koninkrijksdeel.json'));
              break;

            case 'overheid:spatial_provincie':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_spatial_provincie.json'));
              break;

            case 'overheid:spatial_scheme':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_spatial_scheme.json'));
              break;

            case 'overheid:spatial_waterschap':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_spatial_waterschap.json'));
              break;

            case 'overheid:taxonomiebeleidsagenda':
              $values = $this->buildTranslatableKeyValueList($this->getValueList('overheid_taxonomiebeleidsagenda.json'));
              break;

          }

          $this->cacheBackend->set($cid, $values, $this->expireDate);
        }
        catch (\Exception $exception) {
          // If there is a problem downloading the cache (timeout), try to use
          // the cached version (if available).
          $this->logger->error($exception->getMessage());
          if ($cache) {
            $values = $cache->data;
          }
        }
      }

      $this->lists[$cid] = $values;
    }

    // Add an empty element to the start of the array. When an empty value is
    // added the list is most likely being used to fill an select list, so to
    // make it more user friendly we might as well sort the list.
    if ($addEmptyElement) {
      asort($values);
      $values = ['' => $this->t('- Select item -')] + $values;
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentChildThemeList() {
    $cid = 'donl_value_list:parent_child_theme_list';

    $list = [];
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }

    if ($values = $this->getContent('https://waardelijsten.dcat-ap-donl.nl/overheid_taxonomiebeleidsagenda.json')) {
      foreach ($values as $uri => $term) {
        $list[$uri] = $term->parent ?? $uri;
      }
      $this->cacheBackend->set($cid, $list, $this->expireDate);
    }

    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function getHierarchicalThemeList() {
    $cid = 'donl_value_list:hierarchical_theme_list';

    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }

    if ($values = $this->parseThemeTree((array) $this->getContent('https://waardelijsten.dcat-ap-donl.nl/overheid_taxonomiebeleidsagenda.json'))) {
      $this->cacheBackend->set($cid, $values, $this->expireDate);
      return $values;
    }

    return [];
  }

  /**
   * Helper function to render the correct Tree for the theme list.
   */
  private function parseThemeTree($tree, $root = NULL) {
    $return = [];
    foreach ($tree as $key => $element) {
      $parent = property_exists($element, 'parent') ? $element->parent : NULL;
      if ($parent == $root) {
        unset($tree[$key]);
        $return[$key] = [
          'label' => $this->getLabel($element),
          'children' => $this->parseThemeTree($tree, $key),
        ];
      }
    }

    return empty($return) ? NULL : $return;
  }

  /**
   * Helper function to build the correct value list.
   */
  private function buildTranslatableKeyValueList($content, $languageCode = NULL) {
    $array = [];
    foreach ((array) $content as $k => $v) {
      $array[$k] = $this->getLabel($v, $languageCode);
    }

    return $array;
  }

  /**
   * Helper function to build the correct value list.
   */
  private function buildIdTitlelList($content) {
    $array = [];
    foreach ((array) $content as $v) {
      $array[$v->id] = $v->title;
    }

    return $array;
  }

  /**
   * Retrieve the value list data.
   *
   * @param string $list
   *   The name of the value list.
   *
   * @return mixed|null
   */
  private function getValueList($list) {
    foreach ($this->valueListLocations as $path) {
      // Add an slash if the path doesn't end with one.
      if (substr($path, -1, 1) !== '/') {
        $path .= '/';
      }

      if (strpos($path, 'http') === 0) {
        if ($content = $this->getContent($path . $list)) {
          return $content;
        }
      }
      else {
        // We suppress the warnings as we are only trying to see if we can get
        // the data.
        if ($content = @json_decode(@file_get_contents($path . $list))) {
          return $content;
        }
      }
    }

    return [];
  }

  /**
   * Retrieve the value list from the given url.
   *
   * @param string $url
   *
   * @return mixed|null
   */
  private function getContent($url) {
    try {
      $options = [
        'headers' => [
          'Accept' => 'application/json',
        ],
        'timeout' => 5,
      ];

      $response = $this->client->get($url, $options);
      return json_decode($response->getBody());
    }
    catch (BadResponseException $e) {
      $this->logger->error('Failed to retrieve the value list. @error', ['@error' => $e->getMessage()]);
    }
    catch (ConnectException $e) {
      $this->logger->error('Failed to retrieve the value list. @error', ['@error' => $e->getMessage()]);
    }

    return NULL;
  }

  /**
   * Helper function to retrieve the correct label.
   *
   * @param object $element
   * @param null|string $languageCode
   *
   * @return string
   */
  private function getLabel($element, $languageCode = NULL) {
    if ($languageCode === 'en' || $this->languageCode === 'en') {
      return (string) $element->labels->{'en-US'};
    }
    return (string) $element->labels->{'nl-NL'};
  }

  /**
   * {@inheritdoc}
   * */
  public function getPreparedHierarchicalThemeList(): array {
    $themeList = $this->getHierarchicalThemeList();
    $list = [];
    asort($themeList);
    foreach ($themeList as $key => $item) {
      $list[$key] = $item['label'];
      asort($item['children']);
      foreach ($item['children'] as $childKey => $child) {
        $list[$childKey] = ' -- ' . $child['label'];
      }
    }

    return $list;
  }

}
