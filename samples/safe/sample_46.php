<?php

global $conf;
// Prevent caching.
assqa_core_set_drupal_cache(FALSE);

// Query string parameters.
$query_parameters = drupal_get_query_parameters();
$timezone = NULL;
if (isset($query_parameters['time_resolution']) && $query_parameters['time_resolution'] > 2) {
  $timezone = new DateTimeZone($conf['aasqa_air_data_timezone']);
}

// Recovers the min/max date
$min_date_timestamp = 0;
$max_date_timestamp = 0;

if(isset($query_parameters['min_date_timestamp'])) {
  $min_date_timestamp = $query_parameters['min_date_timestamp'];
}
else {
  if(isset($query_parameters['min_date'])){
    $min_date_timestamp = aasqa_core_strtotime('d/m/Y_H', $query_parameters['min_date'], $timezone);
  }
}
if(isset($query_parameters['max_date_timestamp'])) {
  $max_date_timestamp = $query_parameters['max_date_timestamp'];
}
else {
  if(isset($query_parameters['max_date'])){
    $max_date_timestamp = aasqa_core_strtotime('d/m/Y_H', $query_parameters['max_date'], $timezone);
  }
}
