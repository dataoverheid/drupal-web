<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\CkanRequestInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\donl_value_list\ValueListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
abstract class BaseForm extends FormBase {

  public const MAXLENGTH_TEXTFIELD_URL = 512;

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  protected $valueList;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request'),
      $container->get('donl.value_list'),
      $container->get('messenger'),
      $container->get('entity.manager'),
      $container->get('request_stack')
    );
  }

  /**
   *
   */
  public function __construct(CkanRequestInterface $ckanRequest, ValueListInterface $valueList, MessengerInterface $messenger, EntityTypeManagerInterface $entityManager, RequestStack $request) {
    $this->ckanRequest = $ckanRequest;
    $this->valueList = $valueList;
    $this->messenger = $messenger;
    $this->userStorage = $entityManager->getStorage('user');
    $this->request = $request;
  }

  /**
   * Helper function to show the retrieved errors on screen.
   */
  protected function setErrors(array &$form, FormStateInterface $form_state, array $errors) {
    $showGenericError = TRUE;
    foreach ($errors as $field => $error) {
      $showGenericError = FALSE;
      if (is_array($error)) {
        foreach ($error as $e) {
          $form_state->setErrorByName($field, $field . ': ' . $e);
        }
      }
      else {
        $form_state->setErrorByName($field, $field . ': ' . $error);
      }
    }

    if ($showGenericError) {
      $form_state->setErrorByName('', 'An unknown error occurred.');
    }
  }

  /**
   * Create the correct value array for fields with an add more button.
   *
   * @param array $value
   *
   * @return array
   */
  protected function getMultiValue(array $value) {
    $return = [];
    if (!empty($value)) {
      foreach ($value as $k => $v) {
        if (is_int($k) && !empty($v)) {
          $return[] = $v;
        }
      }
    }

    return $return;
  }

  /**
   * Create the correct date string for date fields.
   *
   * @param $value
   *
   * @return null|string
   */
  protected function getDateValue($value) {
    if (!empty($value) && $value instanceof DrupalDateTime) {
      return $value->format('Y-m-d\TH:i:s');
    }
    return NULL;
  }

  /**
   * Checks if we'll need to run the validate function.
   *
   * Duo to the error handler being in CKAN we'll need to save the object
   * during the validation process. But an "add one more" button will also
   * trigger a form validation. So to make sure we only create the object
   * during a save we'll check which button the user has pressed.
   */
  protected function requireValidateForm(FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    return isset($button['#attributes']['data-submit']) && $button['#attributes']['data-submit'] === 'true';
  }

  /**
   * @return \Drupal\ckan\User\CkanUserInterface
   */
  protected function getUser() {
    return $this->userStorage->load($this->currentUser()->id());
  }

  /**
   * Builds up the wrapper for the "add more" fields.
   *
   * @param string $title
   *   The title of the field.
   * @param string $id
   *   The id for the "add more" wrapper div.
   *
   * @return array
   */
  protected function buildFormWrapper($title, $id) {
    return [
      '#type' => 'fieldset',
      '#title' => $title,
      '#tree' => TRUE,
      '#prefix' => '<div class="form__element"><div id="' . $id . '-wrapper" class="well">',
      '#suffix' => '</div></div>',
    ];
  }

}
