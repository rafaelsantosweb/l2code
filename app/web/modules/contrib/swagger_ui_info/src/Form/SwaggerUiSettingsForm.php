<?php

namespace Drupal\swagger_ui_info\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Implements an swagger ui settings form.
 */
class SwaggerUiSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'swagger_ui_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $fid = \Drupal::state()->get('swagger_ui_file');
    $filepath = \Drupal::state()->get('swagger_ui_file_path');

    $form['file_json'] = [
      '#type' => 'managed_file',
      '#name' => 'file_json',
      '#title' => t('File'),
      '#size' => 20,
      '#description' => t('json format only'),
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#upload_location' => 'public://swagger_files',
      '#default_value' => $fid ? [$fid] : NULL,
    ];

    $form['file_json_path'] = [
      '#type' => 'textfield',
      '#title' => t('File path'),
      '#size' => 20,
      '#description' => t('Absolute path to json file.<br> Example: `@`', [
        '@' => $this->exampleSwagger(),
      ]),
      '#default_value' => !$fid && $filepath ? $filepath : '',
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * Swagger example file.
   */
  private function exampleSwagger($filename = 'swagger_cart_example.json') : string {
    $module_path = \Drupal::service('module_handler')->getModule('swagger_ui_info')->getPath();
    $path = "$module_path/accets/swagger/$filename";
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $formFile = $form_state->getValue('file_json');
    $filePath = $form_state->getValue('file_json_path');
    if ($formFile) {
      $file = File::load(reset($formFile));
      $uri = $file->getFileUri();
      $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
      $file_path = $stream_wrapper_manager->realpath();
      if (!empty($file_path) && file_exists($file_path)) {
        \Drupal::state()->set('swagger_ui_file', $file->id());
        $swagger_file_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        \Drupal::state()->set('swagger_ui_file_path', $swagger_file_url);
      }
    }
    elseif (!empty($filePath)) {
      \Drupal::state()->set('swagger_ui_file', NULL);
      \Drupal::state()->set('swagger_ui_file_path', $filePath);
    }
  }

}
