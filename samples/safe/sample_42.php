<?php

if ($variables['field_name'] == 'field_sticky_block') {
  $config_name = 'layout_config';
  $options = [
    'attributes' => [
      'class' => [
        'contact-link',
      ],
    ],
  ];

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

  /** @var \Drupal\block_content\Entity\BlockContent $block */
  $block = $variables['items'][0]['content']['#block_content'];

  if ($block->hasField('field_category_value')) {
    $queryParameter = $block->get('field_category_value')->getValue();

    if ($queryParameter) {
      $options['query'] = [
        'categories' => $queryParameter[0]['value'],
      ];
    }
  }

  $variables['items'][0]['content']['#contact_link'] = Url::fromUri('base:' . $url, $options)->toString();
}
