<?php

$tabs = [];
    $catalogs_nid_by_segmentation = [];
    $catalogs_by_segmentation = [];
    $catalog_types_by_segmentation = [];
    $segmentation_tids = [];
    $user_logged = FALSE;
    $exclude_catalog_type = [];
    // Recovers segmentation terms.
    $segmentation_term_tids = $this->getSegmentation();

    // Recovers catalogs by segmentation.
    if (!empty($segmentation_term_tids)) {
      // @codingStandardsIgnoreStart
      /** @var \Drupal\taxonomy\Entity\Term[] $segmentations */
      $segmentations = Term::loadMultiple($segmentation_term_tids);
      // @codingStandardsIgnoreEnd

      // If the user is logged, display the tabs in the good order.
      if ($this->currentUser->isAuthenticated()) {
        $user_logged = TRUE;
      }

      foreach ($segmentations as $tid => $segmentation) {
        // Get icon sprite name.
        $icon_ref = $tid;
        $segmentation_tids[$tid] = $tid;
        if ($segmentation->hasField('field_icon_segmentation')) {
          $icon_value = $segmentation->get('field_icon_segmentation')
            ->getValue();
          if (!empty($icon_value)) {
            $icon_ref = $icon_value[0]['value'];
            $segmentation_tids[$tid] = $icon_value[0]['value'];
          }
        }

        // Get labels of tabs.
        $tabs[$icon_ref]['title_light'] = $segmentation->label();
        if ($segmentation->hasField('field_bold_title')) {
          $bold_title = $segmentation->get('field_bold_title')->getValue();
          if (!empty($bold_title)) {
            $tabs[$icon_ref]['title_bold'] = $bold_title[0]['value'];
          }
        }

        // Recovers catalog types by segmentation.
        $catalog_types_tids = $this->getCatalogTypeBySegmentationTid($tid);
        if (!empty($catalog_types_tids)) {
          // @codingStandardsIgnoreStart
          /** @var \Drupal\taxonomy\Entity\Term[] $types */
          $types = Term::loadMultiple($catalog_types_tids);
          // @codingStandardsIgnoreEnd

          foreach ($types as $tid_type => $type) {
            $has_restricted_access = TRUE;
            /** @var \Drupal\taxonomy\Entity\Term $type */
            if ($type->hasField('field_logged_customer_access')) {
              $has_restricted_access = $type->get('field_logged_customer_access')
                ->getValue();

              if (!empty($has_restricted_access) && $has_restricted_access[0]['value'] == '1') {
                $has_restricted_access = FALSE;
                $exclude_catalog_type[] = $tid_type;
              }
            }
            $catalog_types_by_segmentation[$icon_ref][$tid_type] = [
              'name' => $type->label(),
              'anonymous_access' => $has_restricted_access,
            ];
          }
        }
        // Get catalogs by activity.
        $catalogs_nid_by_segmentation[$icon_ref] = $this->getCatalogs([$tid], NULL, $user_logged ? [] : $exclude_catalog_type, TRUE);
      }
    }
    // Recovers teaser_vertical view of catalogs.
    foreach ($catalogs_nid_by_segmentation as $key => $catalogs) {
      $catalogs_by_segmentation[$key] = $this->getCatalogsInViewMode($catalogs, 'teaser_vertical');
    }

    $user_segmentation = $this->SSOManager->getUserSegmentation();
    if (!empty($segmentations) && count($segmentations) == 2) {
      $segmentation_tab_2 = end($segmentations);
      $segmentation_tab_2_id = $segmentation_tab_2->id();
    }

    $tab_1_first = TRUE;
    $tab_2_first = FALSE;
    if (count($user_segmentation) == 1) {
      if (isset($segmentation_tab_2_id)) {
        if ($user_segmentation[0] == $segmentation_tab_2_id) {
          $tab_1_first = FALSE;
          $tab_2_first = TRUE;
        }
      }
    }

    return [
      'segmentation_tab_hidden' => FALSE,
      'segmentation' => $tabs,
      'segmentation_selector' => $segmentation_tids,
      'filters' => $catalog_types_by_segmentation,
      'catalogs_by_segmentation' => $catalogs_by_segmentation,
      'tab_1_first' => $tab_1_first,
      'tab_2_first' => $tab_2_first,
    ];

