
<?php

 if (strpos($form_id, 'fivestar_field_vote__vote_fivestar_votingapi_fivestar_form') !== FALSE) {
    $form['#attached']['library'][] = 'vdg_voting/vdg_voting';
    $form['#attributes']['class'][] = 'notes';
    $form['#attributes']['class'][] = 'classique';
  }

