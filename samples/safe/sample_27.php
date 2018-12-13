<?php

$node = $variables['node'];

  if ($node->bundle() == 'new') {
    /** @var Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = \Drupal::service('date.formatter');
    $datestamp = $node->getCreatedTime();

    $variables['create_date_formated'] = $date_formatter->format($datestamp, 'actuality_date');

    // Recover url to press folder.
    if ($node->hasField('field_press_folder')) {
      $press_folder_mid = $node->get('field_press_folder')->getValue();

      if (!empty($press_folder_mid)) {

        /** @var \Drupal\file\Entity\File $press_folder_media */
        $press_folder_media = Media::load($press_folder_mid[0]['target_id']);
        $press_folder_fid = $press_folder_media->get('field_media_file')
          ->getValue();

        if (!empty($press_folder_fid)) {
          $press_folder_fid = $press_folder_fid[0]['target_id'];
          /** @var \Drupal\file\Entity\File $press_folder_file */
          $press_folder_file = File::load($press_folder_fid);

          if (!is_null($press_folder_file)) {
            $variables['press_folder_url'] = file_create_url($press_folder_file->getFileUri());
          }
        }
      }
    }
  }

