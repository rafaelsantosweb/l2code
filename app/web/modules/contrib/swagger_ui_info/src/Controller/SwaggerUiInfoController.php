<?php

namespace Drupal\swagger_ui_info\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for swagger_ui_info module routes.
 */
class SwaggerUiInfoController extends ControllerBase {

  /**
   * Builds the state variable overview page.
   *
   * @return array
   *   Array of page elements to render.
   */
  public function infoPage() {
    $token = \Drupal::service('csrf_token')->get('rest');

    $library_name = 'swagger_ui_formatter.swagger_ui_integration';
    /** @var \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery */
    $library_discovery = \Drupal::service('library.discovery');
    /** @var \Drupal\swagger_ui_formatter\Service\SwaggerUiLibraryDiscoveryInterface $swagger_ui_library_discovery */
    $swagger_ui_library_discovery = \Drupal::service('swagger_ui_formatter.swagger_ui_library_discovery');

    // The Swagger UI library integration is only registered if the Swagger UI
    // library directory and version is correct.
    if ($library_discovery->getLibraryByName('swagger_ui_formatter', $library_name) === FALSE) {
      $output = [
        '#theme' => 'status_messages',
        '#message_list' => [
          'error' => [$this->t('The Swagger UI library is missing, incorrectly defined or not supported.')],
        ],
      ];
    }
    else {
      $library_dir = $swagger_ui_library_discovery->libraryDirectory();
      // Set the oauth2-redirect.html file path for OAuth2 authentication.
      $oauth2_redirect_url = \Drupal::request()->getSchemeAndHttpHost() . '/' . $library_dir . '/dist/oauth2-redirect.html';

      // It's the user's responsibility to set up field settings correctly
      // and use this field formatter with valid Swagger files. Although, it
      // could happen that a URL could not be generated from a field value.
      $swagger_file_url = \Drupal::state()->get('swagger_ui_file_path');

      if ($swagger_file_url === NULL) {
        $output = [
          '#theme' => 'status_messages',
          '#message_list' => [
            'error' => [$this->t('Could not create URL to file.')],
          ],
        ];
      }
      else {
        $output = [
          '#theme' => 'swagger_ui_info',
          '#name' => 'api-display',
          '#attached' => [
            'library' => [
              'swagger_ui_formatter/' . $library_name,
              'swagger_ui_info/swagger_ui_integration',
            ],
            'drupalSettings' => [
              'swaggerUIFormatter' => [
                "api-display" => [
                  'csrfToken' => $token,
                  'oauth2RedirectUrl' => $oauth2_redirect_url,
                  'swaggerFile' => $swagger_file_url,
                  'docExpansion' => 'list',
                  'supportedSubmitMethods' => [
                    "get",
                    "put",
                    "post",
                    "delete",
                    "options",
                    "head",
                    "patch",
                  ],
                ],
              ],
            ],
          ],
        ];
      }
    }

    return $output;
  }

}
