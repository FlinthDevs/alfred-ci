<?php

    $breadcrumb = new Breadcrumb();
    $links = [];

    // Add the url.path.parent cache context. This code ignores the last path
    // part so the result only depends on the path parents.
    $breadcrumb->addCacheContexts(['url.path.parent']);

    // Do not display a breadcrumb on the frontpage.
    if ($this->pathMatcher->isFrontPage()) {
      return $breadcrumb;
    }

    $exclude = [];
    // Don't show a link to the front-page path.
    $front = $this->config->get('page.front');
    $exclude[$front] = TRUE;
    $exclude['/user'] = TRUE;

    // Add the Home link.
    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    $page_title = $this->titleResolver->getTitle($this->request, $route_match->getRouteObject());

    if (!empty($page_title)) {
      $links[] = Link::fromTextAndUrl($page_title, new Url('<none>'));
    }

    return $breadcrumb->setLinks($links);
