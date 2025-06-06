<?php

namespace Drupal\product\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Product entity edit forms.
 *
 * @ingroup product
 */
class ProductEntityForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\product\Entity\ProductEntity $entity */
    $form = parent::buildForm($form, $form_state);
    $form['#attributes']['id'] = ['product-entity-wrapper'];
    $name = !isset($form_state->getTriggeringElement()['#name']) || $form_state->getTriggeringElement()['#name'] !== 'field_contenido_especifico_add_more';
    if ($name) {
      $form['field_product_type']['widget']['add_more']['add_more_button_product_banner_superior']['#ajax'] = [
        'callback' => '::wrapperSize',
        'wrapper' => 'product-entity-wrapper',
      ];

      $superior = $form_state->getValue('field_product_type_product_banner_superior_add_more') ?? NULL;
      $size = $form_state->getValue('field_size')[0]['value'] ?? $form['field_size']['widget']['#default_value'][0] ?? NULL;
      if ($superior || $size === 'superior') {
        $options_size = [
          '_none' => '- Seleccione un valor -',
          'superior' => 'Superior',
        ];
        $form['field_size']['widget']['#options'] = $options_size;
      }
      else {
        unset($form['field_size']['widget']['#options']['superior']);
      }

      $form['field_size']['widget']['#ajax'] = [
        'callback' => '::wrapperSize',
        'wrapper' => 'product-entity-wrapper',
      ];

      $options = [
        'new' => 'Noticia',
        'event' => 'Eventos',
        'program' => 'Programa',
        'section' => 'Seccion',
      ];
      $landing = FALSE;
      $mobile = FALSE;
      $desktop = FALSE;
      $tablet = FALSE;
      $modal = FALSE;
      if ($size) {
        switch ($size) {
          case '300x250':
            $mobile = TRUE;
            $desktop = TRUE;
            $tablet = TRUE;
            $options = [
              'new' => 'Noticia',
              'event' => 'Eventos',
            ];
            $landing = [
              2 => 'Noticias',
            ];
            break;

          case '300x600':
            $desktop = TRUE;
            $options = [
              'new' => 'Noticia',
            ];
            $landing = [
              2 => 'Noticias',
            ];
            break;

          case '320x100':
            $mobile = TRUE;
            $tablet = TRUE;
            $options = [
              'new' => 'Noticia',
              'event' => 'Eventos',
              'program' => 'Programa',
              'section' => 'Seccion',
            ];
            $landing = [
              1 => 'Home',
              2 => 'Noticias',
              3 => 'Oferta Academica',
            ];
            break;

          case '320x540':
            $mobile = TRUE;
            $modal = TRUE;
            $landing = [
              1 => 'Home',
            ];
            $options = [];
            break;

          case '690x385':
            $tablet = TRUE;
            $modal = TRUE;
            $landing = [
              1 => 'Home',
            ];
            $options = [];
            break;

          case '720x400':
            $desktop = TRUE;
            $modal = TRUE;
            $landing = [
              1 => 'Home',
            ];
            $options = [];
            break;

          case '1280x100';
            $desktop = TRUE;
            $landing = [
              1 => 'Home',
              2 => 'Noticias',
              3 => 'Oferta Academica',
            ];
            $options = [
              'new' => 'Noticia',
              'event' => 'Eventos',
              'program' => 'Programa',
              'section' => 'Seccion',
            ];
            break;

          case 'superior':
            $mobile = TRUE;
            $desktop = TRUE;
            $tablet = TRUE;
            $landing = [
              1 => 'Home',
            ];
            $options = [
              'program' => 'Programa',
            ];
            break;
        }
      }
      else {
        $landing = [];
        $options = [];
      }
      $form['help_text'] = [
        '#theme' => 'item_list',
        '#items' => [
          '300x250 Espacio para escritorio, mobile y tablet. Visible en interna de noticia (superior e inferior), eventos y landing de noticias',
          '300x600 Espacio para escritorio. Visible en interna de noticia (superior e inferior) y landing de noticias',
          '320x100 Espacio para mobile y tablet. Visible en interna de noticia, eventos, programa, seccion (superior e inferior), landing de noticias (superior e inferior), home (superior e inferior) y oferta academica',
          '320x540 Espacio para mobile, es el modal del home',
          '690x385 Espacio para tablet, es el modal del home',
          '720x400 Espacio para escritorio, es el modal del home',
          '1280x100 Espacio para escritorio. Visible en interna de noticia, eventos, programa, seccion (superior e inferior), landing de noticias (superior e inferior), home (superior e inferior) y oferta academica',
        ],
      ];
      if ($mobile && $desktop && $tablet) {
        $form['field_size']['widget']['#description'] = 'Aplica para escritorio, mobile y tablet.';
      }
      elseif ($mobile && $desktop) {
        $form['field_size']['widget']['#description'] = 'Aplica para escritorio y mobile.';
      }
      elseif ($mobile && $tablet) {
        $form['field_size']['widget']['#description'] = 'Aplica para mobile y tablet.';
      }
      elseif ($tablet) {
        $form['field_size']['widget']['#description'] = 'Aplica para tablet.';
      }
      elseif ($mobile) {
        $form['field_size']['widget']['#description'] = 'Aplica para mobile.';
      }
      elseif ($desktop) {
        $form['field_size']['widget']['#description'] = 'Aplica para escritorio.';
      }
      if ($modal) {
        $form['field_size']['widget']['#description'] .= ' Es un modal';
      }
      $form['field_landing']['widget']['#options'] = $landing;
      $form['field_content_type']['widget']['#options'] = $options;
      $especifico = [];
      foreach ($options as $key => $option) {
        $especifico[$key] = $key;
      }

      foreach ($form['field_contenido_especifico']['widget'] as $key => $value) {
        if (is_numeric($key)) {
          $form['field_contenido_especifico']['widget'][$key]['target_id']['#selection_settings']['target_bundles'] = $especifico;
        }
      }
    }
    return $form;

  }

  /**
   *
   */
  public function wrapperSize(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label product entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label product entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.product_entity.canonical', ['product_entity' => $entity->id()]);
  }

}
