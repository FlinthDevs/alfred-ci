<?php

$recipes = [];
$thematic_nid = NULL;
$thematic_recipe_tids = NULL;

if ($this->nodeService->getCurrentNodeBundle() == 'animation_thematic') {
  // Get MDD nid.
  $thematic_nid = $this->nodeService->getCurrentNodeId();

  $thematic = Node::load($thematic_nid);

  if (!empty($thematic)) {
    // Get animation type.
    if ($thematic->hasField('field_thematic_recipe_push')) {
      $thematic_recipe_tids = $thematic->get('field_thematic_recipe_push')
        ->getValue();

      if (!empty($thematic_recipe_tids)) {
        $thematic_recipe_tids = $thematic_recipe_tids[0]['target_id'];
      }
    }
  }
}
