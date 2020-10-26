<?php

namespace Drupal\ckan;

/**
 *
 */
interface LanguageCheckServiceInterface {

  /**
   * @param string $languageUri
   *   The LanguageUri to check against.
   *
   * @return bool
   */
  public function isUriActiveLanguage($languageUri);

}
