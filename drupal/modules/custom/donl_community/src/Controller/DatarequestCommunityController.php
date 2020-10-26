<?php

namespace Drupal\donl_community\Controller;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\donl\Controller\DatarequestController;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Community Datarequest Controller.
 */
class DatarequestCommunityController extends DatarequestController {

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * Constructor.
   *
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   */
  public function __construct(SolrRequestInterface $solrRequest, DateFormatterInterface $dateFormatter, BackLinkService $backLinkService,  CommunityResolverInterface $communityResolver) {
    parent::__construct($solrRequest, $dateFormatter, $backLinkService);
    $this->communityResolver = $communityResolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('date.formatter'),
      $container->get('donl_search_backlink.backlink'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content(NodeInterface $datarequest): array {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    $build = parent::content($datarequest);
    $title = $this->t('Back to all @type in community :community', [
      '@type' => $this->t('data requests'),
      ':community' => $community->getTitle(),
    ]);
    $routeParams = ['community' => $community->getMachineName()];
    $build['#backLink'] = $this->backLinkService->createBackLink($title, 'donl_community.search.datarequest', $routeParams);

    return $build;
  }

}
