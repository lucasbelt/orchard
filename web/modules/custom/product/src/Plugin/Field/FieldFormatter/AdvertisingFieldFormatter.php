<?php

namespace Drupal\advertising\Plugin\Field\FieldFormatter;

use Drupal\advertising\AdvertisingDisplay;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Advertising Field' formatter.
 *
 * @FieldFormatter(
 *   id = "advertising_field_formatter",
 *   label = @Translation("Advertising Field"),
 *   field_types = {
 *     "advertising_field_item"
 *   }
 * )
 */
class AdvertisingFieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {


  /**
   * The current route match.
   *
   * @var \Drupal\advertising\AdvertisingDisplay
   */
  protected $advertising;

  /**
   * Constructs a new ReturnToBlogBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AdvertisingDisplay $advertising) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->advertising = $advertising;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('advertising.display')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => FALSE,
      'position' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements['size'] = [
      '#type' => 'select',
      '#title' => $this->t('Size'),
      '#description' => $this->t('Select size for this block'),
      '#options' => [
        '300x250' => '300x250',
        '300x600' => '300x600',
        '320x100' => '320x100',
        '320x540' => '320x540',
        '690x385' => '690x385',
        '720x400' => '720x400',
        '1280x100' => '1280x100',
      ],
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
    ];
    $elements['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#description' => $this->t('Colocar superior por defecto cuando no exista inferior'),
      '#options' => [
        'superior' => 'Superior',
        'inferior' => 'Inferior',
      ],
      '#default_value' => $this->getSetting('position'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $output = $this->advertising->viewAdvertising($this->getSetting('size'), $this->getSetting('position'));
    $element[] = $output;
    return $element;
  }

}
