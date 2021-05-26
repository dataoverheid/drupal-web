<?php

namespace Drupal\donl_search;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use NumberFormatter;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class SearchFacets implements SearchFacetsInterface {

  use SearchRoutesTrait;
  use StringTranslationTrait;

  /**
   * The facet rename service.
   *
   * @var \Drupal\donl_search\FacetRenameServiceInterface
   */
  protected $facetRename;

  /**
   * The number formatter.
   *
   * @var \NumberFormatter
   */
  protected $numberFormatter;

  /**
   * The search url service.
   *
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $config;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * SearchFacets constructor.
   *
   * @param \Drupal\donl_search\FacetRenameServiceInterface $facetRename
   *   The facet rename service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   The config.
   * @param \Drupal\donl_search\SearchUrlServiceInterface $searchUrlService
   *   The search url service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(FacetRenameServiceInterface $facetRename, LanguageManagerInterface $languageManager, ConfigFactory $config, SearchUrlServiceInterface $searchUrlService, RequestStack $requestStack) {
    $this->facetRename = $facetRename;
    $this->numberFormatter = new NumberFormatter($languageManager->getCurrentLanguage()->getId(), NumberFormatter::DECIMAL);
    $this->config = $config;
    $this->searchUrlService = $searchUrlService;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function getFacetNamesInOrder(): array {
    return [
      'facet_sys_type' => (string) $this->t('Type'),
      'facet_access_rights' => (string) $this->t('Publicity level'),
      'facet_authority' => (string) $this->t('Data owner'),
      'facet_authority_kind' => (string) $this->t('Level of governance'),
      'facet_theme' => (string) $this->t('Theme'),
      'facet_license' => (string) $this->t('License'),
      'facet_sys_language' => (string) $this->t('Metadata language'),
      'facet_format' => (string) $this->t('Source format'),
      'facet_catalog' => (string) $this->t('Source Catalog'),
      'facet_keyword' => (string) $this->t('Tags'),
      'facet_classification' => (string) $this->t('Classification'),
      'facet_status' => (string) $this->t('State'),
      'facet_community' => (string) $this->t('Community'),
      'facet_group' => (string) $this->t('Group'),
      'facet_related_to' => (string) $this->t('Relates to'),
      'facet_recent' => (string) $this->t('Last modified'),
      'facet_phase' => (string) $this->t('Phase'),
      'facet_kind' => (string) $this->t('Kind'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFacetDeleteLinks(string $routeName, $routeParams, array $activeFacets = []): array {
    $facetDeleteLinks = [];
    $renameFacets = $this->getFacetNamesInOrder();
    foreach ($activeFacets as $k => $facet) {
      if (\is_array($facet)) {
        foreach ($facet as $delta => $v) {
          $tmpValues = $activeFacets;
          unset($tmpValues[$k][$delta]);
          $facetDeleteLinks[] = [
            '#type' => 'link',
            '#title' => new FormattableMarkup('%facet: @value', ['%facet' => ($renameFacets[$k] ?? $k), '@value' => $this->facetRename->rename($v, $k)]),
            '#url' => $this->searchUrlService->completeSearchUrl($routeName, $routeParams, 1, 10, $activeFacets['search'] ?? NULL, $activeFacets['sort'] ?? NULL, $tmpValues),
            '#attributes' => [
              'class' => ['facet-remove'],
            ],
          ];
        }
      }
    }
    return $facetDeleteLinks;
  }

  /**
   * {@inheritdoc}
   */
  public function getFacets($routeName, array $routeParams, array $availableFacets, $recordsPerPage, $search, $sort, array $activeFacets = []): array {
    $values = $activeFacets;
    $renameFacets = $this->getFacetNamesInOrder();

    $values['sort'] = $sort;
    if (!empty($search)) {
      $values['search'] = $search;
    }

    $facets = [];
    if (isset($activeFacets['facet_recent'])) {
      unset($availableFacets['facet_recent']);
    }

    // Generate delete active facet links.
    if ($deleteLinks = $this->getFacetDeleteLinks($routeName, $routeParams, $this->request->query->all())) {
      $facets['active'] = [
        'label' => $this->t('Selected filters'),
        'links' => $deleteLinks,
      ];

      $facets['active']['reset'] = [
        '#type' => 'link',
        '#title' => $this->t('Clear all filters'),
        '#url' => $this->searchUrlService->completeSearchUrl($routeName, $routeParams, 1, 10, $search ?? NULL, $activeFacets['sort'] ?? NULL),
        '#attributes' => [
          'class' => ['button', 'button--reset'],
        ],
      ];
    }

    // Generate the facet links.
    if (!empty($availableFacets)) {
      foreach ($availableFacets as $k => $facet) {
        $group = $renameFacets[$k] ?? $k;
        if (\is_array($facet)) {
          // Slice tag facet to max 30 items.
          if ($k === 'facet_keyword') {
            $facet = \array_slice($facet, 0, 30, TRUE);
          }
          foreach ($facet as $v => $count) {
            // Don't add the facet if its already active.
            if (!isset($activeFacets[$k]) || !in_array($v, $activeFacets[$k])) {
              $tmpValues = $values;
              $tmpValues[$k][] = $v;
              $facetTitle = $this->facetRename->rename($v, $k);
              $facets['available'][$group][$v] = [
                '#type' => 'link',
                '#title' => new FormattableMarkup('<span>@facet</span> <span>(@count)</span>', ['@facet' => $facetTitle, '@count' => $this->numberFormatter->format($count)]),
                '#url' => $this->searchUrlService->completeSearchUrl($routeName, $routeParams, 1, $recordsPerPage, $search, $sort, $tmpValues),
                '#attributes' => [
                  'rel' => 'nofollow',
                  'title' => $facetTitle,
                ],
              ];
            }
          }

          if (isset($facets['available'][$group])) {
            // Add a custom sort to the theme facet.
            if ($k === 'facet_theme') {
              // Add any active theme facets as markup to preserve the hierarchy.
              foreach($activeFacets['facet_theme'] ?? [] as $v) {
                $facets['available'][$group][$v] = [
                  '#markup' => new FormattableMarkup('<span>@facet</span>', ['@facet' => $this->facetRename->rename($v, 'facet_theme')]),
                ];
              }
              $facets['available'][$group] = $this->sortThemes($facets['available'][$group]);
              // Remove any markup facets without sub-themes.
              foreach ($facets['available'][$group] as $key => $value) {
                if (!isset($value['subThemes']) && isset($value['theme']['#markup'])) {
                  unset($facets['available'][$group][$key]);
                }
              }
            }

            // Add a custom sort to the recent facet.
            if ($k === 'facet_recent') {
              $facets['available'][$group] = $this->sortRecentFacet($facets['available'][$group]);
            }

            // Overwrite these facets with a link to the specific search page.
            if ($k === 'facet_sys_type') {
              foreach ($facets['available'][$group] as $key => $value) {
                $newKey = ($key === 'appliance' ? 'application' : $key);
                $value['#url'] = $this->searchUrlService->completeSearchUrl($this->getSearchRoute($newKey), [], 1, $recordsPerPage, $search, $sort, $values);
                $facets['available'][$group][$key] = $value;
              }
            }
          }
        }
      }
    }

    return [
      '#theme' => 'donl_search_facets',
      '#title' => $this->t('Filter your results'),
      '#facets' => $facets ?? [],
      '#show_facets_with_less_than' => $this->config->get('donl.settings')->get('show_facets_with_less_than') ?? 3,
      '#attached' => [
        'library' => ['donl_search/donl_search_facets'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function activeFacetsToReadable($activeFacets): array {
    $readableFacets = [];
    $renameFacets = $this->getFacetNamesInOrder();
    foreach ($activeFacets as $k => $facet) {
      $group = $renameFacets[$k] ?? $k;
      if (\is_array($facet)) {
        foreach ($facet as $v) {
          $readableFacets[$group][] = $this->facetRename->rename($v, $k);
        }
      }
    }

    return $readableFacets;
  }

  /**
   * Turn the theme list into a hierarchical list.
   *
   * @param array $themes
   *
   * @return array
   */
  private function sortThemes(array $themes): array {
    $sortedThemes = [];

    foreach ($themes as $key => $theme) {
      if (strpos($key, '|')) {
        $path = explode('|', $key);
        if (isset($path[1])) {
          $sortedThemes[$path[0]]['subThemes'][] = $theme;
        }
        else {
          $sortedThemes[$path[0]]['theme'] = $theme;
        }
      }
      else {
        $sortedThemes[$key]['theme'] = $theme;
      }
    }
    return $sortedThemes;
  }

  /**
   * Sort the recent facet list.
   *
   * @param array $facets
   *
   * @return array
   */
  private function sortRecentFacet(array $facets): array {
    $sortedFacets = [];

    if (isset($facets['NOW-1MONTH TO NOW+1DAY'])) {
      $sortedFacets['NOW-1MONTH TO NOW+1DAY'] = $facets['NOW-1MONTH TO NOW+1DAY'];
      unset($facets['NOW-1MONTH TO NOW+1DAY']);
    }

    if (isset($facets['NOW-1YEAR TO NOW-1MONTH'])) {
      $sortedFacets['NOW-1YEAR TO NOW-1MONTH'] = $facets['NOW-1YEAR TO NOW-1MONTH'];
      unset($facets['NOW-1YEAR TO NOW-1MONTH']);
    }

    if (isset($facets['* TO NOW-1YEAR'])) {
      $sortedFacets['* TO NOW-1YEAR'] = $facets['* TO NOW-1YEAR'];
      unset($facets['* TO NOW-1YEAR']);
    }

    return $sortedFacets + $facets;
  }

}
