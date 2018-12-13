<?php

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
