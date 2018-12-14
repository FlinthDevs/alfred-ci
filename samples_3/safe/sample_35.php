<?php

$node = $variables['node'];

if ($node->bundle() == 'trademark') {
  // Recover the web site url of provider.
  if ($node->hasField('field_link')) {
    $link_site = $node->get('field_link')->getValue();

    if (!empty($link_site)) {
      $variables['web_site_link'] = $link_site[0]['uri'];
    }
  }

  // Check display link products page.
  if ($node->hasField('field_link_to_products')) {
    $has_link_products = $node->get('field_link_to_products')->getValue();

    if (!empty($has_link_products)) {
      $has_link_products = $has_link_products[0]['value'];

      if ($has_link_products) {
        $options = $node->get('field_brand_code')->getValue() ? ['query' => ['marques' => [$node->get('field_brand_code')->getValue()[0]['value']]]] : [];
        $url = Url::fromRoute('pomona_product.product_list', [], $options);

        $variables['link_products'] = $url->toString();
      }
    }
  }
}
