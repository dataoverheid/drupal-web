<?php

namespace Drupal\donl_community\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Community News Controller.
 */
class NewsCommunityController extends ControllerBase {

  /**
   * @var \Drupal\donl_search_backlink\BackLinkService
   */
  protected $backLinkService;

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * Node view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewBuilder;

  /**
   * Constructor.
   *
   * @param \Drupal\ckan\CkanRequestInterface $ckanRequest
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   */
  public function __construct(BackLinkService $backLinkService, CommunityResolverInterface $communityResolver) {
    $this->backLinkService = $backLinkService;
    $this->communityResolver = $communityResolver;
    $this->nodeViewBuilder = $this->entityTypeManager()->getViewBuilder('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search_backlink.backlink'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   *
   */
  public function title(NodeInterface $news): string {
    return $news->getTitle();
  }

    /**
     *
     */
  public function content(NodeInterface $news): array {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    return $this->nodeViewBuilder->view($news);
  }

}
