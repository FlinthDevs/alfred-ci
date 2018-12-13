<?php

$replacements = [];

  if (($type == 'node')) {
    foreach ($tokens as $name => $original) {
      $clang = explode(':', $name);

      if (isset($clang[0]) && $clang[0] == 'trim') {
        // Skip if not a number.
        if (!isset($clang[1]) || !is_numeric($clang[1])) {
          continue;
        }

        // We need remaining data.
        if (!isset($clang[2])) {
          continue;
        }

        // Get trim length.
        $length = $clang[1];
        // Get field value to trim.
        $field = $clang[2];
        $node = reset($data);
        $subtitle = \Drupal::service('vdg_core.utility.entity_field_helper')->getvalue($node, $field);
        $subtitle = strip_tags($subtitle);

        $replacements[$original] = Unicode::truncate($subtitle, $length, FALSE, TRUE);
      }

      if (isset($clang[0]) && $clang[0] == 'meta_image') {
        $node = reset($data);

        // Default image is the logo of theme.
        $theme_name = \Drupal::service('vdg_core.service.theme_helper')->getCurrentThemeUri();
        $image_url = \Drupal::service('theme.initialization')->getActiveThemeByName($theme_name)->getLogo();

        /** @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface $entity_field_helper */
        $entity_field_helper = \Drupal::service('vdg_core.utility.entity_field_helper');
        $teaser_media = $entity_field_helper->getReferencedEntity($node, 'field_teaser_image');

        if (!empty($teaser_media)) {
          /** @var \Drupal\file\Entity\File $image_file */
          $image_file = $entity_field_helper->getReferencedEntity($teaser_media, 'field_media_image');

          if (!empty($image_file)) {
            $image_url = $image_file->getFileUri();
          }
        }

        $image_style_url = ImageStyle::load('1_70_646x379')->buildUrl($image_url);
        $replacements[$original] = file_url_transform_relative($image_style_url);
      }
    }
  }
  elseif ($type == 'current-page') {
    foreach ($tokens as $name => $original) {
      $clang = explode(':', $name);

      if (isset($clang[0]) && $clang[0] == 'view_description') {
        $views_route = [
          'view.directory_page.directory_search' => 'directory',
          'view.news_list.news' => 'news',
          'view.epublications.page_1' => 'epublication',
        ];

        /** @var \Symfony\Component\HttpFoundation\Request $current_request */
        $current_request = \Drupal::service('request_stack')
          ->getCurrentRequest();
        $view_id = $current_request->attributes->get('view_id');

        if (!empty($view_id)) {
          $route = $current_request->attributes->get('_route');

          if (in_array($route, array_keys($views_route))) {
            // Get config page.
            $config_page = config_pages_config('config_contents_lists');

            if ($config_page !== NULL) {
              $field_name = 'field_' . $views_route[$route] . '_list_subtitle';

              $replacements[$original] = Drupal::service('vdg_core.utility.entity_field_helper')
                ->getvalue($config_page, $field_name);
            }
          }
        }
      }
    }
  }

  return $replacements;
