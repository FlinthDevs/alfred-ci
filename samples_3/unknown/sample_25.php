<?php

$type = $this->entityFieldHelper->getvalue($entity, 'field_commercial_space_type');
    $address = $this->entityFieldHelper->getvalue($entity, 'field_address_full');
    $build = [
      '#theme' => 'vdg_commercial_space_chapo',
      '#type' => $type,
      '#address' => $address,
    ];
    return $build;


