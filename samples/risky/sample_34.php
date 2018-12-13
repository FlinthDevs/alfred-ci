<?php

if ($brand->hasField('field_media')) {
  /** @var \Drupal\media\Entity\Media[] $brand_media */
  $brand_media = $brand->get('field_media')
    ->referencedEntities();

  if (!empty($brand_media)) {
    $brand_media = $brand_media[0];

    if ($brand_media->hasField('field_media_image')) {
      /** @var \Drupal\file\Entity\File[] $image_files */
      $image_files = $brand_media->get('field_media_image')
        ->referencedEntities();
      if (!empty($image_files)) {
        $image_files = $image_files[0];
        $variables['logo_brand_url'] = file_create_url($image_files->getFileUri());
      }
    }
  }
}
elseif ($brand->hasField('field_media_push')) {
  /** @var \Drupal\media\Entity\Media[] $brand_media */
  $brand_media = $brand->get('field_media_push')
    ->referencedEntities();

  if (!empty($brand_media)) {
    $brand_media = $brand_media[0];

    if ($brand_media->hasField('field_media_image')) {
      /** @var \Drupal\file\Entity\File[] $image_files */
      $image_files = $brand_media->get('field_media_image')
        ->referencedEntities();
      if (!empty($image_files)) {
        $image_files = $image_files[0];
        $variables['logo_brand_url'] = file_create_url($image_files->getFileUri());
      }
    }
  }
}
