<?php

namespace Drupal\vdg_job_offer\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Job information block.
 *
 * @ExtraFieldDisplay(
 *   id = "job_information",
 *   label = @Translation("Job information (VDG)"),
 *   bundles = {
 *     "node.job_offers",
 *   }
 * )
 */
class JobInformation extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity field helper.
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
    $job_start = $this->entityFieldHelper->getvalue($entity, 'field_job_offer_start_date');
    $job_delay = $this->entityFieldHelper->getvalue($entity, 'field_job_offer_submission');
    $service = $this->entityFieldHelper->getvalue($entity, 'field_service');
    $build = [
      '#theme' => 'vdg_job_offer_information',
      '#job_start' => $job_start,
      '#job_delay' => $job_delay,
      '#service' => $service,
    ];
    return $build;
  }

}
