<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\Entity\Resource;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class ResourceDeleteForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_delete_resource_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL, Resource $resource = NULL): array {
    /** @var Dataset $dataset */
    if ($resource) {
      $form['id'] = [
        '#type' => 'hidden',
        '#value' => $resource->getId(),
      ];

      $form['markup'] = [
        '#markup' => '<p>' . $this->t('Are you sure you want to delete the @entityType %title?', [
          '@entityType' => $this->t('resource'),
          '%title' => $resource->name,
        ]) . '</p>',
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
      ];

      if ($dataset) {
        $form['cancel'] = [
          '#markup' => Link::createFromRoute($this->t('Cancel'), 'ckan.dataset.datasources', ['dataset' => $dataset->getName()])
            ->toString(),
        ];
      }

      return $form;
    }

    throw new NotFoundHttpException();
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->ckanRequest->setCkanUser($this->getUser())
      ->deleteResource($form_state->getValue('id'));

    // Get dataset from arguments (if available).
    $buildInfo = $form_state->getBuildInfo();
    $dataset = $buildInfo['args'][0] ?? NULL;
    if ($dataset instanceof Dataset) {
      $form_state->setRedirect('ckan.dataset.datasources', ['dataset' => $dataset->getName()]);
    }
    else {
      $form_state->setRedirect('donl_search.search.dataset');
    }
  }

}
