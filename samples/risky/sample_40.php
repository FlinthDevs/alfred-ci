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
  if (empty($this->getGraceData($sso_user_data['pomonaCodeClient'][0]))) {
    $return_code = SSOManagerInterface::SSO_ACCOUNT_NOT_BRANCH_CUSTOMER;
  }
  else {
    $return_code = SSOManagerInterface::SSO_ACCOUNT_EXISTS;
  }
}
