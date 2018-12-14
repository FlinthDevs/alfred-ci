<?php

if ($this->configPage == NULL) {
  return NULL;
}
/** @var \Drupal\file\Entity\File $logo */
$logo = $this->entityFieldHelper->getReferencedEntity($this->configPage, 'field_map_logo_sitg');
if (!empty($logo)) {
  $logo = file_url_transform_relative($logo->url());
  $cache->addCacheableDependency($logo);
}
return $logo;
