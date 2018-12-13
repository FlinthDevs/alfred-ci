<?php

    $document = $this->entityFieldHelper->getReferencedEntity($entity, 'field_document');
    $elements = [];

    if (is_null($document)) {
      return $elements;
    }

    $description = $this->entityFieldHelper->getvalue($entity, 'field_description');
    $number_pages = $this->entityFieldHelper->getvalue($document, 'field_number_pages');
    $elements = [];

    if (!empty($description) && !empty($number_pages)) {
      $number_pages = intval($number_pages);
      $number_pages = $this->formatPlural($number_pages, '<strong>1 page.</strong></p>', '<strong>@count pages.</strong></p>', ['@count' => $number_pages]);
      $description = substr_replace($description, $number_pages, self::START);
      $elements = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $description,
      ];
    }
    return $elements;
