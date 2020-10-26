<?php

namespace Drupal\donl_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_search\Form\SearchForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the general search block.
 *
 * @Block(
 *  id = "donl_search_general_block",
 *  admin_label = @Translation("DONL search general block"),
 *  category = @Translation("DONL search"),
 * )
 */
class SearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * @var \Drupal\Core\Path\PathMatcher
   */
  private $pathMatcher;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder, PathMatcher $pathMatcher, RouteMatchInterface $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
    $this->pathMatcher = $pathMatcher;
    $this->currentRouteMatch = $current_route_match;

  }

  /**
   * {@inheritdoc}homepage.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('path.matcher'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $route = $this->currentRouteMatch->getRouteName();
    $routeName = explode('.', $route);
    $type = array_pop($routeName);
    $bgColor = 'bg';

    switch ($type) {
      case 'dataset':
      case 'datarequest':
        $icon = 'icon-data';
        break;

      case 'catalog':
      case 'community':
      case 'organization':
      case 'group':
        $icon = 'icon-community';
        $bgColor = 'bg--green';
        break;

      case 'application':
        $icon = 'icon-informatie';
        $bgColor = 'bg--purple';
        break;

      case 'support':
      case 'news':
        $icon = 'icon-nieuws';
        $bgColor = 'bg--orange';
        break;
    }

    $build = [
      '#form' => $this->formBuilder->getForm(SearchForm::class),
      '#bg_color' => $bgColor,
      '#icon' => $icon ?? NULL,
      '#title' => ucfirst($type),
    ];

    if ($this->pathMatcher->isFrontPage()) {
      $build['#theme'] = 'homepage_search_block';
    }
    else {

      $build['#theme'] = 'search_block';
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
