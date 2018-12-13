<?php

$query = $this->entityTypeManager
  ->getStorage('node')
  ->getQuery()
  ->condition('type', 'product', '=');

if (is_array($ref)) {
  $query->condition('field_product_code', $ref, 'in');
}
else {
  $query->condition('field_product_code', $ref, '=');
}

$result = $query->condition('status', 1)
  ->execute();

return $result;
