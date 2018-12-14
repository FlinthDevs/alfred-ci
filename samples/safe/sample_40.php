<?php

$sso_user_data = $this->getSsoUserData($username, $admin_pomona_digital_token);

if (empty($sso_user_data)) {
  $return_code = SSOManagerInterface::SSO_ACCOUNT_DOES_NOT_EXIST;
}
// Check if the account is temporary.
elseif ($sso_user_data['pomonaStatut'][0] != 'Permanent') {
  $return_code = SSOManagerInterface::SSO_ACCOUNT_TEMPORARY;
}
elseif ($sso_user_data['pomonaReprise'][0]) {
  $return_code = SSOManagerInterface::SSO_ACCOUNT_IN_REPRISE;
}
elseif (!$this->isCustomer($sso_user_data)) {
  $return_code = SSOManagerInterface::SSO_ACCOUNT_EXISTS;
}
else {
  // Check if the customer is this website branch customer.
  $pomona_codes_client = $sso_user_data['pomonaCodeClient'];
  $valid_grace_code_client = '';

  foreach ($pomona_codes_client as $pomona_code_client) {
    $grace_data = $this->getGraceData($pomona_code_client);

    if (!empty($grace_data)) {
      $valid_grace_code_client = $pomona_code_client;
      break;
    }
  }
  if ($valid_grace_code_client == '') {
    $return_code = SSOManagerInterface::SSO_ACCOUNT_NOT_BRANCH_CUSTOMER;
  }
  else {
    $return_code = SSOManagerInterface::SSO_ACCOUNT_EXISTS;
  }
}
