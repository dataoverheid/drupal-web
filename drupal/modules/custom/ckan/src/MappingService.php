<?php

namespace Drupal\ckan;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\donl_value_list\ValueListInterface;

/**
 *
 */
class MappingService implements MappingServiceInterface {
  use StringTranslationTrait;

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  private $valueList;

  /**
   *
   */
  public function __construct(ValueListInterface $valueList, TranslationInterface $stringTranslation) {
    $this->valueList = $valueList;
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeClass($uri) {
    if (!$uri) {
      return '';
    }

    $themeClassMap = [
      'http://standaarden.overheid.nl/owms/terms/Bestuur' => 'governance',
      'http://standaarden.overheid.nl/owms/terms/Cultuur_en_recreatie' => 'culture',
      'http://standaarden.overheid.nl/owms/terms/Economie' => 'economy',
      'http://standaarden.overheid.nl/owms/terms/Financien' => 'finance',
      'http://standaarden.overheid.nl/owms/terms/Huisvesting_(thema)' => 'housing',
      'http://standaarden.overheid.nl/owms/terms/Internationaal' => 'international',
      'http://standaarden.overheid.nl/owms/terms/Landbouw_(thema)' => 'agriculture',
      'http://standaarden.overheid.nl/owms/terms/Migratie_en_integratie' => 'migration',
      'http://standaarden.overheid.nl/owms/terms/Natuur_en_milieu' => 'nature',
      'http://standaarden.overheid.nl/owms/terms/Onderwijs_en_wetenschap' => 'education',
      'http://standaarden.overheid.nl/owms/terms/Openbare_orde_en_veiligheid' => 'security',
      'http://standaarden.overheid.nl/owms/terms/Recht_(thema)' => 'law',
      'http://standaarden.overheid.nl/owms/terms/Ruimte_en_infrastructuur' => 'infrastructure',
      'http://standaarden.overheid.nl/owms/terms/Sociale_zekerheid' => 'social',
      'http://standaarden.overheid.nl/owms/terms/Verkeer_(thema)' => 'traffic',
      'http://standaarden.overheid.nl/owms/terms/Werk_(thema)' => 'jobs',
      'http://standaarden.overheid.nl/owms/terms/Zorg_en_gezondheid' => 'healthcare',
    ];

    $list = $this->valueList->getParentChildThemeList();
    $uri = $list[$uri] ?? '';

    return $themeClassMap[$uri] ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:taxonomiebeleidsagenda', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeFacetValue($uri) {
    if (!$uri) {
      return '';
    }

    $list = $this->valueList->getParentChildThemeList();
    $parent = $list[$uri] ?? NULL;
    if ($parent && $parent !== $uri) {
      return $parent . '|' . $uri;
    }
    return $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:datasetStatus', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getDistributiontatusName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('adms:distributiestatus', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessRightsName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:openbaarheidsniveau', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getLicenseName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:license', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:language', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileFormatName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('mdr:filetype_nal', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaTypeName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('iana:mediatypes', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceCatalogName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:catalogs', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrganizationName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:organization', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getFrequencyName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:frequency', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getDistributionTypeName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:distributiontype', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getSpatialSchemeName($uri) {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:spatial_scheme', FALSE);
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getSpatialValue($schemeUri, $valueUri) {
    switch ($schemeUri) {
      case 'http://standaarden.overheid.nl/owms/4.0/doc/waardelijsten/overheid.koninkrijksdeel':
        $list = $this->valueList->getList('overheid:spatial_koninkrijksdeel', FALSE);
        return $list[$valueUri] ?? $valueUri;

      case 'http://standaarden.overheid.nl/owms/4.0/doc/waardelijsten/overheid.waterschap':
        $list = $this->valueList->getList('overheid:spatial_waterschap', FALSE);
        return $list[$valueUri] ?? $valueUri;

      case 'http://standaarden.overheid.nl/owms/4.0/doc/waardelijsten/overheid.gemeente':
        $list = $this->valueList->getList('overheid:spatial_gemeente', FALSE);
        return $list[$valueUri] ?? $valueUri;

      case 'http://standaarden.overheid.nl/owms/4.0/doc/waardelijsten/overheid.provincie':
        $list = $this->valueList->getList('overheid:spatial_provincie', FALSE);
        return $list[$valueUri] ?? $valueUri;

      case 'http://standaarden.overheid.nl/owms/4.0/doc/syntax-codeerschemas/overheid.epsg28992':
      case 'http://standaarden.overheid.nl/owms/4.0/doc/syntax-codeerschemas/overheid.postcodehuisnummer':
        return $valueUri;
    }

    return $valueUri;
  }

  /**
   * {@inheritdoc}
   */
  public function getQualityName($quality) {
    switch ($quality) {
      case 1:
        return $this->t('All working');

      case 2:
        return $this->t('Partially working');

      case 3:
        return $this->t('Nothing working');
    }
    return $this->t('Not indexed');
  }

}
