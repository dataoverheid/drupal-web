<?php

namespace Drupal\donl_community\Controller;

use Drupal\ckan\CkanRequestInterface;
use Drupal\donl\Controller\ApplicationController;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Community Application Controller.
 */
class ApplicationCommunityController extends ApplicationController {

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * Constructor.
   *
   * @param \Drupal\ckan\CkanRequestInterface $ckanRequest
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   */
  public function __construct(CkanRequestInterface $ckanRequest, BackLinkService $backLinkService, CommunityResolverInterface $communityResolver) {
    parent::__construct($ckanRequest, $backLinkService);
    $this->communityResolver = $communityResolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request'),
      $container->get('donl_search_backlink.backlink'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content(NodeInterface $application): array {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    $build = parent::content($application);
    $title = $this->t('Back to all @type in community :community', [
      '@type' => $this->t('applications'),
      ':community' => $community->getTitle(),
    ]);
    $routeParams = ['community' => $community->getMachineName()];
    $build['#backLink'] = $this->backLinkService->createBackLink($title, 'donl_community.search.application', $routeParams);

    return $build;
  }

}
