<?php

/**
 * @file
 * Contains hook implementations for vdg_dashboard module.
 */

use Drupal\views\ViewExecutable;

/**
 * Implements hook_page_attachments().
 */
function vdg_dashboard_page_attachments(array &$attachments) {
  if (\Drupal::currentUser()->hasPermission('access toolbar')) {
    $attachments['#attached']['library'][] = 'vdg_dashboard/toolbar-icon';
  }
}

/**
 * Implements hook_views_pre_view().
 *
 * Remove the field gid if the actual user doesn't have the listed role.
 */
function vdg_dashboard_views_pre_view(ViewExecutable $view, $display_id, array &$args) {
  $views_id_list = [
    'dashboard_content_types'
  ];

  if (in_array($view->id(), $views_id_list)) {
    $user_roles = \Drupal::currentUser()->getRoles();
    if (empty(array_intersect(['infocom', 'administrator_infocom', 'administrator'], $user_roles))) {
      // Remove the GID field.
      $view->removeHandler($display_id, 'field', 'gid');
    }
  }
}

/**
 * Implements hook_views_pre_build().
 *
 * Remove the filter gid if the actual user doesn't have the listed role.
 */
function vdg_dashboard_views_pre_build(ViewExecutable $view) {
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
}
