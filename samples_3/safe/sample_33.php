<?php

$product_nids = [];
if (!empty($ref)) {
  $product_nids = $this->entityTypeManager
    ->getStorage('node')
    ->getQuery()
    ->condition('type', 'product')
    ->condition('field_product_code', $ref, 'IN')
    ->condition('status', 1)
    ->execute();
}
return $product_nids;
