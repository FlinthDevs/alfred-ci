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
    if ($thematic->hasField('field_type_of_animation')) {
      $animation_tid = $thematic->get('field_type_of_animation')->getValue();

      if (!empty($animation_tid)) {
        $animation_tid = $animation_tid[0]['target_id'];

        /** @var \Drupal\taxonomy\Entity\Term $animation */
        $animation = Term::load($animation_tid);

        // Get thematic recipe associed to animation.
        if ($animation->hasField('field_mapping_recipe_theme')) {
          $thematic_associed_tids = $animation->get('field_mapping_recipe_theme')
            ->getValue();

          if (!empty($thematic_associed_tids)) {
            foreach ($thematic_associed_tids as $thematic_associed_tid) {
              $thematic_recipe_tids[] = $thematic_associed_tid['target_id'];
            }
          }
        }
      }
    }
  }
}
