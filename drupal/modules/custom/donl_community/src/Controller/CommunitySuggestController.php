<?php

namespace Drupal\donl_community\Controller;

use Drupal\Core\Link;
use Drupal\donl_search\Controller\SuggestController;

/**
 * The Community Suggest Controller.
 */
class CommunitySuggestController extends SuggestController {

  /**
   * {@inheritdoc}
   */
  protected function addSearchLink(array &$links, $category, $type, $term, $payload): void {
    if ($community = $this->currentRequest->query->get('communitySysName')) {
      switch ($category) {
        case 'appliance_suggester':
          if ($url = $this->getUrlFromRoute('donl_community.application.view', ['community' => $community, 'application' => $payload])) {
            if ($type === 'application') {
              $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
            }
            else {
              $links['Applications'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          break;

        case 'dataset_suggester':
          if ($url = $this->getUrlFromRoute('donl_community.dataset.view', ['community' => $community, 'dataset' => $payload])) {
            if ($type === 'dataset') {
              $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
            }
            else {
              $links['Datasets'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          break;

        case 'datarequest_suggester':
          if ($url = $this->getUrlFromRoute('donl_community.datarequest.view', ['community' => $community, 'datarequest' => $payload])) {
            if ($type === 'datarequest') {
              $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
            }
            else {
              $links['Datarequests'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          break;

        case 'group_suggester':
          if ($url = $this->getUrlFromRoute('donl_community.group.view', ['community' => $community, 'group' => $payload])) {
            if ($type === 'group') {
              $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
            }
            else {
              $links['Groups'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          break;

        case 'news_suggester':
          if ($url = $this->getUrlFromRoute('donl_community.news.view', ['community' => $community, 'news' => $payload])) {
            if ($type === 'news') {
              $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
            }
            else {
              $links['News items'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          break;

        case 'organization_suggester':
          if ($type === 'dataset') {
            if ($url = $this->getUrlFromRoute('donl_community.search.dataset', ['community' => $community], ['query' => ['facet_authority[0]' => $payload]])) {
              $links['Search dataset on organisation'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          elseif ($type === 'organization') {
            if ($url = $this->getUrlFromRoute('donl_community.organization.view', ['community' => $community, 'organization' => $payload])) {
              $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          else {
            if ($url = $this->getUrlFromRoute('donl_community.organization.view', ['community' => $community, 'organization' => $payload])) {
              $links['Organizations'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          break;

        case 'theme_suggester':
          if ($type === 'dataset') {
            if ($url = $this->getUrlFromRoute('donl_community.search.dataset', ['community' => $community], ['query' => ['facet_theme[0]' => $payload]])) {
              $links['Search dataset on theme'][] = Link::fromTextAndUrl($term, $url);
            }
          }
          break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getSearchSuggestions($type, $search): array {
    $communitySysName = $this->currentRequest->query->get('communitySysName');
    return $this->solrRequest->getSearchSuggestions($type, $search, $communitySysName);
  }

}
