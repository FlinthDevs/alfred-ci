<?php

if (isset($result['taxonomy_term'])) {
  $tids = array_keys($result['taxonomy_term']);
  $pollen_indice_area_tid = array_shift($tids);
  $pollen_indice_area = taxonomy_term_load($pollen_indice_area_tid);

  // Load pollen from indice area.
  $pollen = aasqa_air_pollen_get_pollen_from_pollen_indice_area($pollen_indice_area, TRUE);
  if ($pollen) {
    $pollen_wrapper = entity_metadata_wrapper('node', $pollen);

    $pollen_interval = $pollen_wrapper->field_date_interval->value();
    if ($pollen_interval['value2'] > strtotime('today')) {

      $field_collection_items = $pollen_wrapper->field_indice_areas->value();

      foreach ($field_collection_items as $field_collection_item) {
        $indice = NULL;
        $field_collection_item_wrapper = entity_metadata_wrapper('field_collection_item', $field_collection_item);
        if ($field_collection_item_wrapper->field_indice_area->value()->tid == $pollen_indice_area->tid) {
          $indice = $field_collection_item_wrapper->field_indice->value();
          if(empty($indice)) {
            $indice = 'undefined';
          }
          $pollen_indices = aasqa_air_pollen_get_pollen_indices();
          if($indice || $indice === '0'){
            $risk_level_picto = theme('image', array('path' => $pollen_indices[$indice]['path'], 'attributes' => array('width' => 120, 'height' => 120)));
            $risk_level_label = '<span>' . $pollen_indices[$indice]['label'] . '</span>';
          }
          break;
        }
      }
    }
  }
}
