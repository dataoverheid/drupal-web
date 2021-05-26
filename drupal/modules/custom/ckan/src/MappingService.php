<?php

namespace Drupal\ckan;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\donl_value_list\ValueListInterface;

/**
 * Mapping service.
 */
class MappingService implements MappingServiceInterface {
  use StringTranslationTrait;

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  private $valueList;

  /**
   * MappingService constructor.
   *
   * @param \Drupal\donl_value_list\ValueListInterface $valueList
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   */
  public function __construct(ValueListInterface $valueList, TranslationInterface $stringTranslation) {
    $this->valueList = $valueList;
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeClass(?string $uri): string {
    if (empty($uri)) {
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
  public function getThemeName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:taxonomiebeleidsagenda');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeFacetValue(?string $uri): string {
    if (empty($uri)) {
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
  public function getStatusName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:datasetStatus');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getDistributiontatusName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('adms:distributiestatus');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessRightsName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:openbaarheidsniveau');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getLicenseName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:license');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:language');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileFormatName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('mdr:filetype_nal');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getMediaTypeName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('iana:mediatypes');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceCatalogName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:catalogs');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrganizationName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:organization');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getFrequencyName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:frequency');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getDistributionTypeName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:distributiontype');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getSpatialSchemeName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('overheid:spatial_scheme');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getWobExceptionName(?string $uri): string {
    if (empty($uri)) {
      return '';
    }

    $list = $this->valueList->getList('donl:wobuitzondering');
    return $list[$uri] ?? $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getSpatialValue(?string $schemeUri, ?string $valueUri): string {
    if (empty($schemeUri) || empty($valueUri)) {
      return '';
    }

    switch ($schemeUri) {
      case 'http://standaarden.overheid.nl/owms/4.0/doc/waardelijsten/overheid.koninkrijksdeel':
        $list = $this->valueList->getList('overheid:spatial_koninkrijksdeel');
        return $list[$valueUri] ?? $valueUri;

      case 'http://standaarden.overheid.nl/owms/4.0/doc/waardelijsten/overheid.waterschap':
        $list = $this->valueList->getList('overheid:spatial_waterschap');
        return $list[$valueUri] ?? $valueUri;

      case 'http://standaarden.overheid.nl/owms/4.0/doc/waardelijsten/overheid.gemeente':
        $list = $this->valueList->getList('overheid:spatial_gemeente');
        return $list[$valueUri] ?? $valueUri;

      case 'http://standaarden.overheid.nl/owms/4.0/doc/waardelijsten/overheid.provincie':
        $list = $this->valueList->getList('overheid:spatial_provincie');
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
  public function getQualityName(?int $quality): string {
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
