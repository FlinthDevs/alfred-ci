<?php

$node = $variables['elements']['#node'];

  if ($node->getType() == 'operation' && $variables['view_mode'] == 'full') {
    $filters = [];
    $facets = [];

    $json = $node->get('field_json')->getValue();
    $json = $json[0]['value'];
    $json = Json::decode($json);

    /******* Background *******/
    if ($node->hasField('field_background_picture')) {
      /** @var \Drupal\media\Entity\Media $background_medias */
      $background_media = $node->get('field_background_picture')
        ->referencedEntities();

      if (!empty($background_media)) {
        $background_media = $background_media[0];

        if ($background_media->hasField('field_media_image')) {
          /** @var \Drupal\file\Entity\File[] $image_files */
          $image_files = $background_media->get('field_media_image')
            ->referencedEntities();

          if (!empty($image_files)) {
            $file = $image_files[0];
            $variables['background_url'] = file_create_url($file->getFileUri());
          }
        }
      }
    }

    /***************************** ELK **********************************/
    $page_num = 0;

    // Recover GET parameters.
    $params = \Drupal::request()->query->all();

    if (!empty($params)) {
      /** @var \Drupal\pomona_search\Service\ParameterHelperInterface $parameterService */
      $parameterService = \Drupal::service('pomona_search.parameter_helper');

      $page_num = $parameterService->generateFiltersAndFacets($params, $filters, $facets);
    }
    $variables['num_page'] = $page_num;

    // If the search is full-text and numerical,
    // fill the string with 0 to have 7 character'.
    if (isset($filters['q']) &&
      !empty($filters['q']['values']) &&
      is_numeric($filters['q']['values'])) {
      $filters['q']['values'] = str_pad($filters['q']['values'], 7, '0', STR_PAD_LEFT);
    }

    // Interrogate the elk.
    /** @var \Drupal\pomona_search\Service\SearchOperationHelperInterface $searchFamilyService */
    $searchOperationService = \Drupal::service('pomona_search.search_operation_helper');

    $operation_information = ['total' => 0];
    if (!empty($json)) {
      if (isset($json['recherches']) && !empty($json['recherches'][0])) {
        $operation_information = $searchOperationService->searchOperationFacet(
          $searchOperationService::SEARCH_SIZE_RESULT,
          $page_num * $searchOperationService::SEARCH_SIZE_RESULT,
          $json['recherches'][0], $filters, $facets
        );
      }
      elseif (isset($json['produits']) && !empty($json['produits'][0])) {
        $operation_information = $searchOperationService->searchOperationProduct(
          $searchOperationService::SEARCH_SIZE_RESULT,
          $page_num * $searchOperationService::SEARCH_SIZE_RESULT,
          $json['produits'][0], $filters, $facets
        );
      }
    }

    // Generate pagination.
    $variables['product_number'] = $operation_information['total'];

    if ($operation_information['total'] != 0) {
      $variables['products'] = [
        'total' => $operation_information['total'],
        'nb_pages' => ceil($operation_information['total'] / $searchOperationService::SEARCH_SIZE_RESULT),
      ];

      // Generate renderable array of products.
      /** @var \Drupal\pomona_product\Service\ProductHelperInterface $product_services */
      $product_services = \Drupal::service('pomona_product.product_helper');

      /* Get product_nid by pim code */
      $products_nids = $product_services->getProductByRef($operation_information['results']);

      if ($products_nids != NULL) {
        // @codingStandardsIgnoreStart
        $products_displayed = Node::loadMultiple($products_nids);
        // @codingStandardsIgnoreEnd
        $products_displayed = $product_services->sortProductsByResultPosition($products_displayed, $operation_information['results']);

        $node_viewer = \Drupal::entityTypeManager()->getViewBuilder('node');

        if ($page_num == 0) {
          if (!empty($products_displayed)) {
            $variables['products']['first_product'] = $node_viewer->view(array_shift($products_displayed), 'teaser_big');
          }
          if (!empty($products_displayed)) {
            $variables['products']['second_product'] = $node_viewer->view(array_shift($products_displayed), 'teaser_list');
          }
          if (!empty($products_displayed)) {
            $variables['products']['third_product'] = $node_viewer->view(array_shift($products_displayed), 'teaser_list');
          }
        }
        $variables['products']['products'] = $node_viewer->viewMultiple($products_displayed, 'teaser_list');
      }
    }

    if (!empty($filters['q'])) {
      $keywords = explode(" ", $filters['q']['values']);
      foreach ($keywords as $keyword) {
        $facets['keyword'][] = $keyword;
      }
    }

    // Facets.
    /** @var \Drupal\pomona_search\Form\SearchOperationForm $form_service */
    $form_service = \Drupal::service('pomona_search.search_operation_form');
    $parent = NULL;
    $variables['facets'] = \Drupal::formBuilder()->getForm($form_service, $operation_information, $filters, $facets);

    // Initialize pager.
    pager_default_initialize($operation_information['total'], 24);
    $variables['pagination'] = [
      '#type' => 'pomona_pager',
    ];
  }

