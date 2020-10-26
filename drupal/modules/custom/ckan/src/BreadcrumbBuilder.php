<?php

namespace Drupal\ckan;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class to define the menu_link breadcrumb builder.
 */
class BreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   *
   */
  public function __construct(TranslationInterface $stringTranslation, RequestStack $requestStack) {
    $this->stringTranslation = $stringTranslation;
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $routeName = $route_match->getRouteName();
    return strpos($routeName, 'ckan.dataset') === 0 || strpos($routeName, 'ckan.resource') === 0 || $routeName === 'donl_search.search.dataset';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $search = $this->currentRequest->query->all();
    unset($search['sort']);

    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['url.path']);
    $breadcrumb->addCacheableDependency(0);

    $links = [];
    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    $routeName = $route_match->getRouteName();

    if ($routeName === 'donl_search.search.dataset') {
      if (empty($search)) {
        $links[] = Link::createFromRoute($this->t('Datasets'), '<none>');
      }
      else {
        $links[] = Link::createFromRoute($this->t('Datasets'), 'donl_search.search.dataset');
        $links[] = Link::createFromRoute($this->t('Search results'), '<none>');
      }
    }

    // Create the breadcrumbs for the dataset pages.
    if (strpos($routeName, 'ckan.dataset') === 0) {
      $mode = substr($routeName, 13);
      if (!empty($mode)) {
        /** @var \Drupal\ckan\Entity\Dataset $dataset */
        $dataset = $route_match->getParameter('dataset');
        $links[] = Link::createFromRoute($this->t('Datasets'), 'donl_search.search.dataset');

        switch ($mode) {
          case 'create':
            $links[] = Link::createFromRoute($this->t('Create'), '<none>');
            break;

          case 'delete':
            $links[] = Link::createFromRoute($dataset->getTitle(), 'ckan.dataset.view', ['dataset' => $dataset->getName()]);
            $links[] = Link::createFromRoute($this->t('Delete'), '<none>');
            break;

          case 'edit':
            $links[] = Link::createFromRoute($dataset->getTitle(), 'ckan.dataset.view', ['dataset' => $dataset->getName()]);
            $links[] = Link::createFromRoute($this->t('Edit'), '<none>');
            break;

          case 'view':
            $links[] = Link::createFromRoute($dataset->getTitle(), '<none>');
            break;

          case 'datasources':
            $links[] = Link::createFromRoute($dataset->getTitle(), 'ckan.dataset.view', ['dataset' => $dataset->getName()]);
            $links[] = Link::createFromRoute($this->t('Datasource'), '<none>');
            break;
        }
      }
    }

    // Create the breadcrumbs for the resource pages.
    elseif (strpos($routeName, 'ckan.resource') === 0) {
      $mode = substr($routeName, 14);

      /** @var \Drupal\ckan\Entity\Dataset $dataset */
      $dataset = $route_match->getParameter('dataset');
      $links[] = Link::createFromRoute($this->t('Datasets'), 'donl_search.search.dataset');
      $links[] = Link::createFromRoute($dataset->getTitle(), 'ckan.dataset.view', ['dataset' => $dataset->getName()]);

      /** @var \Drupal\ckan\Entity\Resource $resource */
      $resource = $route_match->getParameter('resource');

      switch ($mode) {
        case 'create':
          $links[] = Link::createFromRoute($this->t('Create resource'), '<none>');
          break;

        case 'edit':
          $links[] = Link::createFromRoute($this->t('Edit resource @name', ['@name' => $resource->getName()]), '<none>');
          break;

        case 'delete':
          $links[] = Link::createFromRoute($this->t('Delete resource @name', ['@name' => $resource->getName()]), '<none>');
          break;
      }

    }

    return $breadcrumb->setLinks($links);
  }

}
