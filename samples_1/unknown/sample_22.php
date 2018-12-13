<?php

namespace Drupal\vdg_city_map\Plugin\search_api\processor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Utility\FieldsHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;

/**
 * Provides POI is linked processor.
 *
 * @SearchApiProcessor(
 *   id = "vdg_city_map_poi_is_linked",
 *   label = @Translation("POI is linked"),
 *   description = @Translation("Checks if a POI is linked to a place page node."),
 *   stages = {
 *     "add_properties" = 0,
 *   }
 * )
 */
class PoiIsLinkedProcessor extends ProcessorPluginBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Search API field helper.
   *
   * @var \Drupal\search_api\Utility\FieldsHelperInterface
   */
  protected $fieldHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    FieldsHelperInterface $field_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldHelper = $field_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('search_api.fields_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('POI is linked to place page node'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['vdg_city_map_poi_is_linked'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $item->getOriginalObject()->getValue();

    if ($entity->getEntityTypeId() == 'node') {
      if ($entity->bundle() == 'poi') {
        // A way to load $field.
        foreach ($this->fieldHelper->filterForPropertyPath($item->getFields(), NULL, 'vdg_city_map_poi_is_linked') as $field) {
          $node_place_ids = $this->entityTypeManager->getStorage('node')->getQuery()
            ->condition('type', 'place_page')
            ->condition('field_poi', $entity->id())
            ->condition('status', NodeInterface::PUBLISHED)
            ->range(0, 1)
            ->execute();

          if (!empty($node_place_ids)) {
            $field->addValue('1');
          }
          else {
            $field->addValue('0');
          }
        }
      }
    }
  }

}
