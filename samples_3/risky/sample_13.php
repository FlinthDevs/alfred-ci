<?php

$views_id_list = [
    'dashboard_content_types'
  ];

  if (in_array($view->id(), $views_id_list)) {
    $user_roles = \Drupal::currentUser()->getRoles();
    if (empty(array_intersect(['infocom', 'administrator_infocom', 'administrator'], $user_roles))) {
      // Remove the GID filter.
      unset($view->filter['gid']);
    }
  }

