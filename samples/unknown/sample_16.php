<?php

    $content = [
      '#theme' => 'vdg_main_menu_menu',
      '#menu1' => $this->mainMenuThemeHelper->getMenuContent(),
      '#menu2' => $this->mainMenuActualitiesHelper->getMenuContent(),
      '#menu3' => $this->mainMenuAuthoritiesHelper->getMenuContent(),
      '#menu4' => $this->mainMenuStepsHelper->getMenuContent(),
      '#menu5' => $this->mainMenuGenevaHelper->getMenuContent(),
      '#attached' => ['library' => ['vdg_main_menu/navigation']],
    ];
    return $content;
