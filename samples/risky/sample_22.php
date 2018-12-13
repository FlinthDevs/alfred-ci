<?php

$content = [];

    $current_node = $this->currentRequest->attributes->get('node');

    if (is_object($current_node) && $current_node instanceof NodeInterface) {
      if ($current_node->getType() == 'information_folder') {
        // Get cartridge color classes.
        $classes = '';
        $select_key = $this->contentEntityHelper->getvalue($current_node, 'field_cartridge_color');

        if (!empty($select_key)) {
          $classes = str_replace('_', ' ', $select_key);
        }

        $content['title_block'] = [
          '#theme' => 'vdg_content_information_folder_title_block',
          '#cartridge_color_classes' => $classes,
          '#title' => $this->informationFolderMenuHelper->getInformationFolderTitle(),
        ];
      }
    }

    return $content;
