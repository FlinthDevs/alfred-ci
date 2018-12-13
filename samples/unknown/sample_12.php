<?php

namespace Drupal\vdg_block_contact\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Title block.
 *
 * @ExtraFieldDisplay(
 *   id = "vcard_contact",
 *   label = @Translation("vCard contact (VDG)"),
 *   bundles = {
 *     "block_content.contacts",
 *     "node.directory_detail",
 *     "node.cm_member",
 *   }
 * )
 */
class VCardLink extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

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
    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($entity);
    if ($entity->bundle() == 'cm_member') {
      $fields = [
        'field_cmmember_address',
        'field_mail',
      ];
    }
    else {
      $fields = [
        'field_phone',
        'field_fax',
        'field_cellphone',
        'field_email',
      ];
    }

    $route = [
      'contacts' => Url::fromRoute('vdg_block_contact.block_content.vcard', ['block_content' => $entity->id()]),
      'directory_detail' => Url::fromRoute('vdg_block_contact.node.vcard', ['node' => $entity->id()]),
      'cm_member' => Url::fromRoute('vdg_block_contact.node.vcard', ['node' => $entity->id()]),
    ];

    $display = FALSE;
    foreach ($fields as $field) {
      if ($entity->hasField($field) && !$entity->get($field)->isEmpty()) {
        $display = TRUE;
      }
    }

    if (!$display) {
      $address = $this->entityFieldHelper->getReferencedEntity($entity, 'field_address');
      if (!is_null($address)) {
        $display = TRUE;
      }
    }

    $elements = [];
    if ($display) {
      $elements = [
        '#theme' => 'vdg_block_contact_vcard',
        '#url' => $route[$entity->bundle()],
      ];
    }
    $cache->applyTo($elements);

    return $elements;
  }

}
