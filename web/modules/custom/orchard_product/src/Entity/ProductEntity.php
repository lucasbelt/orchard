<?php
namespace Drupal\orchard_product\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Product entity.
 *
 * @ContentEntityType(
 *   id = "product_entity",
 *   label = @Translation("Product"),
 *   base_table = "product",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   admin_permission = "administer orchard product",
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "list_builder" = "Drupal\orchard_product\ProductListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider"
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *   },
 *   links = {
 *     "canonical" = "/product/{product_entity}",
 *     "add-form" = "/admin/content/product/add",
 *     "edit-form" = "/admin/content/product/{product_entity}/edit",
 *     "delete-form" = "/admin/content/product/{product_entity}/delete",
 *     "collection" = "/admin/content/product"
 *   }
 * )
 */
class ProductEntity extends ContentEntityBase {
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the product entity entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['summary'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Summary'))
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -3,
        'settings' => [
          'rows' => 5,
        ],
      ])
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setSettings([
        'file_extensions' => 'png jpg jpeg',
        'alt_field_required' => FALSE,
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE);
    
    $fields['featured'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Featured?'))
      ->setDescription(t('Featured product'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
