<?php

namespace Drupal\orchard_product\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'Random Product ' Block.
 *
 * @Block(
 *   id = "random_product_block",
 *   admin_label = @Translation("Product of the Day"),
 *   category = @Translation("Custom")
 * )
 */
class RandomProductBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entityTypeManager;
  protected $renderer;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  public function build(): array {
    $storage = $this->entityTypeManager->getStorage('product_entity');

    $query = $storage->getQuery()
      ->condition('featured', 1)
      ->accessCheck(TRUE);

    $ids = $query->execute();

    if (empty($ids)) {
      return ['#markup' => $this->t('No featured products found.')];
    }

    $random_id = array_rand($ids);
    $product = $storage->load($random_id);

    if (!$product) {
      return ['#markup' => $this->t('Could not load product.')];
    }

    $fid = $product->get('image')->target_id;
    $file = \Drupal\file\Entity\File::load($fid);
    if ($file) {
      $image = [
        '#theme' => 'image_style',
        '#style_name' => 'medium',
        '#uri' => $file->getFileUri(),
        '#alt' => $product->get('image')->alt ?? '',
        '#title' => $product->label(),
      ];
    } else {
      $image = '<p>No image available</p>';
    }
    $link = Link::fromTextAndUrl(
      $this->t('View Product'),
      Url::fromRoute('entity.product_entity.canonical', ['product_entity' => $product->id()])
    )->toRenderable();

    $link['#attributes']['class'][] = 'cta-link';
    $link['#attributes']['data-product-id'] = $product->id();
    $build = [
      '#theme' => 'random_product_block',
      '#name' => $product->get('name')->value,
      '#summary' => $product->get('summary')->value,
      '#image' => $image,
      '#link' => $link,
      '#cache' => ['max-age' => 0],
      '#attached' => [
        'library' => [
          'orchard_product/cta_click',
        ],
      ],
    ];

    return $build;
  }

}
