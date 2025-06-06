<?php

namespace Drupal\orchard_banner\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
/**
 * Provides a 'Banner HTML.
 *
 * @Block(
 *   id = "orchard_banner_block",
 *   admin_label = @Translation("Orchard Banner Block"),
 *   category = @Translation("Orchard Banner Block"),
 * )
 */
class BannerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#type' => 'markup',
      '#markup' => '<div class="banners"></div>',
    ];
    $build['#cache']['max-age'] = 0;

    return $build;
  }

}
