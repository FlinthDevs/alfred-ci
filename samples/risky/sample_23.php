<?php

$is_first_level = $this->informationFolderMenuHelper->isFirstLevelOfInformationFolder();

    $elements = [
      '#type' => 'inline_template',
      '#template' => '<h2 class="titre">{{ title }}</h2>',
      '#context' => ['title' => $is_first_level ? $this->t('Home') : $entity->label()],
    ];
    
    return $elements;
  }


