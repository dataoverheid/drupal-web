<?php

namespace Drupal\donl_statistics\Plugin\views\field;

use Drupal\donl_search\FacetRenameServiceInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("donl_statistics_key_field")
 */
class DonlStatisticsKeyField extends FieldPluginBase {

  /**
   * @var \Drupal\donl_search\FacetRenameServiceInterface
   */
  private $facetRenameService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FacetRenameServiceInterface $facetRenameService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->facetRenameService = $facetRenameService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('donl_search.search.facetRename')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return $this->facetRenameService->rename($values->donl_statistics_key, $values->donl_statistics_topic);
  }

}
