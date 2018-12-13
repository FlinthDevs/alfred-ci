<?php

namespace Drupal\vdg_block_gallery\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\image\Entity\ImageStyle;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_revisions_gallery_view",
 *   label = @Translation("Rendered entity Gallery [VDG]"),
 *   description = @Translation("Display the referenced entities rendered by entity_view() Gallery [VDG]."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class EntityReferenceRevisionsGalleryFormatter extends EntityReferenceRevisionsEntityFormatter implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field helper.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $entityFieldHelper;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The plugin implementation definition.
   * @param \Drupal\vdg_core\Utility\EntityFieldHelperInterface $entity_field_helper
   *   The content entity helper.
   *
   * @SuppressWarnings(PHPMD.ExcessiveParameterList)
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityDisplayRepositoryInterface $entity_display_repository, EntityTypeManagerInterface $entity_type_manager, EntityFieldHelperInterface $entity_field_helper) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $logger_factory, $entity_display_repository);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldHelper = $entity_field_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('entity_display.repository'),
      $container->get('entity_type.manager'),
      $container->get('vdg_core.utility.entity_field_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $view_mode = $this->getSetting('view_mode');
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
      $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()->getId());

      $elements['#total'] = count($items);
      if ($delta == 3) {
        $elements[$delta]['#more'] = '+' . (count($items) - 4);
      }

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += ['resource' => $entity->toUrl()->toString()];
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $elements['#object'];
    $setting = [];
    $setting['items'] = [];
    $gallery_caption = $this->entityFieldHelper->getvalue($block, 'field_default_caption');
    $gallery_copyright = $this->entityFieldHelper->getvalue($block, 'field_default_copyright');

    foreach ($elements as $key => $gallery_item) {
      if (is_int($key)) {

        /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
        $paragraph = $gallery_item['#paragraph'];
        $picture_url = '';
        $picture_caption = $this->entityFieldHelper->getvalue($paragraph, 'field_caption');
        $picture_copyright = $this->entityFieldHelper->getvalue($paragraph, 'field_copyright');

        /** @var \Drupal\file\Entity\File $gallery_picture */
        $gallery_picture = $this->entityFieldHelper->getReferencedEntity($paragraph, 'field_picture_of_the_gallery');

        if (!empty($gallery_picture)) {
          $picture_url = file_url_transform_relative(ImageStyle::load('large')
            ->buildUrl($gallery_picture->getFileUri()));
        }

        $setting['items'][] = [
          'src' => $picture_url,
          'w' => 900,
          'h' => 600,
          'title' => (!empty($picture_caption) ? $picture_caption : $gallery_caption),
          'author' => (!empty($picture_copyright) ? $picture_copyright : $gallery_copyright),
        ];
      }
    }

    $elements['#attached']['drupalSettings']['gallery']['field_' . $elements['#bundle'] . '_' . $block->id()] = $setting;
    $elements['#attached']['library'][] = 'vdg_block_gallery/gallery';

    return $elements;
  }

}
