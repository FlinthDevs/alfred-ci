<?php

namespace Drupal\vdg_facets\Form;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contribute form.
 */
class VdgFacetWhenForm extends FormBase {

  use stringTranslationTrait;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * SearchFamilyHelper constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   */
  public function __construct(
    RequestStack $request_stack,
    DateFormatter $date_formatter
  ) {
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vdg_facet_when_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $view = Views::getView('diary_page');
    $default_values = $this->currentRequest->query->all();
    $radio_options = [
      0 => [
        'label' => 'Today (@result)',
        'display_id' => 'today',
      ],
      1 => [
        'label' => 'Tomorrow (@result)',
        'display_id' => 'tomorrow',
      ],
      2 => [
        'label' => 'This week-end (@result)',
        'display_id' => 'week_end',
      ],
    ];

    if (is_object($view)) {
      $options = [];

      foreach ($radio_options as $radio_option) {
        if ($result = $this->getCountResult($view, $radio_option['display_id']) > 0) {
          $options[$radio_option['display_id']] = $this->t($radio_option['label'], [
            '@result' => $result
          ]);
        }
      }

      $form['radio_options'] = [
        '#type' => 'radios',
        '#title' => '',
        '#default_value' => isset($default_values['when']) ? $default_values['when'] : 0,
        '#options' => $options,
      ];
    }

    $form['header'] = [
      '#title' => $this->t('When?'),
      '#first_element_facet' => array_shift($options),
    ];

    $form['dates'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'options__datepickers',
        ],
      ],
      '#prefix' => '<div class="options">',
      '#suffix' => '</div>',
    ];

    $form['dates']['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start'),
      '#default_value' => isset($default_values['when_start']) ? $default_values['when_start'] : '',
    ];

    $form['dates']['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End'),
      '#default_value' => isset($default_values['when_end']) ? $default_values['when_end'] : '',
    ];

    $form['#attached']['library'][] = 'vdg_facets/when_facet';

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#name' => '',
      '#attributes' => [
        'class' => [
          'hidden',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $date = $form_state->getValue('date');
    $end_date = $form_state->getValue('end_date');

    if (!empty($date) && !empty($end_date)) {
      $date = date_create($date);
      $date = date_timestamp_get($date);

      $end_date = date_create($end_date);
      $end_date = date_timestamp_get($end_date);

      if ($end_date < $date) {
        $form_state->setError(
          $form['dates']['end_date'],
          $this->t('The @title end date cannot be before the start date', [
            '@title' => $this->dateFormatter->format($end_date, 'custom', 'd/m/Y')
          ])
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $date_start = '';
    $date_end = '';
    $parameters = $this->currentRequest->query->all();

    // Radio buttons.
    $option_selected = $form_state->getValue('radio_options');
    if (!empty($option_selected)) {
      $parameters['when'] = $option_selected;

      switch ($option_selected) {
        case 'today':
          $date_start = $this->dateFormatter->format(strtotime('today'), 'custom', 'Y-m-d');
          $date_end = $this->dateFormatter->format(strtotime('today'), 'custom', 'Y-m-d');
          break;

        case 'tomorrow':
          $date_start = $this->dateFormatter->format(strtotime('tomorrow'), 'custom', 'Y-m-d');
          $date_end = $this->dateFormatter->format(strtotime('tomorrow'), 'custom', 'Y-m-d');
          break;

        case 'week_end':
          $date_start = $this->dateFormatter->format(strtotime('next Saturday'), 'custom', 'Y-m-d');
          $date_end = $this->dateFormatter->format(strtotime('next Sunday'), 'custom', 'Y-m-d');

          // Case : today is Saturday.
          if ($date_start > $date_end) {
            $date_start = $this->dateFormatter->format(strtotime('today'), 'custom', 'Y-m-d');
          }

          // Case : today is Sunday.
          if ($date_end == strtotime('+7 day')) {
            $date_start = $this->dateFormatter->format(strtotime('yesterday'), 'custom', 'Y-m-d');
            $date_end = $this->dateFormatter->format(strtotime('today'), 'custom', 'Y-m-d');
          }
          break;
      }

      if (!empty($date_start) && !empty($date_end)) {
        $parameters['when_start'] = $date_start;
        $parameters['when_end'] = $date_end;
      }
    }

    // Inputs date.
    $date = $form_state->getValue('date');
    $end_date = $form_state->getValue('end_date');

    if (!empty($date)) {
      $parameters['when_start'] = $date;

      if (!empty($date_start)) {
        unset($parameters['when']);
      }
    }

    if (!empty($end_date)) {
      $parameters['when_end'] = $end_date;

      if (!empty($date_start)) {
        unset($parameters['when']);
      }
    }

    $form_state->setRedirect('view.diary_page.event_search', [], [
      'query' => $parameters,
    ]);
  }

  /**
   * Count the number of result of display view.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view.
   * @param string $display_id
   *   The display view.
   *
   * @return int
   *   The number of result.
   */
  protected function getCountResult(ViewExecutable $view, string $display_id) : int {
    $view->setArguments([]);
    $view->setDisplay($display_id);
    $view->preExecute();
    $view->execute();

    return count($view->result);
  }

}

