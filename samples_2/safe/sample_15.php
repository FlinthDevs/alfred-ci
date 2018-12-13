<?php

 $job_start = $this->entityFieldHelper->getvalue($entity, 'field_job_offer_start_date');
    $job_delay = $this->entityFieldHelper->getvalue($entity, 'field_job_offer_submission');
    $service = $this->entityFieldHelper->getvalue($entity, 'field_service');
    $build = [
      '#theme' => 'vdg_job_offer_information',
      '#job_start' => $job_start,
      '#job_delay' => $job_delay,
      '#service' => $service,
    ];
    return $build;

