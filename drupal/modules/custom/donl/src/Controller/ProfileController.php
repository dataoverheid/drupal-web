<?php

namespace Drupal\donl\Controller;

use Drupal\ckan\CkanRequestInterface;
use Drupal\ckan\User\CkanUserInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Link;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\donl_search\Form\SearchForm;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;
use NumberFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *
 */
class ProfileController extends ControllerBase {

  private const RECORDS_PER_PAGE = 10;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The number formatter.
   *
   * @var \NumberFormatter
   */
  protected $numberFormatter;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * ProfileController constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   * @param \Drupal\ckan\CkanRequestInterface $ckanRequest
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   */
  public function __construct(DateFormatterInterface $dateFormatter, CkanRequestInterface $ckanRequest, RequestStack $requestStack, KillSwitch $killSwitch) {
    $this->dateFormatter = $dateFormatter;
    $this->ckanRequest = $ckanRequest;
    $this->nodeStorage = $this->entityTypeManager()->getStorage('node');
    $this->userStorage = $this->entityTypeManager()->getStorage('user');
    $this->formBuilder = $this->formBuilder();
    $this->request = $requestStack->getCurrentRequest();
    $this->numberFormatter = new NumberFormatter($this->languageManager()->getCurrentLanguage()->getId(), NumberFormatter::DECIMAL);
    $this->killSwitch = $killSwitch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('ckan.request'),
      $container->get('request_stack'),
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * Builds the profile page.
   *
   * @return array
   *   The render array.
   */
  public function content($user = NULL) {
    $this->killSwitch->trigger();

    $currentUser = $this->currentUser();
    $uid = $currentUser->id();

    if ($currentUser->hasPermission('access user profiles')) {
      if ($user && $user instanceof UserInterface) {
        $uid = $user->id();
      }
    }

    if (!$user = $this->userStorage->load($uid)) {
      throw new AccessDeniedHttpException();
    }

    $panels['metadata'] = [
      '#theme' => 'panel',
      '#id' => 'metadata',
      '#content' => [
        '#theme' => 'donl_user_profile-metadata',
        '#user' => $user,
        '#user_registered_since' => $this->dateFormatter->formatTimeDiffSince($user->getCreatedTime()),
      ],
    ];
    $tabs['panel-metadata'] = $this->t('Profile data');

    $datasets = $this->getDatasetData($user);
    $panels['datasets'] = [
      '#theme' => 'panel',
      '#id' => 'datasets',
      '#content' => [
        '#theme' => 'manage-content-table',
        '#links' => [Link::createFromRoute($this->t('Register new dataset'), 'ckan.dataset.create', [], ['attributes' => ['class' => ['cta']]])],
        '#rows' => $datasets['rows'],
        '#empty_text' => $this->t('No datasets found'),
        '#pagination' => $datasets['pagination'],
      ],
    ];
    $tabs['panel-datasets'] = $this->t('My datasets (@total)', [
      '@total' => $this->numberFormatter->format($datasets['count']),
    ]);

    $applications = $this->getApplicationData($user);
    $panels['applications'] = [
      '#theme' => 'panel',
      '#id' => 'applications',
      '#content' => [
        '#theme' => 'manage-content-table',
        '#links' => [Link::createFromRoute($this->t('Create new application'), 'node.add', ['node_type' => 'appliance'], ['attributes' => ['class' => ['cta']]])],
        '#rows' => $applications['rows'],
        '#empty_text' => $this->t('No applications found'),
        '#pagination' => $applications['pagination'],
      ],
    ];
    $tabs['panel-applications'] = $this->t('My applications (@total)', [
      '@total' => $this->numberFormatter->format($applications['count']),
    ]);

    return [
      '#theme' => 'donl_user_profile',
      '#editLinks' => $this->buildEditLinks($user),
      '#search' => $this->formBuilder->getForm(SearchForm::class),
      '#user' => $user,
      '#user_registered_since' => $this->dateFormatter->formatTimeDiffSince($user->getCreatedTime()),
      '#tabs' => $tabs,
      '#panels' => $panels,
    ];
  }

  /**
   * Builds the edit links.
   *
   * @param \Drupal\ckan\User\CkanUserInterface $user
   *
   * @return array
   *   A collection of links.
   */
  private function buildEditLinks(CkanUserInterface $user): array {
    $tabs = [];

    $options = [
      'attributes' => [
        'class' => [
          'buttonswitch__button',
          // This link seems to always be active here.
          'is-active',
        ],
        'aria-selected' => 'true',
      ],
    ];
    if ($this->currentUser->id() === $user->id()) {
      $viewUrl = Url::fromRoute('donl.profile.view', [], $options);
    }
    else {
      $viewUrl = Url::fromRoute('entity.user.canonical', [
        'user' => $user->id(),
      ], $options);
    }
    if ($viewUrl->access()) {
      $tabs[] = Link::fromTextAndUrl($this->t('View'), $viewUrl);
    }

    $editUrl = Url::fromRoute('entity.user.edit_form', [
      'user' => $user->id(),
    ], ['attributes' => ['class' => ['buttonswitch__button']]]);
    if ($editUrl->access()) {
      $tabs[] = Link::fromTextAndUrl($this->t('Edit'), $editUrl);
    }

    if ($this->currentUser->id() === $user->id()) {
      $logoutUrl = Url::fromRoute('user.logout.http', [], [
        'attributes' => ['class' => ['buttonswitch__button']],
      ]);
      if ($logoutUrl->access()) {
        $tabs[] = Link::fromTextAndUrl($this->t('Logout'), $logoutUrl);
      }
    }

    return $tabs;
  }

  /**
   * Retrieves the datasets from the current user.
   *
   * @param \Drupal\ckan\User\CkanUserInterface $user
   *
   * @return array
   */
  private function getDatasetData(CkanUserInterface $user): array {
    $page = (int) $this->request->get('datasets', 1);

    if ($user->isDataOwner()) {
      $rows = [];
      if ($result = $this->ckanRequest->searchDatasets($page, $this::RECORDS_PER_PAGE, NULL, 'title asc', ['creator_user_id' => [$user->getCkanId()]])) {
        foreach ($result['datasets'] as $dataset) {
          $rows[] = [
            Link::createFromRoute($dataset->getTitle(), 'ckan.dataset.view', ['dataset' => $dataset->getName()]),
            Link::createFromRoute($this->t('Edit'), 'ckan.dataset.edit', ['dataset' => $dataset->getName()]),
            Link::createFromRoute($this->t('Delete'), 'ckan.dataset.delete', ['dataset' => $dataset->getName()]),
          ];
        }

        return [
          'count' => $result['count'],
          'rows' => $rows,
          'pagination' => $this->getPagination('datasets', $result['count'], $page, $user),
        ];
      }
    }

    return [
      'count' => 0,
      'rows' => [],
      'pagination' => NULL,
    ];
  }

  /**
   * Retrieves the applications from the current user.
   *
   * @param \Drupal\ckan\User\CkanUserInterface $user
   *
   * @return array
   */
  private function getApplicationData(CkanUserInterface $user): array {
    $page = (int) $this->request->get('applications', 1);

    $count = $this->nodeStorage->getQuery()
      ->condition('type', 'appliance', '=')
      ->condition('uid', $user->id(), '=')
      ->condition('status', Node::PUBLISHED, '=')
      ->count()->execute();

    $nids = $this->nodeStorage->getQuery()
      ->condition('type', 'appliance', '=')
      ->condition('uid', $user->id(), '=')
      ->condition('status', Node::PUBLISHED, '=')
      ->sort('title', 'asc')
      ->range((($page - 1) * $this::RECORDS_PER_PAGE), $this::RECORDS_PER_PAGE)
      ->execute();

    $rows = [];
    foreach ($this->nodeStorage->loadMultiple($nids) as $node) {
      $rows[] = [
        Link::createFromRoute($node->label(), 'entity.node.canonical', ['node' => $node->id()]),
        Link::createFromRoute($this->t('Edit'), 'entity.node.edit_form', ['node' => $node->id()]),
        Link::createFromRoute($this->t('Delete'), 'entity.node.delete_form', ['node' => $node->id()]),
      ];
    }

    return [
      'count' => $count ?? 0,
      'rows' => $rows,
      'pagination' => $this->getPagination('applications', $count, $page, $user),
    ];
  }

  /**
   * Get the pagination certain the user profile tabs.
   *
   * @param string $tab
   * @param int $numberOfRecords
   * @param int $page
   * @param \Drupal\ckan\User\CkanUserInterface $user
   *
   * @return array
   */
  private function getPagination(string $tab, int $numberOfRecords, int $page, CkanUserInterface $user): array {
    $links = [];

    // Don't show paging if the results fit on a single page.
    if ($numberOfRecords > $this::RECORDS_PER_PAGE) {
      $last = ceil($numberOfRecords / $this::RECORDS_PER_PAGE);
      $start = (($page - 1) > 0) ? $page - 1 : 1;
      $end = (($page + 1) < $last) ? $page + 1 : $last;

      // Add the previous link if we aren't on the first page.
      if ($page !== 1) {
        $links[] = [
          'type' => 'link',
          'link' => $this->buildPaginationLink('&laquo;', $page - 1, $tab, $user),
        ];
      }

      // If we aren't on the first page so a link to the first page.
      if ($start > 1) {
        $links[] = [
          'type' => 'link',
          'link' => $this->buildPaginationLink(1, 1, $tab, $user),
        ];

        // Only add a separator if there is a gab between the numbers.
        if ($page - 2 > 1) {
          $links[] = [
            'type' => 'separator',
          ];
        }
      }

      // Add the current page and links for the numbers around it.
      for ($i = $start; $i <= $end; $i++) {
        if ($page === $i) {
          $links[] = [
            'type' => 'active',
            'label' => $i,
          ];
        }
        else {
          $links[] = [
            'type' => 'link',
            'link' => $this->buildPaginationLink($i, $i, $tab, $user),
          ];
        }
      }

      // If we aren't on the last page so a link to the last page.
      if ($end < $last) {
        // Only add a separator if there is a gab between the numbers.
        if ($page + 2 < $last) {
          $links[] = [
            'type' => 'separator',
          ];
        }

        $links[] = [
          'type' => 'link',
          'link' => $this->buildPaginationLink($last, $last, $tab, $user),
        ];
      }

      // Add the next link if we aren't on the last page.
      if ($page !== $last) {
        $links[] = [
          'type' => 'link',
          'link' => $this->buildPaginationLink('&raquo;', $page + 1, $tab, $user),
        ];
      }
    }

    return [
      '#theme' => 'donl_search_pagination',
      '#pagination' => $links,
    ];
  }

  /**
   * @param string $title
   * @param int $page
   * @param string $tab
   * @param \Drupal\ckan\User\CkanUserInterface $user
   *
   * @return \Drupal\Core\Link
   */
  private function buildPaginationLink(string $title, int $page, string $tab, CkanUserInterface $user): Link {
    $options = [
      'fragment' => $tab,
      'query' => [
        $tab => $page,
      ],
    ];
    if ($this->currentUser->id() !== $user->id()) {
      return Link::createFromRoute(Markup::create($title), 'entity.user.canonical', ['user' => $user->id()], $options);
    }
    return Link::createFromRoute(Markup::create($title), 'donl.profile.view', [], $options);
  }

}
