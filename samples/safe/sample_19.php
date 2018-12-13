<?php

namespace Drupal\vdg_sidebar_first\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a parent link menu block in first column.
 *
 * @Block(
 *  id = "vdg_sidebar_parent_link_menu_block",
 *  admin_label = @Translation("Parent link menu - Sidebar first"),
 * )
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VdgParentLinkMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The list of bundles which have parent predefined.
   *
   * @var array BUNDLES
   */
  const BUNDLES = [
    'news' => 'news',
    'event' => 'event',
    'place_page' => 'place',
    'directory_detail' => 'directory',
    'detail_approach' => 'approach',
    'job_offers' => 'job_offers',
    'commercial_spaces' => 'commercial',
  ];

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current request service.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The config page.
   *
   * @var \Drupal\config_pages\ConfigPagesInterface
   */
  protected $configPage;

  /**
   * The content entity helper.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $contentEntityHelper;

  /**
   * The current language.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $currentLanguage;

  /**
   * Constructs of VdgHeaderSearchBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\vdg_core\Utility\EntityFieldHelperInterface $content_entity
   *   The content entity helper.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    RequestStack $request_stack,
    EntityFieldHelperInterface $content_entity,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->contentEntityHelper = $content_entity;
    $this->configPage = config_pages_config('config_contents_lists');
    $this->currentLanguage = $language_manager->getCurrentLanguage();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('request_stack'),
      $container->get('vdg_core.utility.entity_field_helper'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = [];
    $cache = new CacheableMetadata();
    $cache->addCacheTags(['config_pages_list']);

    if ($this->configPage != NULL) {
      $cache->setCacheTags([]);
      $cache->addCacheableDependency($this->configPage);
      $cache->addCacheContexts(['url.path']);

      $current_node = $this->currentRequest->attributes->get('node');

      // Case: Previews page.
      if (empty($current_node)) {
        $current_node = $this->currentRequest->attributes->get('node_preview');
      }

      if (is_object($current_node) && $current_node instanceof NodeInterface) {
        $bundle = isset(self::BUNDLES[$current_node->bundle()]) ? self::BUNDLES[$current_node->bundle()] : '';
        $field_name = 'field_reference_link_' . $bundle;
        /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $reference_link */
        $reference_link = $this->contentEntityHelper->getReferencedEntity($this->configPage, $field_name);

        if (!empty($reference_link)) {
          /** @var \Drupal\Core\Url $url */
          $url = $reference_link->getUrlObject();
          $link_title = $reference_link->getTitle();

          $langcode = $this->currentLanguage->getId();
          if ($reference_link instanceof TranslatableInterface && $reference_link->hasTranslation($langcode)) {
            $translation = $reference_link->getTranslation($langcode);
            $link_title = $translation->label();
          }

          $content['menu'] = [
            '#theme' => 'vdg_sidebar_parent_link_menu_block',
            '#parent_link' => Link::fromTextAndUrl(
              new FormattableMarkup('<h6 class="titre">@text</h6>', ['@text' => $link_title]),
              $url
            ),
          ];
        }
      }
    }
    $cache->applyTo($content);

    return $content;
  }

}
