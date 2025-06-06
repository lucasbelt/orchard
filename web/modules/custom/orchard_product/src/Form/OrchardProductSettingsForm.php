<?php
namespace Drupal\orchard_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class OrchardProductSettingsForm extends ConfigFormBase {
  protected function getEditableConfigNames() {
    return ['orchard_product.settings'];
  }

  public function getFormId() {
    return 'orchard_product_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('orchard_product.settings');

    $form['block_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Block title'),
      '#default_value' => $config->get('block_title'),
    ];

    $form['admin_emails'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Admin emails'),
      '#default_value' => $config->get('admin_emails'),
      '#description' => $this->t('Separate multiple emails with commas.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('orchard_product.settings')
      ->set('block_title', $form_state->getValue('block_title'))
      ->set('admin_emails', $form_state->getValue('admin_emails'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
