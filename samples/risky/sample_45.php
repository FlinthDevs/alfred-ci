<?php

if (!empty($json['etapes']) && !empty($json['ingredients'])) {
  $current_step = 0;
  $steps = [];
  foreach ($json['etapes'] as $step) {
    if (isset($step['code']) && $step['code'] >= $current_step && !empty($step['titre'])) {
      $current_step = $step['code'];
      $steps[$step['code']]['titre'] = $step['titre'];
      foreach ($json['ingredients'] as $ingredient) {
        if (isset($ingredient['code']) && $ingredient['code'] != $current_step) {
          continue;
        }
        else {

          if (isset($ingredient['produit'])) {
            // Get tip of product by reference.
            $product_nid = $product_service->getProductByRef([$ingredient['produit']]);

            if (!empty($product_nid)) {
              $product_nid = array_shift($product_nid);
              if (isset($ingredient['unite'])) {
                if (isset($ingredient['quantite'])) {
                  $quantity = $ingredient['quantite'] . " " . ($ingredient['quantite'] > 1 ? $ingredient['unite']['pluriel'] : $ingredient['unite']['designation']);
                }
                else {
                  $quantity = $ingredient['unite']['designation'];
                }
              }

              /** @var \Drupal\node\Entity\Node $product_node */
              $product_node = Node::load($product_nid);

              $steps[$step['code']]['ingredients'][] = [
                'nid' => $product_nid,
                'ref' => $ingredient['produit'],
                'name' => $product_node->getTitle(),
                'quantity' => $quantity,
              ];
            }
          }
          // "Free" product.
          elseif (isset($ingredient['ingredientLibre'])) {
            if (isset($ingredient['unite'])) {
              if (isset($ingredient['quantite'])) {
                $quantity = $ingredient['quantite'] . " " . ($ingredient['quantite'] > 1 ? $ingredient['unite']['pluriel'] : $ingredient['unite']['designation']);
              }
              else {
                $quantity = $ingredient['unite']['designation'];
              }
              $steps[$step['code']]['ingredients'][] = [
                'ref' => NULL,
                'name' => $ingredient['ingredientLibre'],
                'quantity' => $quantity,
              ];
            }
          }
        }
      }
    }
    else {
      if (isset($step['code'])) {
        $steps[$step['code']]['steps'][] = [
          'description' => $step['description'],
        ];
      }
    }
  }
  $variables['steps'] = $steps;
}
