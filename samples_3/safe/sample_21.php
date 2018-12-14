<?php
$content = [];
    $cache = new CacheableMetadata();
    $cache->addCacheContexts(['url.path']);

    $current_node = $this->currentRequest->attributes->get('node');

    // Case: Previews page.
    if (empty($current_node)) {
      $current_node = $this->currentRequest->attributes->get('node_preview');
    }

    if (is_object($current_node) && $current_node instanceof NodeInterface) {
      if ($current_node->getType() == 'directory_detail') {
        $cache->addCacheableDependency($current_node);

        $content['title_block'] = [
          '#theme' => 'vdg_content_directory_detail_title_block',
          '#name' => strtoupper($this->contentEntityHelper->getvalue($current_node, 'field_name')),
          '#first_name' => $this->contentEntityHelper->getvalue($current_node, 'field_firstname'),
        ];
      }
    }
    $cache->applyTo($content);

    return $content;

