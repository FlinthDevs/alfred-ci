<?php

$last_recipes = [];
$season_info = [];
$current_season = NULL;

// Get current season.
$current_season_tid = $this->getCurrentSeasonal();

if (!empty($current_season_tid)) {
  /** @var \Drupal\taxonomy\TermInterface[] $seasons */
  $seasons = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => 'season']);


  foreach ($seasons as $key => $season) {
    if ($season->id() == $current_season_tid) {
      $current_season = $season;
      unset($seasons[$key]);
      break;
    }
  }

  if (!is_null($current_season)) {
    $season_info = $this->entityTypeManager->getViewBuilder('taxonomy_term')
      ->view($current_season, 'default');
  }

  // Get three last recipes for current season.
  // ELK recipe request.
  $filters = [
    'saisons' => [
      'values' => [
        $season->label(),
      ],
    ],
  ];
  $must_nots = [];
  foreach ($seasons as $season) {
    $must_nots[] = [
      'term',
      [
        'field' => 'saisons.nom',
        'value' => $season->label(),
      ],
    ];
  }
  $recipes_list = $this->recipeSearch->searchRecipe($filters, 0, 3, $must_nots);

  if ($recipes_list['total'] != 0) {
    $recipes_nids = $this->recipeHelper->getRecipeByRef($recipes_list['results']);

    if (!empty($recipes_nids)) {
      // First recipe on "teaser big" view mode.
      $first_recipe = Node::load(array_pop($recipes_nids));

      $last_recipes['first'] = $this->entityTypeManager->getViewBuilder('node')
        ->view($first_recipe, 'teaser_big');

      // Two others on "teaser list" view mode.
      $other_recipes = Node::loadMultiple($recipes_nids);

      $last_recipes['others'] = $this->entityTypeManager->getViewBuilder('node')
        ->viewMultiple($other_recipes, 'teaser_list');
    }
  }
}

$title = '';
$filed_blade_title = $paragraph->get('field_blade_title')->getValue();
if (!empty($filed_blade_title)) {
  $title = $filed_blade_title[0]['value'];
}

return [
  '#theme' => 'seasonal_recipes_widget',
  '#title' => $title,
  '#last_recipes' => $last_recipes,
  '#season_info' => $season_info,
  '#season_name' => !is_null($current_season) ? $current_season->label() : '',
  '#attached' => [
    'library' => [
      'pomona_widget/seasonal_recipes_pomona_widget',
    ],
  ],
];
