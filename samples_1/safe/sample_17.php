<?php

namespace Drupal\vdg_media;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Component\Utility\NestedArray;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class IptcManager.
 *
 * @see https://www.drupal.org/project/iptc_media
 */
class IptcManager {

  /**
   * Form API callback: Processes a file_generic field element.
   *
   * Expands the file_generic type to include the description and display
   * fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   *
   * @param array $element
   *   The field where the function is attached.
   * @param array|\Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of field.
   * @param array|\Drupal\Core\Form\FormStateInterface $form
   *   The form of field.
   *
   * @return array
   *   The field where the function is attached with modification.
   */
  public static function processIptcFile(array &$element, FormStateInterface $form_state, array $form) : array {
    $element['upload_button']['#ajax']['callback'] = [self::class, 'iptcUploadAjaxCallback'];

    return $element;
  }

  /**
   * Function iptcUploadAjaxCallback.
   *
   * @param array|\Drupal\Core\Form\FormStateInterface $form
   *   The form of field.
   * @param array|\Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of field.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request at the add of image file in the add media form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response of the request in parameters with custom attachments/commands.
   */
  public static function iptcUploadAjaxCallback(&$form, FormStateInterface &$form_state, Request $request) : AjaxResponse {
    $fields = [
      'copyright' => ['#edit-field-media-copyright-0-value'],
      'creation' => ['#edit-created-0-value-time'],
      'created' => ['#edit-created-0-value-date'],
      'credit' => ['#edit-field-media-credits-0-value'],
      'note' => ['#edit-field-media-legend-0-value'],
      'title' => ['#edit-field-media-title-0-value', '#edit-name-0-value'],
    ];
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $form_parents = explode('/', $request->query->get('element_parents'));

    // Retrieve the element to be rendered.
    $form = NestedArray::getValue($form, $form_parents);
    $form['#suffix'] .= '<span class="ajax-new-content"></span>';
    $status_messages = ['#type' => 'status_messages'];
    $form['#prefix'] .= $renderer->renderRoot($status_messages);
    $output = $renderer->renderRoot($form);

    $response = new AjaxResponse();
    $response->setAttachments($form['#attached']);
    $field_value = $form_state->getValue('field_media_image');

    // When the file field is not limited:
    $fids = [];
    foreach ($field_value as $val) {
      if (isset($val['fids']) && !empty($val['fids'])) {
        $fids[] = $val['fids'][0];
      }
    }

    if (empty($fids)) {
      return $response->addCommand(new ReplaceCommand(NULL, $output));
    }

    $file = File::load($fids[0]);
    $filename = $file->getFilename();

    $iptc_data = self::extractIptcData($fids[0]);

    // Set "file name".
    if (isset($filename)) {
      $response->addCommand(new InvokeCommand('#edit-field-media-filename-0-value', 'val', [$filename]));
    }

    foreach ($fields as $key => $selectors) {
      if (isset($iptc_data[$key])) {
        foreach ($selectors as $selector) {
          $response->addCommand(new InvokeCommand($selector, 'val', [$iptc_data[$key]]));
        }
      }
    }

    return $response->addCommand(new ReplaceCommand(NULL, $output));
  }

  /**
   * Function extractIptcData.
   */
  public static function extractIptcData($fid) {
    $data = [];
    $fields = [
      '2#122' => 'autor',
      '2#120' => 'note',
      '2#118' => 'contact',
      '2#116' => 'copyright',
      '2#115' => 'source',
      '2#110' => 'credit',
      '2#105' => 'title',
      '2#103' => 'reference',
      '2#101' => 'country',
      '2#100' => 'country_code',
      '2#095' => 'province',
      '2#092' => 'region',
      '2#090' => 'city',
      '2#085' => 'title_creator',
      '2#080' => 'creator',
      '2#070' => 'version',
      '2#075' => 'cycle',
      '2#005' => 'object name',
      '2#007' => 'status',
      '2#040' => 'instructions',
      '2#022' => 'identifier',
      '2#026' => 'location',
      '2#010' => 'priority',
      '2#065' => 'program',
    ];

    $file = File::load($fid);
    if ('image/jpeg' == $file->getMimeType()) {
      $info = [];
      $size = getimagesize($file->getFileUri(), $info);
      if (!empty($size)) {
        if (isset($info['APP13'])) {
          if ($iptc = iptcparse($info['APP13'])) {
            foreach ($fields as $key => $data_field_name) {
              if (isset($iptc[$key][0])) {
                $data[$data_field_name] = self::checkUtf8($iptc[$key][0]);
              }
            }

            // 2#015 : catégories.
            if (isset($iptc['2#015'])) {
              $data['categories'] = [];
              foreach ($iptc['2#015'] as $categorie) {
                $data['categories'][] = mb_strtolower(self::checkUtf8($categorie), 'UTF-8');
              }
            }

            // 2#025 : mot clé.
            if (isset($iptc['2#025'])) {
              $data['keywords'] = [];
              foreach ($iptc['2#025'] as $keyword) {
                $data['keywords'][] = mb_strtolower(self::checkUtf8($keyword), 'UTF-8');
              }
            }

            // 2#060 : creation hour.
            if (isset($iptc['2#060'][0])) {
              $hour = $iptc['2#060'][0];
              $data['created_hour'] = substr($hour, 0, 2) . ':' . substr($hour, 2, 2) . ':' . substr($hour, 4, 2);
            }

            // 2#055 : creation date.
            if (isset($iptc['2#055'][0])) {
              $date = $iptc['2#055'][0];
              $data['created'] = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
            }
          }
        }
      }
    }

    return $data;
  }

  /**
   * Class convertUtf8.
   *
   * @param string $string
   *   The string to convert.
   *
   * @return string
   *   THe string converted.
   */
  public static function checkUtf8(string $string) : string {
    if ('UTF-8' != mb_detect_encoding($string, 'UTF-8', TRUE)) {
      $string = utf8_encode($string);
    }

    return $string;
  }

}
