<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search organizations within the community.
 */
class SearchOrganizationCommunityController extends BaseCommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'organization';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('organizations');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_community.search.organization';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search.organization';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 organization', '@count organizations');
  }

  /**
   * {@inheritdoc}
   */
  protected function themeRows(array $result): array {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    $rows = [];
    /** @var \Drupal\donl_search\Entity\SolrResult $solrResult */
    foreach ($result as $solrResult) {
      $routeParams = [
        'community' => $community->getMachineName(),
        'organization' => $solrResult->name ?? $solrResult->id,
      ];
      $url = Url::fromRoute('donl_community.organization.view', $routeParams);
      $solrResult->updateUrl($url);
      $rows[] = [
        '#theme' => 'donl_searchrecord_organization',
        '#record' => $solrResult,
      ];
    }

    return $rows;
  }

}
