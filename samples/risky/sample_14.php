<?php

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

