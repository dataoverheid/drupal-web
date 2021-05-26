<?php

namespace Drupal\donl_support\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a donl support Block.
 *
 * @Block(
 *   id = "support_block",
 *   admin_label = @Translation("Support Block"),
 *   category = @Translation("DONL Support"),
 * )
 */
class SupportBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeStorage;

  /**
   * Constructs a CompanyProfileSearchBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $configFactory->get('donl_support.settings');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Generate links for the given node ids.
   *
   * @param array $nids
   *
   * @return array
   */
  protected function generateLinks(array $nids): array {
    $links = [];
    foreach ($nids as $nid) {
      $links[] = $this->nodeStorage->load($nid)->toLink();
    }
    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'donl_support',
      '#left_title' => $this->config->get('left_title'),
      '#left_links' => $this->generateLinks($this->config->get('left_links') ?? []),
      '#center_title' => $this->config->get('center_title'),
      '#center_links' => $this->generateLinks($this->config->get('center_links') ?? []),
      '#right_title' => $this->config->get('right_title'),
      '#right_links' => $this->generateLinks($this->config->get('right_links') ?? []),
    ];
  }

}
