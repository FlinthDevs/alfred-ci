<?php

namespace Drupal\vdg_block_contact\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Title block.
 *
 * @ExtraFieldDisplay(
 *   id = "title_contact",
 *   label = @Translation("Title contact (VDG)"),
 *   bundles = {
 *     "block_content.contacts",
 *   }
 * )
 */
class TitleContact extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  /**
   * The entity field helper service.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $entityFieldHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityFieldHelperInterface $entity_field_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityFieldHelper = $entity_field_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vdg_core.utility.entity_field_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $title = $this->entityFieldHelper->getvalue($entity, 'field_title');
    $service = $this->entityFieldHelper->getReferencedEntity($entity, 'field_service');

    $ret = [];
    if (!empty($title)) {
      $ret[] = $title;
    }

    $service_link = '';
    if (!is_null($service)) {
      $service_name = $this->entityFieldHelper->getvalue($service, 'field_name');
      if (!is_null($service_name)) {
        $ret[] = $service_name;
        $service_link = $this->entityFieldHelper->getLinkValue($service, 'field_url_description_page');
        if (!empty($service_link)) {
          $service_link = $service_link['uri'];
        }
      }
    }
    else {
      $entity_name_firstname = $this->entityFieldHelper->getvalue($entity, 'field_entity_name_firstname');
      if (!is_null($entity_name_firstname)) {
        $ret[] = $entity_name_firstname;
      }
    }

    $elements = [
      '#theme' => 'vdg_block_contact_title_name',
      '#title_name' => implode(' - ', $ret),
      '#link_uri' => $service_link,
    ];

    return $elements;
  }

}
