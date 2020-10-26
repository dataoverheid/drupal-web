<?php

namespace Drupal\donl_migrate\Controller;

use Drupal\ckan\CkanRequestInterface;
use Drupal\ckan\Entity\User;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class MigrateController extends ControllerBase {

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('entity_type.manager'),
      $container->get('ckan.request')
    );
  }

  /**
   *
   */
  public function __construct(MessengerInterface $messenger, EntityTypeManagerInterface $entityTypeManager, CkanRequestInterface $ckanRequest) {
    $this->messenger = $messenger;
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->ckanRequest = $ckanRequest;

    $ckanRequest->setCkanUser($this->userStorage->load(1));
  }

  /**
   *
   */
  public function content() {
    // Create the data owners in CKAN and save the new keys in the database.
    $ids = $this->userStorage->getQuery()
      ->condition('uid', [0, 1], 'NOT IN')
      ->condition('roles', 'data_owner', '=')
      ->condition('status', 1, '=')
      ->execute();
    foreach ($this->userStorage->loadMultiple($ids) as $account) {
      $user = new User();
      $user->setName('user_d' . $account->id());
      $user->setFullName($account->getAccountName());
      $user->setEmail($account->getEmail());

      $account->set('field_ckan_id', NULL);
      $account->set('field_ckan_api_key', NULL);
      $catalog = $account->get('field_catalog')->getValue()[0]['value'] ?? NULL;
      if ($ckanUser = $this->ckanRequest->createUser($user)) {
        $this->ckanRequest->activateUser($ckanUser, $catalog);
        $account->set('field_ckan_id', $ckanUser->id);
        $account->set('field_ckan_api_key', $ckanUser->apikey);
        $account->save();
      }
    }

    // Remove the old keys from blocked data owners.
    $ids = $this->userStorage->getQuery()
      ->condition('uid', [0, 1], 'NOT IN')
      ->condition('roles', 'data_owner', '=')
      ->condition('status', 0, '=')
      ->execute();
    foreach ($this->userStorage->loadMultiple($ids) as $account) {
      $account->set('field_ckan_id', NULL);
      $account->set('field_ckan_api_key', NULL);
      $account->save();
    }

    return [
      '#markup' => 'Migration done',
    ];
  }

}
