<?php

namespace Drupal\vdg_breadcrumb\Service;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class VdgDefaultBreadcrumbBuilder.
 *
 * @package Drupal\vdg_breadcrumb
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VdgDefaultBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * Site config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The patch matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The admin context.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Constructs the PathBasedBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The admin context.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    PathMatcherInterface $path_matcher = NULL,
    AdminContext $admin_context,
    RequestStack $request,
    TitleResolverInterface $title_resolver
  ) {
    $this->config = $config_factory->get('system.site');
    $this->pathMatcher = $path_matcher;
    $this->adminContext = $admin_context;
    $this->request = $request->getCurrentRequest();
    $this->titleResolver = $title_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if ($this->adminContext->isAdminRoute($route_match->getRouteObject())) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
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
  }

}
