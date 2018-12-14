<?php

// In any case redirect ot the implantations page.
$url = Url::fromRoute('view.implantation_list.implantation_page');
$form_state->setRedirectUrl($url);

try {
  $http_client_options = [
    'base_uri' => Settings::get('pomona_grace_url'),
    'headers' => [
      'Accept' => 'application/json',
    ],
  ];
  $postal_code = $form_state->getValue('postal_code');
  $path = '/grace/referentiel/kilivki/' . Settings::get('pomona_grace_kilivki_branch_code') . '/' . $postal_code . '/FR';

  $result = $this->httpClient->get($path, $http_client_options);
  $response_content = $result->getBody()->getContents();
  $reponse = Json::decode($response_content);
  $city_count = count($reponse);
  // Check multiple or single city.
  if ($city_count == 1) {
    $implantation_id = $reponse[0]['id'];
    // Check if node exists.
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'implantation')
      ->condition('field_implantation_code', $implantation_id)
      ->sort('created', 'DESC')
      ->range(0, 1);
    $nids = $query->execute();

    // Redirect to the implantation.
    if (!empty($nids)) {
      $nid = array_shift($nids);
      $options = ['absolute' => TRUE];
      $url = Url::fromRoute('entity.node.canonical', ['node' => $nid], $options);
      $form_state->setRedirectUrl($url);
    }
  }
  else {
    $route_parameters = [
      'cp' => $postal_code,
    ];
    $url = Url::fromRoute('view.implantation_list.implantation_page', $route_parameters);
    $form_state->setRedirectUrl($url);
  }
}
catch (ClientException $e) {
  $response_content = $e->getResponse()->getBody()->getContents();
  $reponse = Json::decode($response_content);

  $error = $reponse['error'];
  $route_parameters = [
    'error' => $error,
  ];

  $url = Url::fromRoute('view.implantation_list.implantation_page', $route_parameters);
  $form_state->setRedirectUrl($url);
}
