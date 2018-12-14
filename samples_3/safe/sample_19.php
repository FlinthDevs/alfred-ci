<?php

$content = [];
    $cache = new CacheableMetadata();
    $cache->addCacheTags(['config_pages_list']);

    if ($this->configPage != NULL) {
      $cache->setCacheTags([]);
      $cache->addCacheableDependency($this->configPage);
      $cache->addCacheContexts(['url.path']);

      $current_node = $this->currentRequest->attributes->get('node');

      // Case: Previews page.
      if (empty($current_node)) {
        $current_node = $this->currentRequest->attributes->get('node_preview');
      }

      if (is_object($current_node) && $current_node instanceof NodeInterface) {
        $bundle = isset(self::BUNDLES[$current_node->bundle()]) ? self::BUNDLES[$current_node->bundle()] : '';
        $field_name = 'field_reference_link_' . $bundle;
        /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $reference_link */
        $reference_link = $this->contentEntityHelper->getReferencedEntity($this->configPage, $field_name);

        if (!empty($reference_link)) {
          /** @var \Drupal\Core\Url $url */
          $url = $reference_link->getUrlObject();
          $link_title = $reference_link->getTitle();

          $langcode = $this->currentLanguage->getId();
          if ($reference_link instanceof TranslatableInterface && $reference_link->hasTranslation($langcode)) {
            $translation = $reference_link->getTranslation($langcode);
            $link_title = $translation->label();
          }

          $content['menu'] = [
            '#theme' => 'vdg_sidebar_parent_link_menu_block',
            '#parent_link' => Link::fromTextAndUrl(
              new FormattableMarkup('<h6 class="titre">@text</h6>', ['@text' => $link_title]),
              $url
            ),
          ];
        }
      }
    }
    $cache->applyTo($content);

    return $content;

