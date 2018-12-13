<?php
if (\Drupal::currentUser()->hasPermission('preview_website')
    && (\Drupal::service('router.admin_context')->isAdminRoute() == FALSE)) {

    $items['user']['tray']['segmentation_update'] = [
      '#theme' => 'links__toolbar_user',
      '#links' => [
        [
          'title' => t('Website preview'),
          'url' => Url::fromRoute('<current>'),
          'attributes' => [
            'data-toggle' => 'modal',
            'data-target' => '#modal-segmentation',
            'data-remote' => 'false',
            'id' => 'segmentation-update',
          ],
        ],
      ],
      '#attributes' => [
        'class' => ['toolbar-menu'],
      ],
    ];
  }
  return $items;
