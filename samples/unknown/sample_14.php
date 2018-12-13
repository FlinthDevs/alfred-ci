<?php

namespace Drupal\vdg_block_epublication\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Document description.
 *
 * @ExtraFieldDisplay(
 *   id = "document_description",
 *   label = @Translation("Document description (VDG)"),
 *   bundles = {
 *     "block_content.electronics_publications",
 *   }
 * )
 */
class DocumentDescription extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * The entity field helper service.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $entityFieldHelper;

  /**
   * Delimit the character string to replace when building the view.
   */
  const START = -6;

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
    $document = $this->entityFieldHelper->getReferencedEntity($entity, 'field_document');
    $elements = [];

    if (is_null($document)) {
      return $elements;
    }

    $description = $this->entityFieldHelper->getvalue($entity, 'field_description');
    $number_pages = $this->entityFieldHelper->getvalue($document, 'field_number_pages');
    $elements = [];

    if (!empty($description) && !empty($number_pages)) {
      $number_pages = intval($number_pages);
      $number_pages = $this->formatPlural($number_pages, '<strong>1 page.</strong></p>', '<strong>@count pages.</strong></p>', ['@count' => $number_pages]);
      $description = substr_replace($description, $number_pages, self::START);
      $elements = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $description,
      ];
    }
    return $elements;
  }

}
