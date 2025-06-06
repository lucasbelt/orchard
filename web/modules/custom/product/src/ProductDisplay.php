<?php

namespace Drupal\product;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Servicio para el sistema de publicidad.
 */
class ProductDisplay {


  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Formato de fecha.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormat;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Width block.
   *
   * @var int
   */
  private $width;
  /**
   * Height block.
   *
   * @var int
   */
  private $height;

  /**
   * Constructs a new ReturnToBlogBlock instance.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, DateFormatter $dateFormat, AccountProxy $current_user) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormat = $dateFormat;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function viewProduct($text_size, $position = 'superior') {
    $dispositivo = $this->dispositivo($text_size);
    $output = [];
    if ($dispositivo) {
      $modal = FALSE;
      switch ($text_size) {
        case '320x540':
          $modal = TRUE;
          break;

        case '690x385':
          $modal = TRUE;
          break;

        case '720x400':
          $modal = TRUE;
          break;
      }
      $campana = $this->entityTypeManager->getStorage('taxonomy_term');
      $now = $this->dateFormat->format(time(), 'custom', 'Y-m-d\TH:i:s');
      $campana_ids = $campana->getQuery()
        ->accessCheck(FALSE)
        ->condition('vid', 'campana')
        ->condition('field_inicio', $now, '<')
        ->condition('field_final', $now, '>')
        ->execute();
      $prioridad = [];
      if ($campana_ids) {
        $roles = $this->currentUser->getRoles();
        foreach ($campana_ids as $id) {
          $prioridad[] = [
            'id' => $id,
            'prioridad' => 1,
          ];
        }
        $size = explode('x', $text_size);
        $this->width = $size[0];
        $this->height = $size[1];

        $node = $this->routeMatch->getParameter('node');
        $localization = FALSE;
        if ($node instanceof NodeInterface && $type = $node->bundle()) {
          $localization = $node->bundle();
          $query = $this->entityTypeManager->getStorage('product_entity')->getQuery();
          $group = $query->orConditionGroup();
          foreach ($campana_ids as $id) {
            $group->condition('field_campana', $id);
          }
          $qroles = $query->orConditionGroup();
          foreach ($roles as $rol) {
            $qroles->condition('field_roles', $rol);
          }
          $content_type = $query->orConditionGroup();
          $content_type->condition('field_content_type', $type);
          $content_type->condition('field_contenido_especifico', $node->id());
          $results = $query->condition($group)
            ->condition($qroles)
            ->condition($content_type)
            ->condition('field_size', $text_size)
            ->condition('field_position', $position)
            ->accessCheck(TRUE)
            ->execute();
          if ($results) {
            $output = $this->renderAdvertisign($results, $modal);
          }
        }
        else {
          $landing = NULL;
          if ($this->routeMatch->getRouteName() === 'view.frontpage.page_1') {
            $landing = 1;
            $localization = 'home';
          }
          elseif ($this->routeMatch->getRouteName() === 'view.news_list.main_page') {
            $landing = 2;
            $localization = 'noticias';
          }
          elseif ($this->routeMatch->getRouteName() === 'view.academic_offer_page_terms.page_1') {
            $landing = 3;
            $localization = 'ofertas';
          }
          if ($landing) {
            $query = $this->entityTypeManager->getStorage('product_entity')->getQuery();
            $group = $query->orConditionGroup();
            foreach ($campana_ids as $id) {
              $group->condition('field_campana', $id);
            }
            $qroles = $query->orConditionGroup();
            foreach ($roles as $rol) {
              $qroles->condition('field_roles', $rol);
            }
            $results = $query->condition($group)
              ->condition($qroles)
              ->condition('field_landing', $landing)
              ->condition('field_size', $text_size)
              ->condition('field_position', $position)
              ->accessCheck(TRUE)
              ->execute();
            if ($results) {
              $output = $this->renderAdvertisign($results, $modal);
            }
          }
        }
      }

    }
    if ($output) {
      $output['#localization'] = $localization;
    }
    return $output;
  }

  /**
   * Undocumented function.
   */
  public function renderAdvertisign($ids, $modal) {
    $products = $this->entityTypeManager->getStorage('product_entity')->loadMultiple($ids);
    $prioridad = [];
    $output = FALSE;
    foreach ($products as $product) {
      $prioridad[] = [
        'id' => $product->id(),
        'prioridad' => $product->field_prioridad->value ?? 1,
      ];
    }

    if ($prioridad) {
      $elemento = $this->seleccionarElemento($prioridad);
      foreach ($products as $product) {
        if ($product->id() == $elemento) {
          $campana_name = $product->field_name->entity->get('name')->value;
          $product_type = $product->field_product_type->entity ?? FALSE;
          if ($product_type) {
            $product_name = $product->get('name')->value;
            $output = $this->process($product_type->bundle(), $product_type, $modal);
            $output['#campana'] = $campana_name;
            $output['#name'] = $product_name;
          }
          break;
        }
      }
    }
    return $output;
  }

