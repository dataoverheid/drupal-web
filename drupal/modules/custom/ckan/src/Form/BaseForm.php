<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\CkanRequestInterface;
use Drupal\ckan\DataClassificationsInterface;
use Drupal\ckan\DatasetEditLinksTrait;
use Drupal\ckan\MappingServiceInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Element;
use Drupal\donl_dcat_validation\DcatValidationServiceInterface;
use Drupal\donl_value_list\ValueListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
abstract class BaseForm extends FormBase {

  use DatasetEditLinksTrait;

  /**
   * The max length of the textfield's url.
   */
  public const MAXLENGTH_TEXTFIELD_URL = 512;

  /**
   * The ckan request.
   *
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * The value list.
   *
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  protected $valueList;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The dcat validation service.
   *
   * @var \Drupal\donl_dcat_validation\DcatValidationServiceInterface
   */
  protected $dcatValidationService;

  /**
   * The mapping service.
   *
   * @var \Drupal\ckan\MappingServiceInterface
   */
  protected $mappingService;

  /**
   * The data classifications.
   *
   * @var \Drupal\ckan\DataClassificationsInterface
   */
  protected $dataClassifications;

  /**
   * BaseForm constructor.
   *
   * @param \Drupal\ckan\CkanRequestInterface $ckanRequest
   *   The ckan request.
   * @param \Drupal\donl_value_list\ValueListInterface $valueList
   *   The value list.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request.
   * @param \Drupal\donl_dcat_validation\DcatValidationServiceInterface $dcatValidationService
   *   The dcat validation service.
   * @param \Drupal\ckan\MappingServiceInterface $mappingService
   *   The mapping service.
   * @param \Drupal\ckan\DataClassificationsInterface $dataClassifications
   *   The data classifications.
   */
  public function __construct(CkanRequestInterface $ckanRequest, ValueListInterface $valueList, MessengerInterface $messenger, EntityTypeManagerInterface $entityTypeManager, RequestStack $request, DcatValidationServiceInterface $dcatValidationService, MappingServiceInterface $mappingService, DataClassificationsInterface $dataClassifications) {
    $this->ckanRequest = $ckanRequest;
    $this->valueList = $valueList;
    $this->messenger = $messenger;
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->request = $request;
    $this->dcatValidationService = $dcatValidationService;
    $this->mappingService = $mappingService;
    $this->dataClassifications = $dataClassifications;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request'),
      $container->get('donl.value_list'),
      $container->get('messenger'),
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('donl_dcat_validation.validation_service'),
      $container->get('ckan.mapping'),
      $container->get('ckan.data_classifications'),
    );
  }

  /**
   * Helper function to show the retrieved errors on screen.
   */
  protected function setErrors(array &$form, FormStateInterface $form_state, array $errors): void {
    if (empty($errors)) {
      $form_state->setErrorByName('', $this->t('An unknown error occurred, please try again later.'));
    }

    $values = $form_state->getValues();
    foreach ($errors as $field => $error) {
      $error = is_array($error) ? $error : [$error];
      foreach ($error as $e) {
        // Check if the field exists on the form, if not prefix the message with
        // the field name for better clarification.
        if (isset($values[$field])) {
          $form_state->setErrorByName($field, $e);
        }
        else {
          if (!is_object($e)) {
            $form_state->setErrorByName($field, $field . ': ' . $e);
          }
        }
      }
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
   * After build function to remove the text format selector.
   *
   * @param array $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function textFormatAfterBuild(array $element, FormStateInterface $form_state) {
    if (isset($element['format'])) {
      $format = &$element['format'];
      unset($format['help'], $format['guidelines'], $format['#type'], $format['#theme_wrappers']);
    }

    return $element;
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
  protected function buildFormWrapper($title, $id): array {
    return [
      '#type' => 'fieldset',
      '#title' => $title,
      '#tree' => TRUE,
      '#prefix' => '<div class="form__element"><div id="' . $id . '-wrapper">',
      '#suffix' => '</div></div>',
    ];
  }

  /**
   * Builds up the title template for the "advanced" sub steps.
   *
   * @param string $title
   *   The title of the field.
   *
   * @return array
   */
  protected function buildAdvancedTitle($title): array {
    return [
      '#type' => 'inline_template',
      '#template' => '{{ title }}<em> {{ suffix }}</em>',
      '#context' => [
        'title' => $title,
        'suffix' => $this->t('(advanced)'),
      ],
    ];
  }

  /**
   * Returns an array of sub steps with correct state from the given step
   *
   * @param array $form
   * @param string $type
   * @param int $stepCount
   *
   * @return array
   */
  protected function getSubSteps(&$form, $type, $stepCount = 0): array {
    $subSteps = [];
    if (isset($form['full_form_wrapper']['wrapper']['main'][$type])) {
      $children = Element::children($form['full_form_wrapper']['wrapper']['main'][$type]);
      $filtered = array_filter(
        $form['full_form_wrapper']['wrapper']['main'][$type],
        static function ($key) use ($children) {
          return in_array($key, $children);
        },
        ARRAY_FILTER_USE_KEY
      );
      foreach ($filtered as $key => $details) {
        $form['full_form_wrapper']['wrapper']['main'][$type][$key]['#attributes']['class'][] = 'sub-step';
        $form['full_form_wrapper']['wrapper']['main'][$type][$key]['#attributes']['data-sub-step'] = $stepCount;

        $subSteps[] = [
          '#theme' => 'donl_form_substep',
          '#title' => $details['#title'],
          '#id' => $stepCount++,
          '#advanced' => $type === 'advanced',
        ];
      }
    }
    return $subSteps;
  }

}
