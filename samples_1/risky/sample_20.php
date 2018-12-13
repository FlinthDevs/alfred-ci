<?php

/**
 * @file
 * Contains hook implementations for vdg_views module.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;

/**
 * Implements hook_theme().
 */
function vdg_views_theme($existing, $type, $theme, $path) {
  return [
    'vdg_media_file_type_picto' => [
      'variables' => [
        'file_extension' => '',
      ],
    ],
    'vdg_search_did_you_mean' => [
      'variables' => [
        'suggestion' => '',
      ],
    ],
    'vdg_views_facets_diary_page' => [
      'variables' => [
        'facet_zoom_on' => [],
        'facet_for_who' => [],
        'facet_when' => [],
        'facet_what' => [],
        'facet_where' => [],
      ],
    ],
  ];
}

/**
 * Prepares variables for views RSS item templates.
 *
 * Default template: views-view-row-rss.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - row: The raw results rows.
 */
function vdg_views_preprocess_views_view_row_rss(array &$variables) {
  $item = $variables['row'];

  $variables['item_elements'] = [];
  foreach ($item->elements as $element) {

    // Remove the native creator data.
    if ($element['key'] == 'dc:creator') {
      continue;
    }
    // Alter the guid native data.
    elseif ($element['key'] == 'guid') {
      $element['attributes'] = [];
    }

    // Get dynamical item attributes to set.
    if (isset($element['attributes']) && is_array($element['attributes'])) {
      $element['attributes'] = new Attribute($element['attributes']);
    }
    $variables['item_elements'][] = $element;
  }
}

/**
 * Implements hook_form_alter().
 */
function vdg_views_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if ($form_id == 'views_exposed_form') {
    switch ($form['#id']) {
      case 'views-exposed-form-directory-page-directory-search':
        $names = [
          'none' => t('None'),
        ];

        // Display label of search_api_fulltext as form title.
        $form['#prefix'] = '<h2 class="titre">' . $form['#info']['filter-search_api_fulltext']['label'] . '</h2>';
        $form['#info']['filter-search_api_fulltext']['label'] = '';

        // Get All service name.
        $db = \Drupal::database();
        $data = $db->select('node__field_name', 'name')
          ->fields('name', ['field_name_value'])
          ->condition('bundle', 'service')
          ->execute()
          ->fetchAllAssoc('field_name_value');

        foreach ($data as $key => $name) {
          $names[$key] = $name->field_name_value;
        }

        // Replace textfield : field_service by select.
        $form['field_service'] = [
          '#type' => 'select',
          '#options' => $names,
          '#default_value' => 'none',
        ];

        // Add specific class.
        $form['#attributes']['class'][] = 'recherche-specifique__formulaire';
        break;

      case 'views-exposed-form-search-page-page':
        $current_request = \Drupal::requestStack()->getCurrentRequest()->attributes->all();
        if (isset($current_request['view_id']) && $current_request['view_id'] == 'search_page') {
          $configPage = config_pages_config('config_contents_lists');

          if ($configPage != NULL) {
            $form['search_page_header'] = \Drupal::entityTypeManager()->getViewBuilder('config_pages')
              ->view($configPage, $current_request['view_id']);
            $form['search_page_second_header'] = \Drupal::entityTypeManager()->getViewBuilder('config_pages')
              ->view($configPage, $current_request['view_id'] . '_second_header');
          }
        }
        break;
    }
  }
}

/**
 * Implements hook_search_api_autocomplete_suggestions_alter().
 */
function vdg_views_search_api_autocomplete_suggestions_alter(array &$suggestions, array $alter_params) {

  $views_list = [
    'search_procedure_form',
    'search_page',
  ];

  if (in_array($alter_params['search']->id(), $views_list)) {

    /** @var \Drupal\search_api_autocomplete\Suggestion\Suggestion $suggestion */
    foreach ($suggestions as $key => &$suggestion) {
      $options = $suggestion->getUrl()->getOptions();
      $node = $options['entity'];

      // Turn off url so that value becomes suggested key, rather than label.
      $suggestion->setSuggestedKeys($node->label());
      // Otherwise, the output of the label is used as the value.
      $suggestion->setUrl(NULL);
    }
  }
}
