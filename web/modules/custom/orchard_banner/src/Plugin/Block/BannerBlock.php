<?php

namespace Drupal\orchard_banner\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuTreeStorageInterface;
use Drupal\Core\Url;

/**
 * Provides a Banner Block based on the current menu root.
 *
 * @Block(
 *   id = "banner_block",
 *   admin_label = @Translation("Orchard Banner Block"),
 *   category = @Translation("Custom")
 * )
 */
class BannerBlock extends BlockBase {

  public function build() {
    $banner_class = 'banner-default';

    // Get current path and resolve it to a menu link (if any).
    $current_path = \Drupal::service('path.current')->getPath();
    $alias = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);

    $menu_tree = \Drupal::service('menu.link_tree');
    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');

    // Load the full main menu tree.
    $tree_parameters = new \Drupal\Core\Menu\MenuTreeParameters();
    $tree = $menu_tree->load('main', $tree_parameters);

    // Flatten the tree.
    $flat_links = [];
    $this->flattenTree($tree, $flat_links);

    // Try to find the current item and its root ancestor.
    foreach ($flat_links as $element) {
      $link = $element->link;
      $url = $link->getUrlObject();

      if ($url instanceof Url && $url->toString() === $alias) {
        $plugin_id = $link->getPluginId();
        $root_title = $this->getRootTitle($plugin_id, $menu_link_manager);

        if (strtolower($root_title) === 'root a') {
          $banner_class = 'banner-a';
        }
        elseif (strtolower($root_title) === 'root b') {
          $banner_class = 'banner-b';
        }

        break;
      }
    }

    return [
      '#markup' => '<div class="banner ' . $banner_class . '"></div>',
      '#attached' => [
        'library' => [
          'orchard_banner/banner_styles',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Flattens a menu tree to a list of links.
   */
  private function flattenTree(array $tree, array &$links) {
    foreach ($tree as $element) {
      $links[] = $element;
      if (!empty($element->subtree)) {
        $this->flattenTree($element->subtree, $links);
      }
    }
  }

  /**
   * Recursively finds the root title for a given menu link plugin ID.
   */
  private function getRootTitle(string $plugin_id, $menu_link_manager) {
    $definition = $menu_link_manager->getDefinition($plugin_id);
    if (!empty($definition['parent'])) {
      return $this->getRootTitle($definition['parent'], $menu_link_manager);
    }
    return $definition['title'] ?? '';
  }

}
