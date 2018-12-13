<?php

// Generate pagination.
pager_default_initialize($products_information['total'], SearchFamilyHelperInterface::SEARCH_SIZE_RESULT);

$results = [
  'total' => $products_information['total'],
  'pagination' => [
    '#type' => 'pomona_pager',
  ],
];

// Generate renderable array of products.
// Get product_nid by pim code.
$products_nids = $this->productHelper->getProductByRef($products_information['results']);

if ($products_nids != NULL) {
  // @codingStandardsIgnoreStart
  /** @var \Drupal\node\Entity\Node $products */
  $products = Node::loadMultiple($products_nids);
  // @codingStandardsIgnoreEnd
  $products = $this->productHelper->sortProductsByResultPosition($products, $products_information['results']);

  // Allow to render product with viewmode.
  $event = new ProductElkViewmodeEvent($products, $page_num);
  $event_dispatcher_service = \Drupal::service('event_dispatcher');
  $event_dispatcher_service->dispatch(ProductElkViewmodeEvent::EVENT_NAME, $event);
  $results = array_merge($results, $event->getGeneratedProduct());
}

return $results;
