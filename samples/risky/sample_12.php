<?php

$content = [];
    $cache = new CacheableMetadata();
    $current_node = $this->currentRequest->attributes->get('node');

    // Case: Previews page.
    if (empty($current_node)) {
      $current_node = $this->currentRequest->attributes->get('node_preview');
    }

    if (is_object($current_node) && $current_node instanceof NodeInterface && in_array($current_node->getType(), ['place_page', 'section_page'])) {
      $media = $this->contentEntityHelper->getReferencedEntity($current_node, 'field_cover');
      $cache->addCacheContexts(['url.path']);
      $cache->addCacheableDependency($current_node);

      if (!empty($media)) {
        /** @var \Drupal\media\Entity\Media $media */
        $cache->addCacheableDependency($media);

        /** @var \Drupal\file\Entity\File $image */
        $image = $this->contentEntityHelper->getReferencedEntity($media, 'field_media_image');
        if (!empty($image)) {
          $cache->addCacheableDependency($image);
          $image_url = file_url_transform_relative($image->url());

          // Get credit information.
          $credits = $this->contentEntityHelper->getvalue($media, 'field_media_credits');

          // Convert the credit into link if a URL is provided.
          if (UrlHelper::isValid($credits, TRUE)) {
            $credits_host = parse_url($credits, PHP_URL_HOST);
            $credits = Link::fromTextAndUrl($credits_host, Url::fromUri($credits, ['attributes' => ['class' => ['lien']]]))->toString();
          }

          // Get alt text.
          $alt_text = '';
          $field_media_image = $media->get('field_media_image');

          if (!empty($field_media_image)) {
            $field_media_image = $field_media_image[0];
            $alt_text = $field_media_image->get('alt')->getString();
          }

          $content = [
            '#theme' => 'vdg_cover_image_block',
            '#image_url' => $image_url,
            '#image_alt' => $alt_text,
            '#details' => [
              'image_name' => $this->contentEntityHelper->getvalue($media, 'field_media_title'),
              'copyright' => $this->contentEntityHelper->getvalue($media, 'field_media_copyright'),
              'credits' => $credits,
            ],
            '#attached' => ['library' => ['vdg_core/display_credits']],
          ];
        }
      }
    }
    elseif (is_object($current_node) && $current_node instanceof NodeInterface && $current_node->getType() == 'information_folder') {
      $media = $this->contentEntityHelper->getReferencedEntities($current_node, 'field_cover');

      if (!empty($media)) {
        /** @var \Drupal\media\Entity\Media $media */
        $media = $media[0];
        $cache->addCacheableDependency($media);

        /** @var \Drupal\file\Entity\File $image */
        $image = $this->contentEntityHelper->getReferencedEntity($media, 'field_media_image');
        if (!empty($image)) {
          $image_url = file_url_transform_relative($image->url());
          $cache->addCacheableDependency($image);

          $content = [
            '#theme' => 'image',
            '#uri' => $image_url,
            '#attributes' => [
              'class' => [
                'image',
              ],
            ],
          ];
        }
      }
    }
    $cache->applyTo($content);

    return $content;

