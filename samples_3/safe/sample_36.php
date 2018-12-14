<?php

$paragraph = $variables['paragraph'];

if (in_array($paragraph->bundle(), ['push_rc_rs'])) {
  $variables['logged'] = FALSE;
  $variables['tab_1_first'] = FALSE;
  $variables['tab_2_first'] = FALSE;
  // Get the background picture.
  if ($paragraph->hasField('field_media')) {
    $background_media = $paragraph->get('field_media')->getValue();

    // If the background picture is set.
    if (!empty($background_media[0]['target_id'])) {

      // Load the Media first.
      $picture_media = Media::load($background_media[0]['target_id']);

      // Then the file and get the url.
      $picture = ImageStyle::load('background_picture_l1280')->buildUrl(
        $picture_media->field_media_image->entity->getFileUri());
      $variables['background_picture'] = $picture;
    }
  }

  // If the user is logged, display the tabs with the good segmentation.
  /** @var \Drupal\pomona_sso\Service\SSOManagerInterface $sso_service */
  $sso_service = Drupal::service('pomona_sso.sso_manager');

  $user_segmentation = $sso_service->getUserSegmentation();
  $segmentations = $sso_service->getSegmentations();

  if ($paragraph->hasField('field_segmentation_tab_2')) {
    $segmentation_tab_2 = $paragraph->get('field_segmentation_tab_2')->getValue();
  }

  if (count($user_segmentation) == 1) {
    $variables['logged'] = TRUE;
    $variables['tab_1_first'] = TRUE;
    $variables['tab_2_first'] = FALSE;

    if (isset($segmentation_tab_2) && !empty($segmentation_tab_2[0]['target_id'])) {
      if ($user_segmentation[0] == $segmentation_tab_2[0]['target_id']) {
        $variables['tab_1_first'] = FALSE;
        $variables['tab_2_first'] = TRUE;
      }
    }
  }
  else {
    // The user hasn't segmentation or has more than 1 segmentation.
    $variables['tab_1_first'] = TRUE;
    $variables['tab_2_first'] = FALSE;

    if (isset($segmentations['default'])) {
      // If the segmentation_tab_2 is set.
      if (isset($segmentation_tab_2) && !empty($segmentation_tab_2[0]['target_id'])) {
        if ($segmentations['default'] == $segmentation_tab_2[0]['target_id']) {
          $variables['tab_1_first'] = FALSE;
          $variables['tab_2_first'] = TRUE;
        }
      }
    }
  }

  if (!isset($variables['#cache'])) {
    $variables['#cache'] = [
      'contexts' => ['user'],
    ];
  }
  elseif (!isset($variables['#cache']['contexts'])) {
    $variables['#cache']['contexts'] = ['user'];
  }
  elseif (isset($variables['#cache']['contexts'])) {
    $variables['#cache']['contexts'][] = 'user';
  }
  $variables['#attached']['library'][] = 'pomona_base_theme/tabs-visibility';


