<?php

namespace Drupal\vdg_block_epublication\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Publication link.
 *
 * @ExtraFieldDisplay(
 *   id = "publication_link",
 *   label = @Translation("Publication link (VDG)"),
 *   bundles = {
 *     "block_content.electronics_publications",
 *   }
 * )
 */
class PublicationLink extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {
  use StringTranslationTrait;

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
    $elements = [];
    $document = $this->entityFieldHelper->getReferencedEntity($entity, 'field_document');
    if (!is_null($document)) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->entityFieldHelper->getReferencedEntity($document, 'field_media_file');
      $link = $this->entityFieldHelper->getlinkvalue($document, 'field_media_url');
      $title = '';
      $url = '';

      if (!is_null($file)) {
        $url = Url::fromUri(file_create_url($file->getFileUri()));
        if (!empty($link)) {
          $title = $link['title'];
        }
      }
      elseif (!empty($link)) {
        $url = Url::fromUri($link['uri']);
        $title = $link['title'];
      }
      if (!empty($title) && !empty($url)) {
        $elements = [
          '#type' => 'link',
          '#title' => $this->t('Consult the electronic publication @title', ['@title' => $title]),
          '#url' => $url,
          '#attributes' => [
            'title' => $title,
            'class' => 'vdg-external-link gras sous-ligne',
            'target' => '_blank',
          ],
        ];
        $elements['#attached']['library'][] = 'vdg_block_epublication/epublication';
      }
    }
    return $elements;
  }

}
