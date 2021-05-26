<?php

namespace Drupal\ckan\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\donl_search\SolrRequestInterface;
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
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

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
      $container->get('donl_search.request')
    );
  }

  /**
   *
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, SolrRequestInterface $solrRequest) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->solrRequest = $solrRequest;
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
      if ($result = $this->solrRequest->getDatasetResultByIdentifier($item->value)) {
        // Render each element as markup.
        $element[$delta] = [
          '#title' => $result->title,
          '#type' => 'link',
          '#url' => $result->url,
        ];
      }
      else {
        $element[$delta] = ['#markup' => $item->value];
      }
    }

    return $element;
  }

}
