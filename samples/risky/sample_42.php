<?php

if ($variables['field_name'] == 'field_category_value') {
  $config_name = 'layout_config';

  /** @var \Drupal\config_pages\Entity\ConfigPages $config */
  $config = config_pages_config($config_name);

  $url = '';
  if ($config != NULL) {
    if ($config->hasField('field_url_webform_contact')) {

      $url_webform_contact = $config->get('field_url_webform_contact');
      $url_webform_contact = $url_webform_contact->getValue();

      // If the background picture is set.
      if (!empty($url_webform_contact[0]['value'])) {
        $url = $url_webform_contact[0]['value'];
      }
    }
  }
  $queryParameter = $variables['element']['#object']->get('field_category_value')->value;
  $options = [
    'query' => [
      'categories' => $queryParameter,
    ],
    'attributes' => [
      'class' => [
        'contact-link',
      ],
    ],
  ];

  $variables['contact_link'] = Url::fromUri('base:' . $url, $options)->toString();
  unset($variables['items']);
}
