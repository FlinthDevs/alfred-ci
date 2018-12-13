<?php

    $title = $this->entityFieldHelper->getvalue($entity, 'field_title');
    $service = $this->entityFieldHelper->getReferencedEntity($entity, 'field_service');

    $ret = [];
    if (!empty($title)) {
      $ret[] = $title;
    }

    $service_link = '';
    if (!is_null($service)) {
      $service_name = $this->entityFieldHelper->getvalue($service, 'field_name');
      if (!is_null($service_name)) {
        $ret[] = $service_name;
        $service_link = $this->entityFieldHelper->getLinkValue($service, 'field_url_description_page');
        if (!empty($service_link)) {
          $service_link = $service_link['uri'];
        }
      }
    }
    else {
      $entity_name_firstname = $this->entityFieldHelper->getvalue($entity, 'field_entity_name_firstname');
      if (!is_null($entity_name_firstname)) {
        $ret[] = $entity_name_firstname;
      }
    }

    $elements = [
      '#theme' => 'vdg_block_contact_title_name',
      '#title_name' => implode(' - ', $ret),
      '#link_uri' => $service_link,
    ];

    return $elements;
