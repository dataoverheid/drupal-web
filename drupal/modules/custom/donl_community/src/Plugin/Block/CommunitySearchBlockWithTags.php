<?php

namespace Drupal\donl_community\Plugin\Block;

use Drupal\donl_community\Form\CommunitySearchForm;

/**
 * Provides the community search block.
 *
 * @Block(
 *  id = "community_search_block_with_tags",
 *  admin_label = @Translation("Community Search Block with Tags"),
 *  category = @Translation("DONL Community"),
 * )
 */
class CommunitySearchBlockWithTags extends CommunitySearchBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $community = $this->communityResolver->resolve();

    return [
      '#theme' => 'community_search_block',
      '#form' => $this->formBuilder->getForm(CommunitySearchForm::class, $community, TRUE),
      '#community' => $community,
    ];
  }

}
