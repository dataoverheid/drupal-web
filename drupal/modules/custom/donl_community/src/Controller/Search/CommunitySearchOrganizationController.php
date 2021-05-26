<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search organizations within the community.
 */
class CommunitySearchOrganizationController extends CommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
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
  protected function getRouteName(): string {
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
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 organization', '@count organizations');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSort(): string {
    return 'score desc,title asc';
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
        'organization' => $solrResult->name,
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
