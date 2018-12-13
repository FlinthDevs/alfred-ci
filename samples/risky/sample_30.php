<?php

$node = $variables['node'];

  if ($node->bundle() == 'product') {
    if ($variables['view_mode'] == 'full') {
      // Get advice block.
      // Load Instance of custom block with variables.
      $block_id = Settings::get('pomona_product_type_advice_returned');

      $product_block = \Drupal::entityTypeManager()
        ->getStorage('block')
        ->load($block_id);

      if (!empty($product_block)) {
        $product_block_content = \Drupal::entityTypeManager()
          ->getViewBuilder('block')
          ->view($product_block);

        if ($product_block_content) {
          // Add block content to build array.
          $variables['product_advice_block_output'] = $product_block_content;
        }
      }

      // Generate the url to contact page.
      $variables['contact_request'] = Url::fromRoute(
        'entity.webform.canonical',
        ['webform' => 'contact'],
        ['query' => ['categories' => 'produit', 'q' => $node->id()]])->toString();

      // Get Json.
      if ($node->hasField('field_json')) {
        $json_text = $node->get('field_json')->getValue();

        if (!empty($json_text)) {
          $json_text = $json_text[0]['value'];

          $json = Json::decode($json_text);
          $json_base = $json['result'][0];

          // Get Updated date.
          if (!empty($json_base['dateDerniereMiseAJour'])) {
            $variables['updatedDate'] = $json_base['dateDerniereMiseAJour'];
          }
          // Get ficheInfoAchat info.
          if (!empty($json_base['ficheInfoAchat'])) {
            if (!empty($variables['ficheInfoAchat'])) {
              $variables['ficheInfoAchat'] = array_merge($variables['ficheInfoAchat'], $json_base['ficheInfoAchat']);
            }
            else {
              $variables['ficheInfoAchat'] = $json_base['ficheInfoAchat'];
            }
          }

          // Get allergenes info.
          if (!empty($json_base['allergenes'])) {
            $allergenes_description = '';
            foreach ($json_base['allergenes'] as $key => $allergenes) {
              switch ($key) {
                case 0:
                  $allergenes_description = $allergenes['designation'];
                  break;

                default:
                  $allergenes_description .= ', ' . $allergenes['designation'];
              }

            }
            $variables['ficheInfoAchat']['allergenes'] = $allergenes_description;
          }

          // Get denominationReglementaire info.
          if (!empty($json_base['denominationReglementaire'])) {
            $variables['ficheInfoAchat']['denominationReglementaire'] = $json_base['denominationReglementaire'];
          }

          if (!empty($json_base['ficheInfoAchat']['gemrcn1']['frequence']['code'])) {

            $frenquence_code_url = file_create_url(
              drupal_get_path('module', 'pomona_product') . '/templates/images/' .
              $json_base['ficheInfoAchat']['gemrcn1']['frequence']['code'] . '.png'
            );

            $variables['frequence_code'] = $frenquence_code_url;
          }

          if (!empty($json_base['conseilsMiseEnOeuvre'])) {

            $conseilsMiseEnOeuvre = '';
            foreach ($json_base['conseilsMiseEnOeuvre'] as $conseil) {
              $conseilsMiseEnOeuvre .= '<li>' . $conseil['description'] . '</li>';
            }
            $variables['conseilsMiseEnOeuvre'] = $conseilsMiseEnOeuvre;
          }

          if (!empty($json_base['centPctMaRegion'])) {
            $variables['centPctMaRegion'] = $json_base['centPctMaRegion']['designation'];
          }

          if (!empty($json_base['atouts'])) {
            $atouts_description = '';
            foreach ($json_base['atouts'] as $atout) {
              $atouts_description .= '<li>' . $atout['description'] . '</li>';
            }
            $variables['atouts'] = $atouts_description;
          }

          if (!empty($json_base['plusPlaisir'])) {
            $variables['pleasure_block'] = $json_base['plusPlaisir'];
          }
        }
      }

      $searchRecipeService = \Drupal::service('pomona_search.search_recipe_helper');

      $nodeService = \Drupal::service('pomona_common.node_helper');

      $current_product_code = $node->get('field_product_code')->getValue();
      $current_product_code_id = NULL;

      if (!empty($current_product_code[0]['value'])) {
        $current_product_code_id = $current_product_code[0]['value'];
      }

      $recipe_search_filters = [
        'ingredient1' => [
          'value' => $current_product_code_id,
        ],
      ];

      $current_user = \Drupal::currentUser();
      if ($current_user->isAuthenticated()) {
        $user = User::load($current_user->id());
        $segmentation_user = $user->field_segmentation_user->getValue();

        if (!empty($segmentation_user[0]['target_id'])) {
          $segmentation_term = Term::load($segmentation_user[0]['target_id']);
          $segmentation_title = $segmentation_term->getName();
        }
        else {
          /** @var \Drupal\pomona_sso\Service\SSOManagerInterface $sso_manager */
          $sso_manager = \Drupal::service('pomona_sso.sso_manager');
          $all_segmentations = $sso_manager->getSegmentations();

          $segmentation_term = Term::load($all_segmentations['default']);
          if (!is_null($segmentation_term)) {
            $segmentation_title = $segmentation_term->getName();
          }
        }

        $segmentation = '';
        if (!empty($segmentation_title)) {
          if ($segmentation_title == 'Commerciale') {
            $segmentation = 'cibleClientRC';
          }
          elseif ($segmentation_title == 'Collective') {
            $segmentation = 'cibleClientRS';
          }
        }

        $variables['segmentation'] = $segmentation;
        $recipe_search_filters += [
          'activites' => [
            'values' => [
              $segmentation,
            ],
          ],
        ];
      }
      $recipes_list = $searchRecipeService->searchRecipe($recipe_search_filters, 0, 3);

      $viewRecipes = [];
      $recipe_view_mode = 'teaser_big';
      $variables['more_link'] = FALSE;
      if (!empty($recipes_list)) {
        $recipes_nids = $nodeService->getNodeByPomonaRef('recipe', $recipes_list['results']);

        if ($recipes_nids != NULL) {
          $recipes = Node::loadMultiple($recipes_nids);
          if (count($recipes) > 2) {
            $variables['more_link'] = TRUE;
            array_pop($recipes);
          }
          foreach ($recipes as $recipe) {
            $viewRecipes[] = \Drupal::entityTypeManager()->getViewBuilder('node')->view($recipe, $recipe_view_mode);
          }
        }
      }

      $variables['recipe_associate_block'] = $viewRecipes;

      if (isset($variables['#cache'])) {
        $variables['#cache'] = array_merge_recursive($variables['#cache'], [
          'contexts' => ['user'],
        ]);
      }

      $variables['thematic_nid'] = $current_product_code_id;

      if ($node->hasField('field_product_family')) {
        $parent_family_tid = $node->get('field_product_family')->getValue();

        if ($parent_family_tid) {
          $parent_family = Term::load($parent_family_tid[0]['target_id']);

          // Get it's slideshow.
          $variables['family_product_slideshow'] = \Drupal::entityTypeManager()
            ->getViewBuilder('taxonomy_term')
            ->view($parent_family, 'products_slideshow');
        }
      }

      // Get recipe video.
      if ($node->hasField('field_media_video')) {
        $recipe_video_fid = $node->get('field_media_video')->getValue();

        if (!empty($recipe_video_fid)) {
          $recipe_video_fid = $recipe_video_fid[0]['target_id'];

          if (!empty($recipe_video_fid)) {
            /** @var \Drupal\Core\Render\RendererInterface $renderer */
            $renderer = \Drupal::service('renderer');

            /** @var \Drupal\media\Entity\Media $recipe_video */
            $recipe_video = Media::load($recipe_video_fid);

            // Body of modal.
            $modal_body = [];
            $viewBuilder = \Drupal::entityTypeManager()->getViewBuilder('node');

            if ($recipe_video->hasField('field_media_brainsonic')) {
              $url = $recipe_video->get('field_media_brainsonic');

              if (!empty($url)) {
                $modal_body[] = $viewBuilder->viewField($url, 'default');
              }
            }

            if ($recipe_video->hasField('field_transcript')) {
              $transcription = $recipe_video->get('field_transcript');

              if (!empty($transcription)) {
                $modal_body[] = $viewBuilder->viewField($transcription, 'default');
              }
            }

            $modal_id = 'modal-media-brainsonic-' . $recipe_video->id();

            $settings_video = [
              'selector' => '#' . $modal_id,
              'video_content' => $renderer->render($modal_body),
            ];

            $variables['media_modal'] = [
              '#theme' => 'bootstrap_modal',
              '#id' => $modal_id,
              '#size' => 'modal-lg',
              '#title' => $recipe_video->label(),
              '#attached' => [
                'library' => [
                  'pomona_media_brainsonic/popin_loading',
                ],
              ],
            ];
            $variables['#attached']['drupalSettings']['pomona_media_brainsonic']['popin_loading']['media_' . $recipe_video->id()] = $settings_video;
            $variables['#attached']['drupalSettings']['pomona_media_brainsonic']['popin_to_trigger'] = $settings_video['selector'];
          }
        }
      }

      // Visual slider.
      $setting = [];
      $setting['selector'] = '#slider-product-' . $node->id();
      $setting['prev'] = '#slick-prev-product-' . $node->id();
      $setting['next'] = '#slick-next-product-' . $node->id();
      $setting['nbitems'] = 1;
      $setting['nbscroll'] = 1;
      $setting['nbitemsmobile'] = 1;
      $setting['nbscrollmobile'] = 1;

      if ((!empty($node->get('field_media')->getValue())) &&
        (!empty($node->get('field_media_secondary')->getValue()))) {
        $setting['dots'] = TRUE;
      }
      else {
        $setting['dots'] = FALSE;
      }

      $setting['infinite'] = TRUE;
      $setting['autoplay'] = TRUE;

      // Add custom settings for the JS.
      $variables['#attached']['drupalSettings']['slideshow']['node_' . $node->id()] = $setting;
      $variables['#attached']['library'][] = 'pomona_base_theme/slideshow';
      $variables['#attached']['library'][] = 'pomona_sticky/sticky';
    }

    // Get product card url.
    if ($node->hasField('field_technical_sheet')) {
      $product_card = $node->get('field_technical_sheet')->getValue();

      if (!empty($product_card[0]['target_id'])) {

        // Load the Media first.
        $product_card_pdf = Media::load($product_card[0]['target_id']);
        if ($product_card_pdf != NULL && !$product_card_pdf->get('field_media_file_private')->isEmpty()) {
          $file_media = $product_card_pdf->get('field_media_file_private')->getValue();
        }

        if (!empty($file_media)) {
          $file_media_id = $file_media[0]['target_id'];

          // Then the file and get the url.
          if ($file_media_id != NULL) {
            $file = File::load($file_media_id);
            $file_path = file_create_url($file->getFileUri());
            $variables['product_card_url'] = $file_path;
          }
        }
      }
    }

    // Get Json / affichageWeb.
    if ($node->hasField('field_json')) {
      $json_text = $node->get('field_json')->getValue();

      if (!empty($json_text)) {
        $json_text = $json_text[0]['value'];

        $json = Json::decode($json_text);
        $json_base = $json['result'][0];
      }
      // Get marque Commerciale  affichage Web.
      if (!empty($json_base['marqueCommerciale']['affichageWeb'])) {
        $variables['affichageWeb'] = $json_base['marqueCommerciale']['affichageWeb'];
      }
    }
    // Get brand.
    if ($node->hasField('field_brand')
      && !empty($variables['affichageWeb'])
      && $variables['affichageWeb']) {
      $brand = $node->get('field_brand')->referencedEntities();

      if (!empty($brand)) {
        $brand = $brand[0];

        if ($brand->hasField('field_media')) {
          /** @var \Drupal\media\Entity\Media[] $brand_media */
          $brand_media = $brand->get('field_media')
            ->referencedEntities();

          if (!empty($brand_media)) {
            $brand_media = $brand_media[0];

            if ($brand_media->hasField('field_media_image')) {
              /** @var \Drupal\file\Entity\File[] $image_files */
              $image_files = $brand_media->get('field_media_image')
                ->referencedEntities();
              if (!empty($image_files)) {
                $image_files = $image_files[0];
                $variables['logo_brand_url'] = file_create_url($image_files->getFileUri());
              }
            }
          }
        }
        elseif ($brand->hasField('field_media_push')) {
          /** @var \Drupal\media\Entity\Media[] $brand_media */
          $brand_media = $brand->get('field_media_push')
            ->referencedEntities();

          if (!empty($brand_media)) {
            $brand_media = $brand_media[0];

            if ($brand_media->hasField('field_media_image')) {
              /** @var \Drupal\file\Entity\File[] $image_files */
              $image_files = $brand_media->get('field_media_image')
                ->referencedEntities();
              if (!empty($image_files)) {
                $image_files = $image_files[0];
                $variables['logo_brand_url'] = file_create_url($image_files->getFileUri());
              }
            }
          }
        }
      }
    }

    // Get region.
    if ($node->hasField('field_area')) {
      $area_tid = $node->get('field_area')->getValue();

      if (!empty($area_tid)) {
        $area_tid = $area_tid[0]['target_id'];

        $area = Term::load($area_tid);

        if ($area) {
          $variables['area_label'] = \Drupal::entityTypeManager()
            ->getViewBuilder('taxonomy_term')
            ->view($area, 'token');
        }
      }
    }

    // Get label.
    if ($node->hasField('field_product_label')) {
      $labels = $node->get('field_product_label')->referencedEntities();

      $variables['logo_label_urls'] = [];
      foreach ($labels as $label) {

        if ($label->hasField('field_label_visual')) {
          /** @var \Drupal\media\Entity\Media[] $label_media */
          $label_media = $label->get('field_label_visual')
            ->referencedEntities();

          if (!empty($label_media)) {
            $label_media = $label_media[0];

            if ($label_media->hasField('field_media_image')) {
              /** @var \Drupal\file\Entity\File[] $image_files */
              $image_files = $label_media->get('field_media_image')
                ->referencedEntities();

              if (!empty($image_files)) {
                $image_files = $image_files[0];
                $variables['logo_label_urls'][] = file_create_url($image_files->getFileUri());
              }
            }
          }
        }
      }
    }

    // In favorite products page in customer space.
    if (\Drupal::request()->get('_route') == 'pomona_customer_space.favorites_products') {
      $variables['in_favorites'] = TRUE;
    }
    else {
      $variables['in_favorites'] = FALSE;
    }

    // Add product code into Twig variables.
    $variables['pomona_id'] = '';
    if ($node->hasField('field_product_code')) {
      $pomona_id = $node->get('field_product_code')->getValue();
      if (!empty($pomona_id)) {
        $variables['pomona_id'] = $pomona_id[0]['value'];
      }
    }
  }

  // Check if product is new.
  if ($node->hasField('field_new')) {
    $is_new = $node->get('field_new')->getValue();

    if (!empty($is_new)) {
      $variables['is_new'] = $is_new[0]['value'] ? TRUE : FALSE;
    }
  }

  // Get the last advice who use the actual product.
  $advice_nids = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->getQuery()
    ->condition('field_contents_related.entity.nid', $node->id())
    ->condition('type', 'advice')
    ->range(0, 1)
    ->sort('changed', 'desc')
    ->condition('status', 1)
    ->execute();
  $variables['advice'] = [];
  if (!empty($advice_nids)) {
    $advice_nid = array_shift($advice_nids);
    $advice = Node::load($advice_nid);
    $variables['advice'][] = \Drupal::entityTypeManager()
      ->getViewBuilder('node')
      ->view($advice, 'teaser_list');
  }

  $variables['#attached']['library'][] = 'pomona_media_brainsonic/redirect_video';

