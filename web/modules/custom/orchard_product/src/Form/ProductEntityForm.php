<?php
namespace Drupal\orchard_product\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

class ProductEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $status = $entity->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Created the %label Product.', ['%label' => $entity->label()]));
    }
    else {
      $this->messenger()->addMessage($this->t('Updated the %label Product.', ['%label' => $entity->label()]));
    }

    $form_state->setRedirect('entity.product.collection');
  }
}
