<?php

$offset = 0;
$viewRecipes = [];
$viewFilters = [];
$filters = [];
$facets = [];
$current_filters = [];
$parameters = [];
$spell_check = [];
$is_mobile = $this->mobileDetect->isMobile();

// Recover GET parameters.
$params = $this->currentRequest->query->all();
unset($params['op']);

if (!empty($params)) {
  // Get page param and change offset.
  $page_num = $this->parameterHelper->generateFiltersAndFacets($params, $filters, $facets, TRUE);

  if ($page_num && !$is_mobile) {
    $offset = max(0, ($page_num * SearchRecipeHelperInterface::RECIPE_NUMBER_BY_PAGE));
  }
}
unset($params['page']);

// Get form.
$form_recipe = $this->formBuilder->getForm($this->recipeFilter, $params);

// ELK recipe request.
$recipes_list = $this->recipeSearch->searchRecipe($filters, $offset);

if ($recipes_list['total'] != 0) {
  $recipes_nids = $this->recipeHelper->getRecipeByRef($recipes_list['results']);

  if ($recipes_nids != NULL) {
    $node_storage = $this->entityTypeManager->getStorage('node');
    /** @var \Drupal\node\Entity\Node[] $recipes */
    $recipes = $node_storage->loadMultiple($recipes_nids);

    $recipes = $this->recipeHelper->sortRecipesByResultPosition($recipes, $recipes_list['results']);

    foreach ($recipes as $recipe) {
      $viewRecipes[] = $this->entityTypeManager()
        ->getViewBuilder('node')
        ->view($recipe, self::RECIPE_LIST_VIEW_MODE);
    }
  }
}
else {
  // ELK spell-check request.
  if (isset($params['nom'])) {
    $spell_check_suggestion = $this->spellCheckService->spellcheck($params['nom']);

    if (!empty($spell_check_suggestion)) {
      foreach ($spell_check_suggestion as $suggest) {
        $spell_check[] = Link::createFromRoute($suggest, 'pomona_recipe.recipe_list', [], [
          'query' => [
            'q' => $suggest,
          ],
          'attributes' => [
            'class' => [
              'text-underline',
              'text-blue',
            ],
          ],
        ]);
      }
    }
  }
}

if (isset($params['q']) && $params['q'] == 'open') {
  $viewFilters['#open'] = TRUE;
}
