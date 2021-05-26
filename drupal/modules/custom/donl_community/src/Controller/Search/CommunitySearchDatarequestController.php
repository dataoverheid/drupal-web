<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search datarequests within the community.
 */
class CommunitySearchDatarequestController extends CommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('data requests');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_community.search.datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search.datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 data request', '@count data requests');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSort(): string {
    return 'score desc,sys_created desc';
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
        'datarequest' => $solrResult->id,
      ];
      $url = Url::fromRoute('donl_community.datarequest.view', $routeParams);
      $solrResult->updateUrl($url);
      $rows[] = [
        '#theme' => 'donl_searchrecord_datarequest',
        '#record' => $solrResult,
      ];
    }

    return $rows;
  }

}
