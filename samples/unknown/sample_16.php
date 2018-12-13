<?php

namespace Drupal\vdg_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\vdg_main_menu\Service\MainMenuThemeHelper;
use Drupal\vdg_main_menu\Service\MainMenuActualitiesHelper;
use Drupal\vdg_main_menu\Service\MainMenuAuthoritiesHelper;
use Drupal\vdg_main_menu\Service\MainMenuStepsHelper;
use Drupal\vdg_main_menu\Service\MainMenuGenevaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays main menu of website.
 *
 * @Block(
 *  id = "vdg_main_menu_block",
 *  admin_label = "Main Menu block",
 * )
 */
class VdgMainMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Helper for "Theme" submenu.
   *
   * @var \Drupal\vdg_main_menu\Service\MainMenuThemeHelper
   */
  protected $mainMenuThemeHelper;

  /**
   * Helper for "Actualities" submenu.
   *
   * @var \Drupal\vdg_main_menu\Service\MainMenuActualitiesHelper
   */
  protected $mainMenuActualitiesHelper;

  /**
   * Helper for "Authorities" submenu.
   *
   * @var \Drupal\vdg_main_menu\Service\MainMenuAuthoritiesHelper
   */
  protected $mainMenuAuthoritiesHelper;

  /**
   * Helper for "Administrative Steps" submenu.
   *
   * @var \Drupal\vdg_main_menu\Service\MainMenuStepsHelper
   */
  protected $mainMenuStepsHelper;

  /**
   * Helper for "Geneva" submenu.
   *
   * @var \Drupal\vdg_main_menu\Service\MainMenuGenevaHelper
   */
  protected $mainMenuGenevaHelper;

  /**
   * Constructs of VdgMainMenuBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\vdg_main_menu\Service\MainMenuThemeHelper $mainMenuThemeHelper
   *   The helper for "Theme" submenu.
   * @param \Drupal\vdg_main_menu\Service\MainMenuActualitiesHelper $mainMenuActualitiesHelper
   *   The helper for "Actualities" submenu.
   * @param \Drupal\vdg_main_menu\Service\MainMenuAuthoritiesHelper $mainMenuAuthoritiesHelper
   *   The helper for "Authorities" submenu.
   * @param \Drupal\vdg_main_menu\Service\MainMenuStepsHelper $mainMenuStepsHelper
   *   The helper for "Administrative steps" submenu.
   * @param \Drupal\vdg_main_menu\Service\MainMenuGenevaHelper $mainMenuGenevaHelper
   *   The helper for "What to do in Geneva?" submenu.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MainMenuThemeHelper $mainMenuThemeHelper,
    MainMenuActualitiesHelper $mainMenuActualitiesHelper,
    MainMenuAuthoritiesHelper $mainMenuAuthoritiesHelper,
    MainMenuStepsHelper $mainMenuStepsHelper,
    MainMenuGenevaHelper $mainMenuGenevaHelper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mainMenuThemeHelper = $mainMenuThemeHelper;
    $this->mainMenuActualitiesHelper = $mainMenuActualitiesHelper;
    $this->mainMenuAuthoritiesHelper = $mainMenuAuthoritiesHelper;
    $this->mainMenuStepsHelper = $mainMenuStepsHelper;
    $this->mainMenuGenevaHelper = $mainMenuGenevaHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vdg_main_menu.main_menu_theme'),
      $container->get('vdg_main_menu.main_menu_actualities'),
      $container->get('vdg_main_menu.main_menu_authorities'),
      $container->get('vdg_main_menu.main_menu_steps'),
      $container->get('vdg_main_menu.main_menu_geneva')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
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
  }

}
