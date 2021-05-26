<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search news within the community.
 */
class CommunitySearchNewsController extends CommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'news';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('news');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_community.search.news';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search.news';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 news item', '@count news items');
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
        'news' => $solrResult->name,
      ];
      $url = Url::fromRoute('donl_community.news.view', $routeParams);
      $solrResult->updateUrl($url);
      $rows[] = [
        '#theme' => 'donl_searchrecord_news',
        '#record' => $solrResult,
      ];
    }

    return $rows;
  }

}
