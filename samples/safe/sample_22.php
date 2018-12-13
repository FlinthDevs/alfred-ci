<?php

namespace Drupal\vdg_content_information_folder\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Drupal\vdg_sidebar_first\Service\InformationFolderMenuHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a information folder menu block in first column.
 *
 * @Block(
 *  id = "vdg_information_folder_title_block",
 *  admin_label = @Translation("Information folder - Title"),
 * )
 */
class VdgContentInformationFolderTilteBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current request service.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The content entity helper.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $contentEntityHelper;

  /**
   * The information folder menu helper.
   *
   * @var \Drupal\vdg_sidebar_first\Service\InformationFolderMenuHelperInterface
   */
  protected $informationFolderMenuHelper;

  /**
   * Constructs of VdgHeaderSearchBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\vdg_core\Utility\EntityFieldHelperInterface $content_entity
   *   The content entity helper.
   * @param \Drupal\vdg_sidebar_first\Service\InformationFolderMenuHelperInterface $information_folder_menu
   *   The information folder menu helper.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RequestStack $request_stack,
    EntityFieldHelperInterface $content_entity,
    InformationFolderMenuHelperInterface $information_folder_menu
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->contentEntityHelper = $content_entity;
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
      $container->get('request_stack'),
      $container->get('vdg_core.utility.entity_field_helper'),
      $container->get('vdg_sidebar_first.service.information_folder_menu_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = [];

    $current_node = $this->currentRequest->attributes->get('node');

    if (is_object($current_node) && $current_node instanceof NodeInterface) {
      if ($current_node->getType() == 'information_folder') {
        // Get cartridge color classes.
        $classes = '';
        $select_key = $this->contentEntityHelper->getvalue($current_node, 'field_cartridge_color');

        if (!empty($select_key)) {
          $classes = str_replace('_', ' ', $select_key);
        }

        $content['title_block'] = [
          '#theme' => 'vdg_content_information_folder_title_block',
          '#cartridge_color_classes' => $classes,
          '#title' => $this->informationFolderMenuHelper->getInformationFolderTitle(),
        ];
      }
    }
    $this->informationFolderMenuHelper->getCache()->applyTo($content);

    return $content;
  }

}
