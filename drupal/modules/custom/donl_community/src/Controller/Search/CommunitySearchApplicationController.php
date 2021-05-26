<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search applications within the community.
 */
class CommunitySearchApplicationController extends CommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'application';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('applications');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_community.search.application';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search.application';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 application', '@count applications');
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
        'application' => $solrResult->id,
      ];
      $url = Url::fromRoute('donl_community.application.view', $routeParams);
      $solrResult->updateUrl($url);
      $rows[] = [
        '#theme' => 'donl_searchrecord_application',
        '#record' => $solrResult,
      ];
    }

    return $rows;
  }

}
