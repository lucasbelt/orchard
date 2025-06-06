<?php
namespace Drupal\orchard_banner\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class BannerSubscriber implements EventSubscriberInterface {

  protected $routeMatch;
  protected $entityTypeManager;

  public function __construct(RouteMatchInterface $routeMatch, EntityTypeManagerInterface $entityTypeManager) {
    $this->routeMatch = $routeMatch;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function getSubscribedEvents() {
    return [
      KernelEvents::VIEW => ['addBannerClass', 100],
    ];
  }

  public function addBannerClass(ViewEvent $event) {
    $route = $this->routeMatch->getRouteName();
    $parameters = $this->routeMatch->getParameters();

    if ($parameters->has('node')) {
      $node = $parameters->get('node');

      $menu_links = $this->entityTypeManager->getStorage('menu_link_content')
        ->loadByProperties(['menu_name' => 'main']);

      foreach ($menu_links as $link) {
        $url = $link->getUrlObject();
        if ($url->isRouted() && $url->getRouteName() === 'entity.node.canonical') {
          if ((int) $url->getRouteParameters()['node'] === (int) $node->id()) {
            $parent = $link->get('parent');
            foreach ($parent as $root) {
            }
            $parent = $parent->getString();
            if (str_contains($parent, 'root-a')) {
              $this->addLibraryAndClass('banner-a');
            } elseif (str_contains($parent, 'root-b')) {
              $this->addLibraryAndClass('banner-b');
            }
          }
        }
      }
    }
  }

  protected function addLibraryAndClass($class_name) {
    $attach = \Drupal::service('renderer')->renderRoot([
      '#attached' => [
        'library' => ['orchard_banner/banner_styles'],
        'html_head' => [[
          [
            '#tag' => 'script',
            '#value' => "document.addEventListener('DOMContentLoaded', function() { document.querySelector('.banner').classList.add('{$class_name}'); });",
          ],
          'menu_banner_script',
        ]],
      ],
    ]);
  }
}
