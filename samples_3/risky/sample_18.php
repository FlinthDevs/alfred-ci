<?php

parent::buildExposedForm($form, $form_state);

    if (
      !empty($this->options['expose']['identifier']) &&
      isset($this->options['type']) &&
      $this->options['type'] == 'select_year'
    ) {
      $default_value = $form[$this->options['expose']['identifier']]['#default_value'];
      $default_value_list = explode('-', ($default_value));
      $options = [];
      $current_year = date('Y');
      if ($this->options['expose']['identifier'] == 'end_value') {
        $default_value = $default_value_list[0] . '-' . $default_value_list[1] . '-' . $current_year;
      }

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
        $years[] = date("Y", $time);
      }
      $years = array_unique($years);

      foreach ($years as $year) {
        $options[$default_value_list[0] . '-' . $default_value_list[1] . '-' . (string) $year] = (string) $year;
      }

      $form[$this->options['expose']['identifier']] = [
        '#type' => 'select',
        '#title' => '',
        '#options' => $options,
        '#default_value' => $default_value,
      ];
    }

