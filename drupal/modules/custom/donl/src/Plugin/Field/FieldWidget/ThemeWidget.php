<?php

namespace Drupal\donl\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\donl_value_list\ValueListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'field_theme_selector' widget.
 *
 * @FieldWidget(
 *   id = "field_theme_selector",
 *   module = "donl",
 *   label = @Translation("Theme selector"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class ThemeWidget extends WidgetBase {

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  protected $valueList;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('donl.value_list')
    );
  }

  /**
   *
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ValueListInterface $valueList) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->valueList = $valueList;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += [
      '#type' => 'select',
      '#default_value' => $items[$delta]->value ?? '',
      '#options' => $this->valueList->getPreparedHierarchicalThemeList(),
      '#empty_option' => $this->t('- Select item -'),
    ];
    return ['value' => $element];
  }

}
