<?php

namespace Drupal\donl_markdown\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use League\CommonMark\CommonMarkConverter;

/**
 * Add a markdown filter.
 *
 * @Filter(
 *   id = "filter_markdown",
 *   title = @Translation("Markdown Filter"),
 *   description = @Translation("Process markdown text."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class FilterMarkdown extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $converter = new CommonMarkConverter();
    return new FilterProcessResult($converter->convertToHtml($text));
  }

}
