<?php

namespace Drupal\ckan\Plugin\Field\FieldFormatter;

use Drupal\ckan\CkanRequestInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'field_dataset_text' formatter.
 *
 * @FieldFormatter(
 *   id = "field_dataset_text",
 *   label = @Translation("Dataset"),
 *   field_types = {
 *     "field_dataset"
 *   }
 * )
 */
class DatasetFormatter extends FormatterBase {

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('ckan.request')
    );
  }

  /**
   *
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, CkanRequestInterface $ckanRequest) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->ckanRequest = $ckanRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Dataset.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      /** @var \Drupal\ckan\Entity\Dataset $dataset */
      if ($dataset = $this->ckanRequest->getDatasetByIdentifier($item->value)) {

        // Render each element as markup.
        $element[$delta] = [
          '#title' => $dataset->getTitle(),
          '#type' => 'link',
          '#url' => Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getId()]),
        ];
      }
      else {
        $element[$delta] = ['#markup' => $item->value];
      }
    }

    return $element;
  }

}
