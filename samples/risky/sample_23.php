<?php

namespace Drupal\vdg_content_information_folder\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Drupal\vdg_sidebar_first\Service\InformationFolderMenuHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Information folder content title.
 *
 * @ExtraFieldDisplay(
 *   id = "information_folder_title",
 *   label = @Translation("Title page (VDG)"),
 *   bundles = {
 *     "node.information_folder",
 *   }
 * )
 */
class InformationFolderTitle extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {


  use StringTranslationTrait;

  /**
   * The entity field helper service.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $entityFieldHelper;

  /**
   * The information folder menu helper.
   *
   * @var \Drupal\vdg_sidebar_first\Service\InformationFolderMenuHelperInterface
   */
  protected $informationFolderMenuHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityFieldHelperInterface $entity_field_helper,
    InformationFolderMenuHelperInterface $information_folder_menu
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityFieldHelper = $entity_field_helper;
    $this->informationFolderMenuHelper = $information_folder_menu;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vdg_core.utility.entity_field_helper'),
      $container->get('vdg_sidebar_first.service.information_folder_menu_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $is_first_level = $this->informationFolderMenuHelper->isFirstLevelOfInformationFolder();

    $elements = [
      '#type' => 'inline_template',
      '#template' => '<h2 class="titre">{{ title }}</h2>',
      '#context' => ['title' => $is_first_level ? $this->t('Home') : $entity->label()],
    ];
    
    return $elements;
  }

}
