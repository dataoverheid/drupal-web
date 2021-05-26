<?php

namespace Drupal\ckan\Controller;

use Drupal\ckan\DatasetEditLinksTrait;
use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\LanguageCheckServiceInterface;
use Drupal\ckan\SortDatasetResourcesServiceInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\donl_search\Form\SearchForm;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class DatasetController extends ControllerBase {

  use DatasetEditLinksTrait;

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\ckan\LanguageCheckServiceInterface
   */
  protected $languageCheckService;

  /**
   * @var string
   */
  private $languageCode;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * @var \Drupal\donl_search_backlink\BackLinkService
   */
  protected $backLinkService;

  /**
   * @var \Drupal\ckan\SortDatasetResourcesServiceInterface
   */
  protected $sortDatasetResourcesService;

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
      $container->get('ckan.sort_dataset_resources')
    );
  }

  /**
   *
   */
  public function __construct(SolrRequestInterface $solrRequest, LanguageCheckServiceInterface $languageCheckService, LanguageManager $languageManager, RouteMatchInterface $routeMatch, BackLinkService $backLinkService, SortDatasetResourcesServiceInterface $sortDatasetResourcesService) {
    $this->userStorage = $this->entityTypeManager()->getStorage('user');
    $this->solrRequest = $solrRequest;
    $this->languageCheckService = $languageCheckService;
    $this->formBuilder = $this->formBuilder();
    $this->languageCode = $languageManager->getCurrentLanguage()->getId();
    $this->routeMatch = $routeMatch;
    $this->backLinkService = $backLinkService;
    $this->sortDatasetResourcesService = $sortDatasetResourcesService;
  }

  /**
   *
   */
  public function title(Dataset $dataset) {
    return $dataset->getTitle();
  }

  /**
   *
   */
  public function content(Dataset $dataset) {
    // Check if the users current language matches the language of this dataset.
    $warnings = [];
    if (!$this->languageCheckService->isUriActiveLanguage($dataset->getLanguage()[0])) {
      $language = $this->languageCheckService->getLangaugeNameDataset($dataset->getLanguage()[0]);
      $warnings[] = $this->t('The metadata of this dataset is written in @language.', ['@language' => $language]);
    }

    $links = [];
    // Add url parameters for data owner and required data.
    $links['datarequest'] = Link::createFromRoute($this->t('Make a data request'), 'node.add', ['node_type' => 'datarequest'], [
      'query' => [
        'data_eigenaar' => $dataset->getAuthority(),
        'gevraagde_data' => $dataset->getTitle(),
      ],
    ])->toRenderable();
    $links['datarequest']['#attributes']['class'] = ['button', 'bordered'];

    // Add url parameters for data owner, dataset title, dataset url.
    $links['feedback'] = Link::createFromRoute($this->t('Feedback'), 'node.add', ['node_type' => 'feedback_dataset'], [
      'query' => [
        'dataset' => $dataset->getId(),
        'destination' => Url::fromRoute($this->routeMatch->getRouteName(), $this->routeMatch->getRawParameters()
          ->all())->toString(),
      ],
    ])->toRenderable();
    $links['feedback']['#attributes']['class'] = ['button', 'bordered'];

    if (strlen($dataset->getNotes()) > 4999) {
      $dataset->setNotes(substr($dataset->getNotes(), 0, 4996) . '...');
    }

    // Schema.org generation.
    $schema = [
      '#theme' => 'dataset-schema',
      '#dataset' => $dataset,
      '#cache' => [
        'max-age' => 86400,
      ],
    ];

    $panels = [];
    $tabs = [];

    // Description tab.
    $tabs['panel-description'] = $this->t('Description');
    $panels['description'] = [
      '#theme' => 'panel',
      '#id' => 'description',
      '#content' => [
        '#theme' => 'dataset-panel-description',
        '#dataset' => $dataset,
        '#links' => $links,
      ],
    ];

    // Resources tabs.
    if ($sortedResources = $this->sortDatasetResourcesService->getSortedResources($dataset)) {
      foreach ($sortedResources as $title => $sortedResource) {
        $key = strtolower(str_replace(' ', '-', $title));
        $tabs['panel-' . $key] = $title . ' (' . count($sortedResource) . ')';
        $panels[$key] = [
          '#theme' => 'panel',
          '#id' => $key,
          '#content' => [
            '#theme' => 'dataset-panel-resources',
            '#alias' => $key,
            '#resources' => $sortedResource,
          ],
        ];
        if ($title === 'Visualization' || $title === 'Visualisatie') {
          $fileType = explode('.', $sortedResource[0]->url);
          $last = end($fileType);
          $sortedResource[0]->isImage = str_contains($last, 'png') || str_contains($last, 'jpg') || str_contains($last, 'jpeg');
          $panels[$key]['#content']['#theme'] = 'dataset-panel-visualisations';
          $panels[$key]['#attached']['library'][] = 'ckan/visualisations';
        }
      }
    }

    // @todo check if there is a nicer way to merge this panel.
    $documentation = [];
    foreach ($dataset->getDocumentation() as $url) {
      $documentation[] = $this->createClickableUrl($url);
    }
    $confirmsTo = [];
    foreach ($dataset->getConformsTo() as $url) {
      $confirmsTo[] = $this->createClickableUrl($url);
    }
    if ($documentation || $confirmsTo) {
      $key = 'documentatie';
      if ($this->languageCode === 'en') {
        $key = 'documentation';
      }

      $documentationResources = $panels[$key]['#content']['#resources'] ?? [];
      $tabs['panel-' . $key] = $this->t('Documentation') . ' (' . (count($documentationResources) + count($documentation) + count($confirmsTo)) . ')';
      $panels[$key] = [
        '#theme' => 'panel',
        '#id' => $key,
        '#content' => [
          '#theme' => 'dataset-panel-documentation',
          '#resources' => $documentationResources,
          '#documentation' => $documentation,
          '#confirmsTo' => $confirmsTo,
        ],
      ];
    }

    // Relations tab.
    $comparableLinks = [];
    foreach ($this->solrRequest->getComparableData('dataset', $dataset->getId()) as $name => $title) {
      $comparableLinks[] = Link::createFromRoute($title, 'ckan.dataset.view', ['dataset' => $name]);
    }
    $groupLinks = [];
    foreach ($this->solrRequest->getDatasetGroups($dataset->getIdentifier()) as $name => $title) {
      $groupLinks[] = Link::createFromRoute($title, 'donl_search.group.view', ['group' => $name]);
    }
    $relatedResourceLinks = [];
    foreach ($dataset->getRelatedResource() as $url) {
      $relatedResourceLinks[] = $this->createClickableUrl($url);
    }
    $sourceLinks = [];
    foreach ($dataset->getSource() as $url) {
      $sourceLinks[] = $this->createClickableUrl($url);
    }
    if ($comparableLinks || $groupLinks || $relatedResourceLinks || $sourceLinks) {
      $tabs['panel-relations'] = $this->t('Relations');
      $panels['relations'] = [
        '#theme' => 'panel',
        '#id' => 'relations',
        '#title' => $this->t('Relations'),
        '#content' => [
          '#theme' => 'dataset-panel-relations',
          '#comparableLinks' => $comparableLinks,
          '#groupLinks' => $groupLinks,
          '#relatedResourceLinks' => $relatedResourceLinks,
          '#sourceLinks' => $sourceLinks,
          '#dataset' => $dataset,
        ],
      ];
    }

    // Condities tab.
    $provenanceLinks = [];
    foreach ($dataset->getProvenance() as $url) {
      $provenanceLinks[] = $this->createClickableUrl($url);
    }
    if ($provenanceLinks) {
      $tabs['panel-condition'] = $this->t('Condition (@i)', ['@i' => count($provenanceLinks)]);
      $panels['condition'] = [
        '#theme' => 'panel',
        '#id' => 'condition',
        '#title' => $this->t('Condition'),
        '#content' => [
          $this->getLinkedListRender($provenanceLinks),
        ],
      ];
    }

    // Example tab.
    $sampleLinks = [];
    foreach ($dataset->getSample() as $url) {
      $sampleLinks[] = $this->createClickableUrl($url);
    }
    if ($sampleLinks) {
      $tabs['panel-example'] = $this->t('Example (@i)', ['@i' => count($sampleLinks)]);
      $panels['example'] = [
        '#theme' => 'panel',
        '#id' => 'example',
        '#title' => $this->t('Example'),
        '#content' => [
          $this->getLinkedListRender($sampleLinks),
        ],
      ];
    }

    // Forum tab.
    $forumLinks = [];
    foreach ($dataset->getDocumentation() as $link) {
      if (preg_match('/https?:\/\/(www\.)?((geoforum.nl)|(.*.datacommunities.nl))(\/.*)?/', $link)) {
        $forumLinks[] = Link::fromTextAndUrl($link, Url::fromUri($link));
      }
    }
    if ($forumLinks) {
      $tabs['panel-forum'] = $this->t('Forum (@i)', ['@i' => count($forumLinks)]);
      $panels['forum'] = [
        '#theme' => 'panel',
        '#id' => 'forum',
        '#title' => $this->t('Forum'),
        '#content' => [
          $this->getLinkedListRender($forumLinks),
        ],
      ];
    }

    // Metadata tab.
    $tabs['panel-metadata'] = $this->t('Metadata');
    $permanentUrl = Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getId()], ['absolute' => TRUE]);
    $panels['metadata'] = [
      '#theme' => 'panel',
      '#id' => 'metadata',
      '#content' => [
        '#theme' => 'dataset-panel-metadata',
        '#dataset' => $dataset,
        '#permanent_link' => Link::fromTextAndUrl($permanentUrl->toString(), $permanentUrl),
      ],
    ];

    $editLinks = [];
    $showPublished = FALSE;
    /* @var \Drupal\ckan\User\CkanUserInterface $ckanUser */
    if ($ckanUser = $this->userStorage->load($this->currentUser()->id())) {
      $editLinks = $this->getEditLinks($dataset, $ckanUser, 'view');
      if ($editLinks) {
        $showPublished = TRUE;
      }
    }

    $dataset->setOrgLogoUrl($this->getOrgLogo($dataset));
    $build = [
      '#theme' => 'dataset',
      '#dataset' => $dataset,
      '#panels' => $panels,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all @type', ['@type' => $this->t('datasets')]), 'donl_search.search.dataset'),
      '#editLinks' => $editLinks,
      '#warnings' => $warnings,
      '#tabs' => $tabs,
      '#schema' => $schema,
      '#showPublished' => $showPublished,
      '#search' => $this->formBuilder->getForm(SearchForm::class),
      '#cache' => [
        'max-age' => 86400,
      ],
    ];

    if ($this->config('ckan.dataset.settings')->get('preview_functionality')) {
      $build['#attached'] = [
        'library' => ['ckan/preview'],
      ];
    }
    return $build;
  }

  /**
   * Make an url clickable (if possible).
   */
  public function createClickableUrl($value) {
    if (($value = trim((string) $value)) && UrlHelper::isValid($value, TRUE)) {
      try {
        if ($url = Url::fromUri($value, ['attributes' => ['target' => '_blank']])) {
          return Link::fromTextAndUrl($value, $url);
        }
      }
      catch (\Exception $e) {
        // We don't log errors here as we don't really care if it goes wrong.
      }
    }
    return $value;
  }

  /**
   * Returns the render array for a linked list text.
   *
   * @param array $items
   * @param string|null $title
   *
   * @return array
   */
  private function getLinkedListRender($items) {
    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
      '#attributes' => ['class' => ['list', 'list--linked', 'group-links']],
    ];
  }

  private function getOrgLogo(Dataset $dataset) {
    $result = $this->solrRequest->getResultBySysuri($dataset->getAuthority(), 'organization', 'asset_logo');
    return $result['asset_logo'] ?? NULL;
  }

}
