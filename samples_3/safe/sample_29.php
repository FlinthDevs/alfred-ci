<?php

if (\Drupal::currentUser()->hasPermission('preview_website')) {

    $items['user']['tray']['segmentation_update'] = [
      '#theme' => 'links__toolbar_user',
      '#links' => [
        [
          'title' => t('Website preview'),
          'url' => Url::fromRoute('pomona_preview.modal'),
          'attributes' => [
            'class' => 'use-ajax',
            'data-dialog-type' => 'modal',
          ],
        ],
      ],
      '#attributes' => [
        'class' => ['toolbar-menu'],
      ],
      '#attached' => [
        'library' => ['core/drupal.dialog.ajax'],
      ],
    ];
  }
  return $items;


