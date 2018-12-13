<?php

namespace Drupal\vdg_search\Plugin\views\filter;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\search_api\Plugin\views\filter\SearchApiDate;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a filter for filtering on dates with a dropdown.
 */
class SearchApiDateDropdown extends SearchApiDate implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Connection $database
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildExtraOptionsForm($form, $form_state);

    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Selection type'),
      '#options' => [
        'select_year' => $this->t('Dropdown (year)'),
        'textfield' => $this->t('Textfield'),
      ],
      '#default_value' => $this->options['type'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['type'] = ['default' => 'textfield'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    parent::buildExposedForm($form, $form_state);
    if (
      !empty($this->options['expose']['identifier']) &&
      isset($this->options['type']) &&
      $this->options['type'] == 'select_year'
    ) {
      $sth = $this->database->select('media__field_media_publication_date', 'epublication_date')
        ->fields('epublication_date', ['field_media_publication_date_value'])
        ->condition('epublication_date.bundle', 'electronic_publication')
        ->distinct()
        ->orderBy('field_media_publication_date_value');

      $data = $sth->execute();
      $results = $data->fetchAll(\PDO::FETCH_OBJ);

      $years = [];
      foreach ($results as $date) {
        $time = strtotime($date->field_media_publication_date_value);
        $years[] = date('Y', $time);
      }
      $years = array_unique($years);

      foreach ($years as $year) {
        $options['01-01-' . (string) $year] = (string) $year;
      }
      $form[$this->options['expose']['identifier']]['min'] = [
        '#type' => 'select',
        '#title' => '',
        '#options' => $options,
      ];
    }
  }

}
