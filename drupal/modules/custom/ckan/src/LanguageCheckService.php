<?php

namespace Drupal\ckan;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\donl_value_list\ValueListInterface;

/**
 *
 */
class LanguageCheckService implements LanguageCheckServiceInterface {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $language;

  /**
   * Value List containing all available languages.
   *
   * @var array
   */
  private $languageValueList;

  /**
   *
   */
  public function __construct(LanguageManagerInterface $languageManager, ValueListInterface $valueList, TranslationInterface $stringTranslation) {
    $this->language = $languageManager->getCurrentLanguage();
    $this->languageValueList = $valueList->getList('donl:language');
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public function isUriActiveLanguage($languageUri) {
    if (isset($this->languageValueList[$languageUri])) {
      switch ($this->language->getId()) {
        case 'en':
          return $languageUri === 'http://publications.europa.eu/resource/authority/language/ENG';

        case 'nl':
          return $languageUri === 'http://publications.europa.eu/resource/authority/language/NLD';
      }
    }

    return FALSE;
  }

  /**
   *
   */
  public function getLangaugeNameDataset($languageUri) {
    if (isset($this->languageValueList[$languageUri])) {
      switch ($languageUri) {
        case 'http://publications.europa.eu/resource/authority/language/ENG':
          return $this->t('English', [], ['context' => 'Custom language name']);

        case 'http://publications.europa.eu/resource/authority/language/NLD':
          return $this->t('Dutch', [], ['context' => 'Custom language name']);

        case 'http://publications.europa.eu/resource/authority/language/DEU':
          return $this->t('German', [], ['context' => 'Custom language name']);

        case 'http://publications.europa.eu/resource/authority/language/FRY':
          return $this->t('Frisian', [], ['context' => 'Custom language name']);
      }
    }

    return $this->t('Unknown');
  }

}
