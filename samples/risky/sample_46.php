<?php

// Prevent caching.
assqa_core_set_drupal_cache(FALSE);

// Query string parameters.
$query_parameters = drupal_get_query_parameters();

// Recovers the min/max date
$min_date_timestamp = 0;
$max_date_timestamp = 0;

if(isset($query_parameters['min_date_timestamp'])) {
  $min_date_timestamp = $query_parameters['min_date_timestamp'];
}
else {
  if(isset($query_parameters['min_date'])){
    // Converts the date into timestamp
    list($date, $hour) = explode('_', $query_parameters['min_date']);
    list($day, $month, $year) = explode ('/', $date);

    $min_date_timestamp = mktime($hour, 0, 0, $month, $day, $year);
  }
}
if(isset($query_parameters['max_date_timestamp'])) {
  $max_date_timestamp = $query_parameters['max_date_timestamp'];
}
else {
  if(isset($query_parameters['max_date'])){
    // Converts the date into timestamp
    list($date, $hour) = explode('_', $query_parameters['max_date']);
    list($day, $month, $year) = explode ('/', $date);

    $max_date_timestamp = mktime($hour, 0, 0, $month, $day, $year);
  }
}
