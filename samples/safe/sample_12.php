<?php

// In any case redirect to the implantations page.
$url = Url::fromRoute('view.implantation_list.implantation_page');
$form_state->setRedirectUrl($url);

try {
  $postal_code = $form_state->getValue('postal_code');
  $http_client_options = [
    'base_uri' => Settings::get('pomona_grace_url'),
    'headers' => [
      'Content-type' => 'application/json',
    ],
  ];
  $path = '/grace/referentiel/kilivki/' . Settings::get('pomona_grace_kilivki_branch_code') . '/' . $postal_code . '/FR';

  $result = $this->httpClient->get($path, $http_client_options);
  $response_content = $result->getBody()->getContents();
  $response_content = Json::decode($response_content);
  $city_count = count($response_content);
  // Check multiple or single city.
  if ($city_count == 1) {
    $nids = [];

    // Check if node exists.
    if (isset($response_content[0]['agences'][0]['code_agence'])) {
      $implantation_code = $response_content[0]['agences'][0]['code_agence'];

      $query = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'implantation')
        ->condition('field_implantation_code', $implantation_code)
        ->condition('status', NodeInterface::PUBLISHED)
        ->sort('created', 'DESC')
        ->range(0, 1);
      $nids = $query->execute();
    }

    // Redirect to the implantation.
    if (!empty($nids)) {
      $nid = array_shift($nids);
      $url = Url::fromRoute('entity.node.canonical', ['node' => $nid]);
      $form_state->setRedirectUrl($url);
    }
  }
  else {
    $url_options = [
      'query' => [
        'cp' => $postal_code,
      ],
    ];
    $url = Url::fromRoute('view.implantation_list.implantation_page', [], $url_options);
    $form_state->setRedirectUrl($url);
  }
}
catch (ClientException $e) {
  $response_content = $e->getResponse()->getBody()->getContents();
  $response_content = Json::decode($response_content);

  $error = $response_content['error'];
  $url_options = [
    'query' => [
      'error' => $error,
    ],
  ];
  $url = Url::fromRoute('view.implantation_list.implantation_page', [], $url_options);
  $form_state->setRedirectUrl($url);
}
