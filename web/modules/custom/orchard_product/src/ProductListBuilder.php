<?php
namespace Drupal\orchard_product;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of Product entities.
 */
class ProductListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['id'] = $this->t('ID');
    $header['name'] = $this->t('Name');
    $header['summary'] = $this->t('Summary');
    $header['featured'] = $this->t('Featured');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\orchard_product\Entity\ProductEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = $entity->get('name')->value;
    $row['summary'] = $entity->get('summary')->value;
    $row['featured'] = $entity->get('featured')->value ? $this->t('Yes') : $this->t('No');
    return $row + parent::buildRow($entity);
  }

}
