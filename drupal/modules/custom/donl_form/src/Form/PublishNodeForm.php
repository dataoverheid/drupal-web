<?php

namespace Drupal\donl_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Publish node form.
 */
class PublishNodeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'donl_form_node_publish_form';
  }

  /**
   * Get the page title.
   *
   * @param \Drupal\node\NodeInterface|NULL $node
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function title(NodeInterface $node = NULL): TranslatableMarkup {
    if ($node) {
      return $this->t('Publish @type', ['@type' => $this->t($this->getType($node))]);
    }
    return $this->t('Publish');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL): array {
    if (!$node) {
      throw new NotFoundHttpException();
    }
    $form_state->set('node', $node);

    $type = $this->getType($node);

    $text = $this->t('You are about to publish this @type, are you sure?', ['@type' => $this->t($type)]);
    $buttonText = $this->t('Publish');
    if ($node->isPublished()) {
      $text = $this->t('You are about to unpublish this @type, are you sure?', ['@type' => $this->t($type)]);
      $buttonText = $this->t('Unpublish');
    }

    $form['text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $text,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $buttonText,
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];

    $form['cancel'] = [
      '#title' => $this->t('Cancel'),
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()]),
    ];

    $form['#attributes'] = ['class' => ['donl-form', 'step-form']];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form_state->get('node');
    $type = $this->getType($node);

    if ($node->isPublished()) {
      $node->setUnpublished();
      $this->messenger()->addMessage($this->t('@type successfully unpublished.', ['@type' => $this->t($type)]));
    }
    else {
      $node->setPublished();
      $this->messenger()->addMessage($this->t('@type successfully published.', ['@type' => $this->t($type)]));
    }

    $node->save();
    $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
  }

  /**
   * Return the correct node type name.
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @return string
   */
  private function getType(NodeInterface $node): string {
    switch ($node->getType()) {
      case 'appliance':
        return 'application';

      case 'datarequest':
        return 'data request';

      default:
        return $node->getType();
    }
  }

}
