<?php

namespace Drupal\donl_custom_http_4xx\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\donl_custom_http_4xx\Form\Search4xxForm;

/**
 * Custom HTTP 404 controller.
 */
class CustomHttp4xxController extends ControllerBase {

  /**
   * The default 404 content.
   *
   * @param string $type
   *   The type of the object not found.
   *
   * @return array
   *   A Drupal render array.
   */
  public function on404(string $type): array {
    return [
      [
        '#type' => 'html_tag',
        '#tag' => 'h1',
        '#value' => $this->title($type)->__toString(),
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('The @object you were looking for was not found. Sorry for the inconvenience.', ['@object' => $this->getObject($type)]),
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('There are a number of ways in which you can still access the desired information:'),
      ],
      [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#attributes' => [
          'class' => [
            'list list--linked',
          ],
        ],
        '#items' => [
          $this->t('Visit the homepage on <a href="/">data.overheid.nl</a>'),
          $this->t('Try to find your item via the <a href="/sitemap">Sitemap</a>'),
          $this->t('If you cannot find the information you seek, you can use our <a href="/contact">contactform</a> to ask your question directly.'),
        ]
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['inline-block'],
        ],
        'form' => [
          '#theme' => 'search_block',
          '#form' => $this->formBuilder()->getForm(Search4xxForm::class, $type),
        ],
      ],
    ];
  }

  /**
   * The 404 not found title.
   *
   * @param string $type
   *   The type of the object not found.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function title(string $type): TranslatableMarkup {
    return $this->t('@object not found', ['@object' => $this->getObject($type)]);
  }

  /**
   * Helper function to turn the $type into a readable value.
   *
   * @param string $type
   *   The type of the object not found.
   *
   * @return string
   *   The human readable name.
   */
  private function getObject(string $type): string {
    return $this->t(ucfirst($type))->__toString();
  }

}
