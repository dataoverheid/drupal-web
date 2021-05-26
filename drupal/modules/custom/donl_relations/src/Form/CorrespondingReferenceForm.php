<?php

namespace Drupal\donl_relations\Form;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for corresponding reference add and edit forms.
 */
class CorrespondingReferenceForm extends EntityForm {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $fieldManager;

  /**
   * Constructs a CorrespondingReferenceForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *  The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManager $fieldManager
   *   The entity field manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManager $fieldManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fieldManager = $fieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\donl_relations\Entity\CorrespondingReferenceInterface $correspondingReference */
    $correspondingReference = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $correspondingReference->label(),
      '#description' => $this->t('Label for the corresponding reference.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $correspondingReference->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
      '#disabled' => !$correspondingReference->isNew(),
    ];

    $form['first_field'] = [
      '#type' => 'select',
      '#title' => $this->t('First field'),
      '#description' => $this->t('Select the first field.'),
      '#options' => $this->getFieldOptions(),
      '#default_value' => $correspondingReference->getFirstField(),
      '#required' => TRUE,
    ];

    $form['second_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Second field'),
      '#description' => $this->t('Select the corresponding field. It may be the same field.'),
      '#options' => $this->getFieldOptions(),
      '#default_value' => $correspondingReference->getSecondField(),
      '#required' => TRUE,
    ];

    $form['bundles'] = [
      '#type' => 'select',
      '#title' => $this->t('Bundles'),
      '#description' => $this->t('Select the bundles which should correspond to one another when they have one of the corresponding fields.'),
      '#options' => $this->getBundleOptions(),
      '#multiple' => TRUE,
      '#default_value' => $correspondingReference->getBundles(),
      '#required' => TRUE,
    ];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('When enabled, corresponding references will be automatically created upon saving an entity.'),
      '#default_value' => $correspondingReference->isEnabled(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\donl_relations\Entity\CorrespondingReferenceInterface $correspondingReference */
    $correspondingReference = $this->entity;

    $status = $correspondingReference->save();

    if ($status) {
      $this->messenger()->addStatus($this->t('Saved the %label corresponding reference.', [
        '%label' => $correspondingReference->label(),
      ]));
    }
    else {
      $this->messenger()->addStatus($this->t('The %label corresponding reference was not saved.', [
        '%label' => $correspondingReference->label(),
      ]));
    }

    $form_state->setRedirect('entity.corresponding_reference.collection');
  }

  /**
   * Helper function to check whether a corresponding reference configuration entity exists.
   */
  public function exists($id): bool {
    $entity = $this->entityTypeManager->getStorage('corresponding_reference')
      ->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * Gets a map of possible reference fields.
   *
   * @return array
   *   The reference field map.
   */
  protected function getReferenceFieldMap(): array {
    $fieldMap = $this->fieldManager->getFieldMapByFieldType('entity_reference');
    return $fieldMap['node'] ?? [];
  }

  /**
   * Gets an array of field options to populate in the form.
   *
   * @return array
   *   An array of field options.
   */
  protected function getFieldOptions(): array {
    $options = [];
    foreach (array_keys($this->getReferenceFieldMap()) as $fieldName) {
      $options[$fieldName] = $fieldName;
    }

    return $options;
  }

  /**
   * Gets an array of bundle options to populate in the form.
   *
   * @return array
   *   An array of bundle options.
   */
  protected function getBundleOptions(): array {
    $correspondingFields = $this->entity->getCorrespondingFields();

    $options = [];
    foreach ($this->getReferenceFieldMap() as $fieldName => $field) {
      if (!empty($correspondingFields) && !in_array($fieldName, $correspondingFields)) {
        continue;
      }

      foreach ($field['bundles'] as $bundle) {
        $options[$bundle] = $bundle;
      }
    }

    ksort($options);
    return $options;
  }

  /**
   * Copies form values into the config entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The config entity.
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if ($this->entity instanceof EntityWithPluginCollectionInterface) {
      // Do not manually update values represented by plugin collections.
      $values = array_diff_key($values, $this->entity->getPluginCollections());
    }

    $entity->set('id', $values['id']);
    $entity->set('label', $values['label']);
    $entity->set('first_field', $values['first_field']);
    $entity->set('second_field', $values['second_field']);
    $entity->set('bundles', $values['bundles']);
    $entity->set('enabled', $values['enabled']);
  }

}
