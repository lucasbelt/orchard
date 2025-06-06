<?php
namespace Drupal\product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'product' block.
 *
 * @Block(
 *   id = "product_block",
 *   admin_label = @Translation("Random Product Block")
 * )
 */
class ProductBlock extends BlockBase {
  public function build() {
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'product_entity')
      ->condition('field_featured', 1)
      ->range(0, 5)
      ->execute();

    if (empty($nids)) {
      return ['#markup' => 'No featured product available'];
    }

    $random_nid = array_rand($nids);
    $node = Node::load($random_nid);

    return [
      '#theme' => 'product',
      '#title' => $node->label(),
      '#summary' => $node->get('body')->value,
      '#image_url' => file_create_url($node->get('field_image')->entity->getFileUri()),
      '#cta_link' => $node->toUrl()->toString(),
      '#cache' => ['max-age' => 0],
    ];
  }
}
