<?php

    $elements = [];
    $document = $this->entityFieldHelper->getReferencedEntity($entity, 'field_document');
    if (!is_null($document)) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->entityFieldHelper->getReferencedEntity($document, 'field_media_file');
      $link = $this->entityFieldHelper->getlinkvalue($document, 'field_media_url');
      $title = '';
      $url = '';

      if (!is_null($file)) {
        $url = Url::fromUri(file_create_url($file->getFileUri()));
        if (!empty($link)) {
          $title = $link['title'];
        }
      }
      elseif (!empty($link)) {
        $url = Url::fromUri($link['uri']);
        $title = $link['title'];
      }
      if (!empty($title) && !empty($url)) {
        $elements = [
          '#type' => 'link',
          '#title' => $this->t('Consult the electronic publication @title', ['@title' => $title]),
          '#url' => $url,
          '#attributes' => [
            'title' => $title,
            'class' => 'vdg-external-link gras sous-ligne',
            'target' => '_blank',
          ],
        ];
        $elements['#attached']['library'][] = 'vdg_block_epublication/epublication';
      }
    }
    return $elements;
