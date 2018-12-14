<?php

    $data = [];
    $fields = [
      '2#122' => 'autor',
      '2#120' => 'note',
      '2#118' => 'contact',
      '2#116' => 'copyright',
      '2#115' => 'source',
      '2#110' => 'credit',
      '2#105' => 'title',
      '2#103' => 'reference',
      '2#101' => 'country',
      '2#100' => 'country_code',
      '2#095' => 'province',
      '2#092' => 'region',
      '2#090' => 'city',
      '2#085' => 'title_creator',
      '2#080' => 'creator',
      '2#070' => 'version',
      '2#075' => 'cycle',
      '2#005' => 'object name',
      '2#007' => 'status',
      '2#040' => 'instructions',
      '2#022' => 'identifier',
      '2#026' => 'location',
      '2#010' => 'priority',
      '2#065' => 'program',
      '2#015' => 'categories',
      '2#025' => 'keywords',

    ];

    $file = File::load($fid);
    if ('image/jpeg' == $file->getMimeType()) {
      $info = [];
      $size = getimagesize($file->getFileUri(), $info);
      if (!empty($size)) {
        if (isset($info['APP13'])) {
          if ($iptc = iptcparse($info['APP13'])) {
            foreach ($fields as $key => $data_field_name) {
              if (isset($iptc[$key][0])) {
                $data[$data_field_name] = self::checkUtf8($iptc[$key][0]);
              }
            }

            // 2#060 : creation hour.
            if (isset($iptc['2#060'][0])) {
              $hour = $iptc['2#060'][0];
              $data['created_hour'] = substr($hour, 0, 2) . ':' . substr($hour, 2, 2) . ':' . substr($hour, 4, 2);
            }

            // 2#055 : creation date.
            if (isset($iptc['2#055'][0])) {
              $date = $iptc['2#055'][0];
              $data['created'] = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
            }
          }
        }
      }
    }

    return $data;
