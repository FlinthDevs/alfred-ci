<?php

if ($form['#id'] == 'views-exposed-form-city-map-list-page-1') {
    if (array_key_exists('field_district', $form)) {
      $form['field_district']['#options']['All'] = t('tous les quartiers');
    }

    $form_additional_attributes = [
      '#attributes' => [
        'class' => [
          'recherche-globale__formulaire',
        ],
      ],
    ];
    $form = array_merge_recursive($form, $form_additional_attributes);

    $search_additional_attributes = [
      '#attributes' => [
        'class' => [
          'controle__input',
        ],
      ],
    ];
    $form['search'] = array_merge_recursive($form['search'], $search_additional_attributes);

    $actions_additional_attributes = [
      '#attributes' => [
        'class' => [
          'primaire',
          'controle__bouton',
        ],
      ],
    ];
    $form['actions'] = array_merge_recursive($form['actions'], $actions_additional_attributes);
  }
