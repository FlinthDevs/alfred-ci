<?php

    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($entity);
    if ($entity->bundle() == 'cm_member') {
      $fields = [
        'field_cmmember_address',
        'field_mail',
      ];
    }
    else {
      $fields = [
        'field_phone',
        'field_fax',
        'field_cellphone',
        'field_email',
      ];
    }

    $route = [
      'contacts' => Url::fromRoute('vdg_block_contact.block_content.vcard', ['block_content' => $entity->id()]),
      'directory_detail' => Url::fromRoute('vdg_block_contact.node.vcard', ['node' => $entity->id()]),
      'cm_member' => Url::fromRoute('vdg_block_contact.node.vcard', ['node' => $entity->id()]),
    ];

    $display = FALSE;
    foreach ($fields as $field) {
      if ($entity->hasField($field) && !$entity->get($field)->isEmpty()) {
        $display = TRUE;
      }
    }

    if (!$display) {
      $address = $this->entityFieldHelper->getReferencedEntity($entity, 'field_address');
      if (!is_null($address)) {
        $display = TRUE;
      }
    }

    $elements = [];
    if ($display) {
      $elements = [
        '#theme' => 'vdg_block_contact_vcard',
        '#url' => $route[$entity->bundle()],
      ];
    }
    $cache->applyTo($elements);

    return $elements;
