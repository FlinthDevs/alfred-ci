<?php

if (!empty($json['etapes'])) {
  $steps = [];
  foreach ($json['etapes'] as $step) {
    $step_code = $step['code'];

    // Init step if not already initialized.
    if (!isset($steps[$step_code])) {
      $steps[$step_code] = [
        'steps' => [],
      ];
    }

    // Title and descriptions (sub-step).
    if (isset($step['titre'])) {
      $steps[$step_code]['titre'] = $step['titre'];
    }
    elseif (isset($step['description'])) {
      $steps[$step_code]['steps'][] = $step['description'];
    }
  }

  foreach ($json['ingredients'] as $ingredient) {
    $step_code = $ingredient['code'];

    if (isset($ingredient['produit'])) {
      // Get nid of product by reference.
      $product_nids = $product_service->getProductByRef([$ingredient['produit']]);

      if (!empty($product_nids)) {
        $product_nid = array_shift($product_nids);
        $product_node = Node::load($product_nid);

        $steps[$step_code]['ingredients'][] = [
          'nid' => $product_nid,
          'ref' => $ingredient['produit'],
          'name' => $product_node->label(),
          'quantity' => deliceetcreation_recipe_get_recipe_ingredient_quantity($ingredient),
        ];
      }
    }
    // "Free" product.
    elseif (isset($ingredient['ingredientLibre'])) {
      $steps[$step_code]['ingredients'][] = [
        'ref' => NULL,
        'name' => $ingredient['ingredientLibre'],
        'quantity' => deliceetcreation_recipe_get_recipe_ingredient_quantity($ingredient),
      ];
    }
  }

  $variables['steps'] = $steps;
}
