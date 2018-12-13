<?php

namespace Drupal\pomona_catalog\Service;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\pomona_sso\Service\SSOManagerInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\pomona_customer_space\Service\AuthenticationHelperInterface;

/**
 * Class CatalogHelper.
 *
 * @package Drupal\pomona_catalog\Service
 */
class CatalogHelper implements CatalogHelperInterface {

  /**
   * The name of the widget config page.
   *
   * @var string
   */
  protected $configPageNameWidget = 'config_pomona_widget';

  /**
   * The widget config page.
   *
   * @var \Drupal\config_pages\Entity\ConfigPages
   */
  protected $configPageWidget;

  /**
   * The name of the catalog config page.
   *
   * @var string
   */
  protected $configPageNameCatalog = 'page_list_catalog_conf';

  /**
   * The catalog config page.
   *
   * @var \Drupal\config_pages\Entity\ConfigPages
   */
  protected $configPageCatalog;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The SSO manager.
   *
   * @var \Drupal\pomona_sso\Service\SSOManagerInterface
   */
  protected $SSOManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Authentication service.
   *
   * @var \Drupal\pomona_customer_space\Service\AuthenticationHelperInterface
   */
  protected $authenticationService;

  /**
   * CatalogHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\pomona_customer_space\Service\AuthenticationHelperInterface $authentication
   *   The authentication service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\pomona_sso\Service\SSOManagerInterface $sso_manager
   *   The SSO manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AuthenticationHelperInterface $authentication,
    AccountProxyInterface $current_user,
    SSOManagerInterface $sso_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->authenticationService = $authentication;
    $this->configPageWidget = config_pages_config($this->configPageNameWidget);
    $this->configPageCatalog = config_pages_config($this->configPageNameCatalog);
    $this->currentUser = $current_user;
    $this->SSOManager = $sso_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getBackgroundImageUrl() {
    $background_page = '';

    if ($this->configPageCatalog != NULL) {
      // Recovers the background image url.
      if ($this->configPageCatalog->hasField('field_background_catalog_page')) {
        /** @var \Drupal\media\Entity\Media[] $background_medias */
        $background_medias = $this->configPageCatalog->get('field_background_catalog_page')
          ->referencedEntities();

        if (!empty($background_medias)) {
          $background_media = $background_medias[0];

          if ($background_media->hasField('field_media_image')) {
            /** @var \Drupal\file\Entity\File[] $image_files */
            $image_files = $background_media->get('field_media_image')
              ->referencedEntities();

            if (!empty($image_files)) {
              $file = $image_files[0];
              $background_page = file_create_url($file->getFileUri());
            }
          }
        }
      }
    }

    return $background_page;
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  public function getCatalogsListsWithSegmentationTabs() {
    $tabs = [];
    $catalogs_nid_by_segmentation = [];
    $catalogs_by_segmentation = [];
    $catalog_types_by_segmentation = [];
    $segmentation_tids = [];
    $exclude_catalog_type = [];
    // Recovers segmentation terms.
    $segmentation_term_tids = $this->getSegmentation();

    // Recovers catalogs by segmentation.
    if (!empty($segmentation_term_tids)) {
      // @codingStandardsIgnoreStart
      /** @var \Drupal\taxonomy\Entity\Term[] $segmentations */
      $segmentations = Term::loadMultiple($segmentation_term_tids);
      // @codingStandardsIgnoreEnd

      foreach ($segmentations as $tid => $segmentation) {
        // Get icon sprite name.
        $icon_ref = $tid;
        $segmentation_tids[$tid] = $tid;
        if ($segmentation->hasField('field_icon_segmentation')) {
          $icon_value = $segmentation->get('field_icon_segmentation')
            ->getValue();
          if (!empty($icon_value)) {
            $icon_ref = $icon_value[0]['value'];
            $segmentation_tids[$tid] = $icon_value[0]['value'];
          }
        }

        // Get labels of tabs.
        $tabs[$icon_ref]['title_light'] = $segmentation->label();
        if ($segmentation->hasField('field_bold_title')) {
          $bold_title = $segmentation->get('field_bold_title')->getValue();
          if (!empty($bold_title)) {
            $tabs[$icon_ref]['title_bold'] = $bold_title[0]['value'];
          }
        }

        // Recovers catalog types by segmentation.
        $catalog_types_tids = $this->getCatalogTypeBySegmentationTid($tid);
        if (!empty($catalog_types_tids)) {
          // @codingStandardsIgnoreStart
          /** @var \Drupal\taxonomy\Entity\Term[] $types */
          $types = Term::loadMultiple($catalog_types_tids);
          // @codingStandardsIgnoreEnd

          foreach ($types as $tid_type => $type) {
            $anonymous_access = TRUE;
            /** @var \Drupal\taxonomy\Entity\Term $type */
            if ($type->hasField('field_logged_customer_access')) {
              $has_restricted_access = $type->get('field_logged_customer_access')
                ->getValue();

              if (!empty($has_restricted_access) && $has_restricted_access[0]['value'] == '1') {
                $anonymous_access = FALSE;
                $exclude_catalog_type[] = $tid_type;
              }
            }
            $catalog_types_by_segmentation[$icon_ref][$tid_type] = [
              'name' => $type->label(),
              'anonymous_access' => $anonymous_access,
            ];
          }
        }
        // Get public catalogs.
        if ($this->currentUser->isAnonymous()) {
          $catalogs_nid_by_segmentation[$icon_ref] = $this->getCatalogs([$tid], NULL, $exclude_catalog_type, TRUE);
        }
        else {
          $catalogs_nid_by_segmentation[$icon_ref] = $this->getUserCatalogs();
        }
      }
    }
    // Recovers teaser_vertical view of catalogs.
    foreach ($catalogs_nid_by_segmentation as $key => $catalogs) {
      $catalogs_by_segmentation[$key] = $this->getCatalogsInViewMode($catalogs, 'teaser_vertical');
    }

    $user_segmentation = $this->SSOManager->getUserSegmentation();
    if (!empty($segmentations) && count($segmentations) == 2) {
      $segmentation_tab_2 = end($segmentations);
      $segmentation_tab_2_id = $segmentation_tab_2->id();
    }

    $tab_1_first = TRUE;
    $tab_2_first = FALSE;
    if (count($user_segmentation) == 1) {
      if (isset($segmentation_tab_2_id)) {
        if ($user_segmentation[0] == $segmentation_tab_2_id) {
          $tab_1_first = FALSE;
          $tab_2_first = TRUE;
        }
      }
    }

    return [
      'segmentation_tab_hidden' => FALSE,
      'segmentation' => $tabs,
      'segmentation_selector' => $segmentation_tids,
      'filters' => $catalog_types_by_segmentation,
      'catalogs_by_segmentation' => $catalogs_by_segmentation,
      'tab_1_first' => $tab_1_first,
      'tab_2_first' => $tab_2_first,
    ];
  }
}
