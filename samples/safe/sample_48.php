<?php

// Option to override all previsions' comment with today's.
if (!$week && $display_today_comment) {

  $today_timestamp = $settings['daily']['displayed'];
  $yesterday_timestamp = strtotime('-1 day', $today_timestamp);
  $comment = '';
  // Use today's prevision comment for every raster shown.
  if (!empty($settings['daily']['raster'][$today_timestamp]['comment'])) {
    $comment = $settings['daily']['raster'][$today_timestamp]['comment'];
  }
  // Fallback to yesterday's prevision comment for every raster shown.
  // Only for AURA at this time.
  elseif (module_exists('airra')
    && !empty($settings['daily']['raster'][$yesterday_timestamp]['comment'])) {
    $comment = $settings['daily']['raster'][$yesterday_timestamp]['comment'];
  }

  if (!empty($comment)) {
    foreach ($dates_to_display as $key => $timestamp) {
      $settings['daily']['raster'][$timestamp]['comment'] = $comment;
    }
  }
}
