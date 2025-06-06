<?php

namespace Drupal\product\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the product entity entity.
 *
 * @ingroup product
 *
 * @ContentEntityType(
 *   id = "product_entity",
 *   label = @Translation("product entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\product\ProductEntityListBuilder",
 *     "views_data" = "Drupal\product\Entity\ProductEntityViewsData",
 *     "translation" = "Drupal\product\ProductEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\product\Form\ProductEntityForm",
 *       "add" = "Drupal\product\Form\ProductEntityForm",
 *       "edit" = "Drupal\product\Form\ProductEntityForm",
 *       "delete" = "Drupal\product\Form\ProductEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\product\ProductEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\product\ProductEntityAccessControlHandler",
 *   },
 *   base_table = "product_entity",
 *   data_table = "product_entity_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer product entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/product_entity/{product_entity}",
 *     "add-form" = "/admin/structure/product_entity/add",
 *     "edit-form" = "/admin/structure/product_entity/{product_entity}/edit",
 *     "delete-form" = "/admin/structure/product_entity/{product_entity}/delete",
 *     "collection" = "/admin/structure/product_entity",
 *   },
 *   field_ui_base_route = "product.settings"
 * )
 */
class ProductEntity extends ContentEntityBase implements ProductEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

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

    /* $fields['status']->setDescription(t('A boolean indicating whether the product entity is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]); */

    $fields['summary'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Summary'))
      ->setRequired(FALSE)
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
      ->setLabel(t('Featured'))
      ->setDescription(t('Featured product'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
