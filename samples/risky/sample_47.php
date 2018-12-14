<?php

global $base_path, $theme;
$region_coordinates = aasqa_air_pollen_get_coordinates();
$map_points = array();
$pollen_indices = aasqa_air_pollen_get_pollen_indices();

// Extract data to build map points.
$zones = $pollen_wrapper->field_indice_areas->value();
foreach ($zones as $zone) {
  $zone_wrapper = entity_metadata_wrapper('field_collection_item', $zone);
  $area = $zone_wrapper->field_indice_area->value();
  $indice = $zone_wrapper->field_indice->value();
  if(empty($indice)) {
    $indice = 'undefined';
  }
  $taxons = $zone_wrapper->field_taxons->value();

  $area_wrapper = entity_metadata_wrapper('taxonomy_term', $area);
  $geodata = $area_wrapper->field_geofield->value();

  $map_points[] = array(
    'class' => $pollen_indices[$indice]['class'],
    'latitude'  => $geodata['lat'],
    'longitude' => $geodata['lon'],
    'picto'     => $base_path . $pollen_indices[$indice]['path'],
    'width'     => 35,
    'height'    => 35,
    'tooltip'   =>
      '<p>' . t('Ville :') . ' ' . $area_wrapper->name->value() . '</p>' .
      '<p>' . t('Risque :') . ' ' . $pollen_indices[$indice]['label'] . '</p>' .
      '<p>' . aasqa_air_pollen_get_taxons_list($taxons) . '</p>',
  );
}
