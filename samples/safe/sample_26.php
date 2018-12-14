<?php

if (isset($change_password_token['token']) && !empty($change_password_token['token'])) {
  $pomona_codes_client = $user_data['pomonaCodeClient'];
  $valid_grace_code_client = '';

  foreach ($pomona_codes_client as $pomona_code_client) {
    $grace_data = $this->SSOManager->getGraceData($pomona_code_client);

    if (!empty($grace_data)) {
      $valid_grace_code_client = $pomona_code_client;
      break;
    }
  }

  // Send email.
  $this->mailManager->mail('pomona_sso', 'reprise_email', $user_mail, $this->languageManager->getCurrentLanguage(), [
    'civility' => isset($user_data['title'][0]) ? $user_data['title'][0] : '',
    'lastname' => isset($user_data['sn'][0]) ? $user_data['sn'][0] : '',
    'change_password_token' => $change_password_token['token'],
    'email' => $user_mail,
    'customer_code' => $valid_grace_code_client,
    'establishment' => isset($grace_data['libelle']) ? $grace_data['libelle'] : '',
  ]);

  // Redirect to specific page configured.
  if ($this->configPage) {
    if ($this->configPage->hasField('field_reprise_page_url')) {
      $reprise_page_tid = $this->configPage->get('field_reprise_page_url')->getValue();

      if ($reprise_page_tid) {
        $reprise_page = $this->entityTypeManager->getStorage('node')->load($reprise_page_tid[0]['target_id']);
      }
    }
  }

  $form_state->setRedirectUrl(isset($reprise_page) ?
    $reprise_page->toUrl()
      ->setRouteParameter('isReprise', 'true')
      ->setRouteParameter('token', base64_encode($user_mail)) :
    Url::fromRoute('<front>')
      ->setRouteParameter('isReprise', 'true')
      ->setRouteParameter('token', base64_encode($user_mail))
  );
}
else {
  $form_state->setErrorByName('mail', $this->t('An error occurred when trying to connect you. Please try again later or contact us.'));
}
