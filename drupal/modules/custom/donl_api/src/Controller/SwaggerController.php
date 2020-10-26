<?php

namespace Drupal\donl_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Creates the swagger.json page.
 */
class SwaggerController extends ControllerBase {

  /**
   * The base url.
   *
   * @var string|null
   */
  protected $baseUrl;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(RequestStack $requestStack) {
    if ($request = $requestStack->getCurrentRequest()) {
      $this->baseUrl = $request->getSchemeAndHttpHost();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Returns the markup for the swagger page.
   *
   * @return array
   *   Drupal render array.
   */
  public function view(): array {
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['container'],
        'id' => 'swagger-ui',
      ],
      '#attached' => [
        'library' => [
          'donl_api/api',
        ],
        'drupalSettings' => [
          'donl_api' => [
            'url' => $this->baseUrl . '/' . drupal_get_path('module', 'donl_api') . '/swagger.json',
          ],
        ],
      ],
    ];
  }

}
