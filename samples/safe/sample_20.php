<?php

if ($form_id == 'views_exposed_form') {
    switch ($form['#id']) {
      case 'views-exposed-form-directory-page-directory-search':
        // Display label of search_api_fulltext as form title.
        $form['#prefix'] = '<h2 class="titre">' . $form['#info']['filter-search_api_fulltext']['label'] . '</h2>';
        $form['#info']['filter-search_api_fulltext']['label'] = '';

        // Get All service name.
        $db = \Drupal::database();
        $data = $db->select('node__field_name', 'name')
          ->fields('name', ['field_name_value'])
          ->condition('bundle', 'service')
          ->distinct()
          ->execute()
          ->fetchAllAssoc('field_name_value');

        $names = [];
        foreach ($data as $key => $name) {
          $names[$key] = $name->field_name_value;
        }

        // Replace textfield : field_service by select.
        $form['field_service'] = [
          '#type' => 'select',
          '#options' => $names,
          '#default_value' => '',
          '#empty_option' => t('None'),
          '#empty_value' => '',
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
