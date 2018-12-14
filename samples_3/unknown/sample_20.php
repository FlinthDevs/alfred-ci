<?php

    $breadcrumb = new Breadcrumb();
    $links = [];

    $breadcrumb->addCacheContexts(['url.path.parent']);

    if ($this->pathMatcher->isFrontPage()) {
      return $breadcrumb;
    }

    $exclude = [];
    $front = $this->config->get('page.front');
    $exclude[$front] = TRUE;
    $exclude['/user'] = TRUE;

    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    $page_title = $this->titleResolver->getTitle($this->request, $route_match->getRouteObject());

    if (!empty($page_title)) {
      $links[] = Link::fromTextAndUrl($page_title, new Url('<none>'));
    }

    return $breadcrumb->setLinks($links);
