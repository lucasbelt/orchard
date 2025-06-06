<?php

namespace Drupal\orchard_product\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrchardProductClickController extends ControllerBase {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Endpoint to log a CTA click for a product.
   */
  public function logClick(Request $request): Response {
    $product_id = $request->query->get('product_id');

    if (!$product_id) {
      return new JsonResponse(['status' => 'error', 'message' => 'No product ID provided'], 400);
    }

    $this->database->insert('orchard_product_clicks')
      ->fields([
        'product_id' => $product_id,
        'clicked_at' => \Drupal::time()->getCurrentTime(),
      ])
      ->execute();

    return new JsonResponse(['status' => 'success']);
  }

}