  /**
   *
   */
  public function process($type, $entity, $modal) {
    switch ($type) {
      case 'product_image':
        $image = $entity->field_product_image->entity;
        $output = [
          '#theme' => 'product_image',
          '#width' => $this->width,
          '#height' => $this->height,
          '#url' => $image->createFileUrl(),
          '#path' => $entity->field_ruta->uri ? Url::fromUri($entity->field_ruta->uri) : NULL,
          '#modal' => $modal,
        ];
        break;

      case 'product_video':
        $output = [
          '#theme' => 'product_video',
          '#width' => $this->width,
          '#height' => $this->height,
          '#src' => $entity->field_product_video->url,
          '#title' => $entity->field_product_video->title,
          '#modal' => $modal,
        ];
        break;

      case 'product_html':
        $output = [
          '#theme' => 'product_html',
          '#width' => $this->width,
          '#height' => $this->height,
          '#body' => $entity->field_html->value,
          '#modal' => $modal,
        ];
        break;

      case 'product_banner_superior':
        $output = [
          '#theme' => 'product_superior',
          '#body' => $entity->field_texto->value,
          '#path' => $entity->field_ruta->uri ? Url::fromUri($entity->field_ruta->uri) : NULL,
          '#button' => $entity->field_button_name->value ?? 'Ir',
        ];
        break;

      default:
        $output = [];
        break;
    }

    return $output;
  }

  /**
   *
   */
  public function calcularFrecuencias($elementos) {
    $frecuenciasAjustadas = [];
    $sumaFrecuenciasAjustadas = 0;

    // Calcular frecuencias ajustadas.
    foreach ($elementos as $elemento) {
      $prioridad = $elemento['prioridad'];
      $frecuenciaAjustada = 1 / $prioridad;
      $frecuenciasAjustadas[$elemento['id']] = $frecuenciaAjustada;
      $sumaFrecuenciasAjustadas += $frecuenciaAjustada;
    }

    // Normalizar frecuencias.
    $frecuenciasNormalizadas = [];
    foreach ($frecuenciasAjustadas as $id => $frecuenciaAjustada) {
      $frecuenciaNormalizada = $frecuenciaAjustada / $sumaFrecuenciasAjustadas;
      $frecuenciasNormalizadas[$id] = $frecuenciaNormalizada;
    }

    return $frecuenciasNormalizadas;
  }

  /**
   *
   */
  public function seleccionarElemento($elementos) {
    // Calcula las frecuencias ajustadas y normalizadas.
    $frecuencias = $this->calcularFrecuencias($elementos);

    // Genera un número aleatorio entre 0 y 1.
    $random = mt_rand() / mt_getrandmax();

    // Elige el elemento según la probabilidad generada aleatoriamente.
    $acumulador = 0;
    foreach ($frecuencias as $id => $probabilidad) {
      $acumulador += $probabilidad;
      if ($random <= $acumulador) {
        return $id;
      }
    }

    // En caso de que algo salga mal, devolver el último elemento.
    return end($elementos)['id'];
  }

  /**
   * Dispositivo desde donde se llama el bloque.
   */
  public function dispositivo($size) {
    $tablet_browser = 0;
    $mobile_browser = 0;

    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
      $tablet_browser++;
    }

    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
      $mobile_browser++;
    }

    if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
      $mobile_browser++;
    }

    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
    $mobile_agents = [
      'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
      'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
      'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
      'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
      'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
      'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
      'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
      'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
      'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-',
    ];

    if (in_array($mobile_ua, $mobile_agents)) {
      $mobile_browser++;
    }

    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera mini') > 0) {
      $mobile_browser++;
      // Check for tablets on opera mini alternative headers.
      $stock_ua = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] ?? ($_SERVER['HTTP_DEVICE_STOCK_UA'] ?? ''));
      if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
        $tablet_browser++;
      }
    }
    if ($tablet_browser > 0) {
      $dispositivo = 'tablet';
    }
    elseif ($mobile_browser > 0) {
      $dispositivo = 'mobile';
    }
    else {
      $dispositivo = 'desktop';
    }

    $mobile = FALSE;
    $desktop = FALSE;
    $tablet = FALSE;
    switch ($size) {
      case 'superior':
        $mobile = TRUE;
        $desktop = TRUE;
        $tablet = TRUE;
        break;

      case '300x250':
        $mobile = TRUE;
        $desktop = TRUE;
        $tablet = TRUE;
        break;

      case '300x600':
        $desktop = TRUE;
        break;

      case '320x100':
        $mobile = TRUE;
        $tablet = TRUE;
        break;

      case '320x540':
        $mobile = TRUE;
        break;

      case '690x385':
        $tablet = TRUE;
        break;

      case '720x400':
        $desktop = TRUE;
        break;

      case '1280x100';
        $desktop = TRUE;
        break;
    }

    $render = FALSE;
    if ($dispositivo === 'mobile' && $mobile) {
      $render = TRUE;
    }
    elseif ($dispositivo === 'tablet' && $tablet) {
      $render = TRUE;
    }
    elseif ($dispositivo === 'desktop' && $desktop) {
      $render = TRUE;
    }
    return $render;
  }

}
