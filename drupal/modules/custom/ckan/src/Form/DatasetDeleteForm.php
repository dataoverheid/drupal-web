<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class DatasetDeleteForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_delete_dataset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL): array {
    /** @var Dataset $dataset */
    if ($dataset) {
      $user = $this->getUser();
      if ($user->isAdministrator() || $user->getCkanId() === $dataset->getCreatorUserId()) {
        $form['editLinks'] = [
          '#type' => 'inline_template',
          '#template' => '<div class="container"><div class="buttonswitch">{% for editLink in editLinks %}{{ editLink }}{% endfor %}</div></div>',
          '#context' => [
            'editLinks' => [
              'view' => [
                '#type' => 'link',
                '#title' => $this->t('View'),
                '#url' => Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getName()]),
                '#attributes' => [
                  'class' => ['buttonswitch__button'],
                ],
              ],
              'edit' => [
                '#type' => 'link',
                '#title' => $this->t('Edit'),
                '#url' => Url::fromRoute('ckan.dataset.edit', ['dataset' => $dataset->getName()]),
                '#attributes' => [
                  'class' => ['buttonswitch__button'],
                ],
              ],
              'delete' => [
                '#type' => 'link',
                '#title' => $this->t('Delete'),
                '#url' => Url::fromRoute('ckan.dataset.delete', ['dataset' => $dataset->getId()]),
                '#attributes' => [
                  'class' => ['buttonswitch__button', 'is-active'],
                ],
              ],
              'data-sources' => [
                '#type' => 'link',
                '#title' => $this->t('Manage data sources'),
                '#url' => Url::fromRoute('ckan.dataset.datasources', ['dataset' => $dataset->getId()]),
                '#attributes' => [
                  'class' => ['buttonswitch__button'],
                ],
              ],
            ],
          ],
        ];
      }

      $form['id'] = [
        '#type' => 'hidden',
        '#value' => $dataset->getId(),
      ];

      $form['markup'] = [
        '#markup' => '<p>' . $this->t('Are you sure you want to delete the @entityType %title?', [
          '@entityType' => $this->t('dataset'),
          '%title' => $dataset->getTitle(),
        ]) . '</p>',
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
      ];

      $form['cancel'] = [
        '#markup' => Link::createFromRoute($this->t('Cancel'), 'ckan.dataset.view', ['dataset' => $dataset->getName()])
          ->toString(),
      ];

      return $form;
    }

    throw new NotFoundHttpException();
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->ckanRequest->setCkanUser($this->getUser())
      ->deleteDataset($form_state->getValue('id'));
    $form_state->setRedirect('donl_search.search.dataset');
  }

}
