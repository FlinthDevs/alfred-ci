<?php

if ($current_recipe->hasField($this->fieldName)) {
  $type_of_dish_tid = $current_recipe->get($this->fieldName)->getValue();

  if (!empty($type_of_dish_tid)) {
    $type_of_dish_tid = $type_of_dish_tid[0]['target_id'];

    // Go with.
    if ($this->HasToGoWith) {
      // @codingStandardsIgnoreStart
      /** @var \Drupal\taxonomy\Entity\Term $recipe */
      $current_type_of_dish = Term::load($type_of_dish_tid);
      // @codingStandardsIgnoreEnd

      if ($current_type_of_dish && $current_type_of_dish->hasField('field_other_type_of_dish')) {
        $type_of_dish_tids = $current_type_of_dish->get('field_other_type_of_dish')->getValue();

        if (!empty($type_of_dish_tids)) {
          foreach ($type_of_dish_tids as $tid) {
            $types_of_dish[] = $tid['target_id'];
          }
        }
      }
    }
    // Other ideas.
    else {
      /** @var \Drupal\taxonomy\Entity\Term $theme_term */
      $types_of_dish[] = $type_of_dish_tid;
    }
  }
  else {
    // Go with.
    if ($this->HasToGoWith) {
      if ($this->configPage->hasField($this->configPageField)) {
        $type_of_dish_tid = $this->configPage->get($this->configPageField)->getValue();

        if (!empty($type_of_dish_tid)) {
          $type_of_dish_tid = $type_of_dish_tid[0]['target_id'];
          // @codingStandardsIgnoreStart
          /** @var \Drupal\taxonomy\Entity\Term $recipe */
          $current_type_of_dish = Term::load($type_of_dish_tid);
          // @codingStandardsIgnoreEnd

          if ($current_type_of_dish && $current_type_of_dish->hasField('field_other_type_of_dish')) {
            $type_of_dish_tids = $current_type_of_dish->get('field_other_type_of_dish')
              ->getValue();

            if (!empty($type_of_dish_tids)) {
              foreach ($type_of_dish_tids as $tid) {
                $types_of_dish[] = $tid['target_id'];
              }
            }
          }
        }
      }
    }
    // Other ideas.
    else {
      if ($this->configPage->hasField($this->configPageField)) {
        $type_of_dish_tids = $this->configPage->get($this->configPageField)->getValue();

        if (!empty($type_of_dish_tids)) {
          foreach ($type_of_dish_tids as $tid) {
            /** @var \Drupal\taxonomy\Entity\Term $theme_term */
            $types_of_dish[] = $tid;
          }
        }
      }
    }
  }
}
