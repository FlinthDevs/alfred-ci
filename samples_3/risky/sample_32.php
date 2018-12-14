<?php

$current_nid = $this->nodeHelper->getCurrentNodeId();
$advices_views = [];

if (empty($current_nid)) {
  return [];
}

$node_storage = $this->entityTypeManager->getStorage('node');
/** @var \Drupal\node\Entity\Node $current_product */
$current_product = $node_storage->load($current_nid);

if (!empty($current_product)) {
  // Get themes of product.
  $themes = [];
  if ($current_product->hasField($this->fieldName)) {
    $themes_tid = $current_product->get($this->fieldName)->getValue();

    if (!empty($themes_tid)) {
      foreach ($themes_tid as $theme_tid) {
        /** @var \Drupal\taxonomy\Entity\Term $theme_term */
        $themes[] = $theme_tid['target_id'];
      }
    }
  }
  $advices = $this->productPush->getAdviceWithSameTheme($current_nid, $this->fieldName, $themes, 1);

  foreach ($advices as $advice) {
    $advices_views[] = $this->entityTypeManager->getViewBuilder('node')
      ->view($advice, 'teaser_list');
  }
}

return [
  '#theme' => 'product_advice',
  '#advices' => $advices_views,
];
