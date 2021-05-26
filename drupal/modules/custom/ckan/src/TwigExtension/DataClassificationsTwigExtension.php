<?php

declare(strict_types = 1);

namespace Drupal\ckan\TwigExtension;

use Drupal\ckan\DataClassificationsInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class DataClassificationsTwigExtension
 */
class DataClassificationsTwigExtension extends AbstractExtension {

  /**
   * The data classifcations service.
   *
   * @var \Drupal\ckan\DataClassificationsInterface
   */
  protected $dataClassifications;

  /**
   * Constructs a DataClassificationsTwigExtension object.
   *
   * @param \Drupal\ckan\DataClassificationsInterface $dataClassifications
   *   The data classifcations service.
   */
  public function __construct(DataClassificationsInterface $dataClassifications) {
    $this->dataClassifications = $dataClassifications;
  }

  /**
   * Declares our custom twig functions.
   *
   * @return \Twig\TwigFunction[]
   *   The twig functions.
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('get_data_classification_tooltip_forms', [$this, 'getDataClassificationTooltipForms']),
      new TwigFunction('get_data_classification_tooltip_views', [$this, 'getDataClassificationTooltipViews']),
    ];
  }

  /**
   * Retrieves the tooltip of data classifications for forms.
   *
   * @param string $dataClassification
   *   The data classification name.
   *
   * @return string
   *   The tooltip for forms.
   */
  public function getDataClassificationTooltipForms(string $dataClassification): string {
    if ($classification = $this->dataClassifications->getDataClassification($dataClassification)) {
      return $this->dataClassifications->getTooltipForm($classification);
    }
    return '';
  }

  /**
   * Retrieves the tooltip of data classifications for views.
   *
   * @param string $dataClassification
   *   The data classification name.
   *
   * @return string
   *   The tooltip for views.
   */
  public function getDataClassificationTooltipViews(string $dataClassification): string {
    if ($classification = $this->dataClassifications->getDataClassification($dataClassification)) {
      return $this->dataClassifications->getTooltipViews($classification);
    }
    return '';
  }

}
