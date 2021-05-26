<?php

namespace Drupal\donl_community\Controller;

use Drupal\ckan\Controller\DatasetController;
use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\LanguageCheckServiceInterface;
use Drupal\ckan\SortDatasetResourcesServiceInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_community\Form\CommunitySearchForm;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\donl_search_backlink\BackLinkService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Community Dataset Controller.
 */
class DatasetCommunityController extends DatasetController {

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
   * @param \Drupal\ckan\LanguageCheckServiceInterface $languageCheckService
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   * @param \Drupal\ckan\SortDatasetResourcesServiceInterface $sortDatasetResourcesService
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   */
  public function __construct(SolrRequestInterface $solrRequest, LanguageCheckServiceInterface $languageCheckService, LanguageManager $languageManager, RouteMatchInterface $routeMatch, BackLinkService $backLinkService, SortDatasetResourcesServiceInterface $sortDatasetResourcesService, CommunityResolverInterface $communityResolver) {
    parent::__construct($solrRequest, $languageCheckService, $languageManager, $routeMatch, $backLinkService, $sortDatasetResourcesService);
    $this->communityResolver = $communityResolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('ckan.languageCheck'),
      $container->get('language_manager'),
      $container->get('current_route_match'),
      $container->get('donl_search_backlink.backlink'),
      $container->get('ckan.sort_dataset_resources'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content(Dataset $dataset) {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    $build = parent::content($dataset);
    $title = $this->t('Back to all @type in community :community', [
      '@type' => $this->t('datasets'),
      ':community' => $community->getTitle(),
    ]);
    $routeParams = ['community' => $community->getMachineName()];
    $build['#backLink'] = $this->backLinkService->createBackLink($title, 'donl_community.search.dataset', $routeParams);

    $build['#search'] = $this->formBuilder()->getForm(CommunitySearchForm::class, $community);

    return $build;
  }

}
