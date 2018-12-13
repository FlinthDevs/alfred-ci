<?php

namespace Drupal\vdg_content_directory_detail\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a directory detail title block.
 *
 * @Block(
 *  id = "vdg_directory_detail_title_block",
 *  admin_label = @Translation("Directory detail - Title"),
 * )
 */
class VdgContentDirectoryDetailTitleBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RequestStack $request_stack,
    EntityFieldHelperInterface $content_entity
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->contentEntityHelper = $content_entity;
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
      $container->get('vdg_core.utility.entity_field_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() : array {
    $content = [];
    $cache = new CacheableMetadata();

    $current_node = $this->currentRequest->attributes->get('node');

    if (is_object($current_node) && $current_node instanceof NodeInterface) {
      if ($current_node->getType() == 'directory_detail') {
        $cache->addCacheableDependency($current_node);

        $content['title_block'] = [
          '#theme' => 'vdg_content_directory_detail_title_block',
          '#name' => strtoupper($this->contentEntityHelper->getvalue($current_node, 'field_name')),
          '#first_name' => $this->contentEntityHelper->getvalue($current_node, 'field_firstname'),
        ];
      }
    }
    $cache->applyTo($content);

    return $content;
  }

}
