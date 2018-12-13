<?php
/**
 * IdentifyFromRegistration.
 *
 * @author    Adrien Illy <adill@smile.fr>
 * @copyright 2017 Smile
 */

namespace Avatacar\Vehicle\Controller\Identification;

/**
 * IdentifyFromRegistration class.
 */
class IdentifyFromRegistration extends \Magento\Framework\App\Action\Action
{
    /**
     * Identification Helper.
     *
     * @var \Avatacar\Vehicle\Helper\Identification
     */
    protected $identificationHelper;

    /**
     * Json Factory.
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Vehicle Data.
     *
     * @var \Avatacar\Vehicle\Helper\Data
     */
    protected $vehicleHelper;

    /**
     * Page Factory.
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Catalog Helper.
     *
     * @var \Avatacar\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * Identification Session.
     *
     * @var \Avatacar\Vehicle\Model\Session
     */
    protected $identificationSession;

    /**
     * Customer Helper.
     *
     * @var \Avatacar\Customer\Helper\Data
     */
    protected $customerHelper;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Avatacar\Vehicle\Helper\Identification          $identificationHelper
     * @param \Avatacar\Vehicle\Helper\Data                    $vehicleHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory
     * @param \Avatacar\Catalog\Helper\Data                    $catalogHelper
     * @param \Avatacar\Vehicle\Model\Session                  $identificationSession
     * @param \Avatacar\Customer\Helper\Data                   $customerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Avatacar\Vehicle\Helper\Identification $identificationHelper,
        \Avatacar\Vehicle\Helper\Data $vehicleHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Avatacar\Catalog\Helper\Data $catalogHelper,
        \Avatacar\Vehicle\Model\Session $identificationSession,
        \Avatacar\Customer\Helper\Data $customerHelper
    ) {
        parent::__construct($context);
        $this->identificationHelper = $identificationHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->vehicleHelper = $vehicleHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->catalogHelper = $catalogHelper;
        $this->identificationSession = $identificationSession;
        $this->customerHelper = $customerHelper;
    }

    /**
     * Execute controller action.
     *
     * @return \Magento\Framework\View\Result\PageFactory|\Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $categoryId = $productSku = 0;
        if ($this->getRequest()->getParam('category_path')) {
            $categoryId = $this->catalogHelper->getCategoryIdByUrlKey($this->getRequest()->getParam('category_path'));
        }
        if ($this->getRequest()->getParam('target_category')) {
            $categoryId = $this->getRequest()->getParam('target_category');
        }
        if ($this->getRequest()->getParam('product_sku')) {
            $productSku = $this->getRequest()->getParam('product_sku');
        }
        if ($this->getRequest()->getParam('postCode')) {
            $this->identificationSession->setData(
                \Avatacar\Vehicle\Helper\Identification::POST_CODE_SESSION_KEY,
                $this->getRequest()->getParam('postCode')
            );
        }
        if ($this->getRequest()->getParam('maintenance-plan')) {
            $this->identificationSession->setData(
                \Avatacar\Vehicle\Helper\Identification::MAINTENANCE_PLAN_KEY,
                $this->getRequest()->getParam('maintenance-plan')
            );
        }

        $result = $this->resultJsonFactory->create();
        $registrationId = $this->getRequest()->getParam('registrationId');
        $return = [
            'valid' => false,
            'level' => \Avatacar\Vehicle\Helper\Identification::NONE,
            'force_step' => \Avatacar\Vehicle\Helper\Identification::KTYPE_TIRE,
        ];
        if ($registrationId) {
            $this->customerHelper->updateLicencePlate($registrationId);
            $vehicleData = $this->identificationHelper->addIdentificationFromRegistration($registrationId);

            if ($this->getRequest()->getParam('mobile_registration')) {
                if ($vehicleData['ktype']) {
                    // Get vehicle ktype to set value in identification page
                    $kTypes[$vehicleData['ktype']->getEnergy()][] =
                        $this->vehicleHelper->formatKtypeForIdentification($vehicleData['ktype']);

                    $return['ktype'] = $kTypes;
                    $return['ktype_title'] = $vehicleData['ktype']->getLabel();
                }

                $bodiesMobile = $this->identificationHelper->getMobileBodiesByRegistrationId($registrationId);
                if ($bodiesMobile) {
                    $return["bodies"] = $bodiesMobile;
                    return $result->setData($return);
                }
            }

            $currentLevel = $this->identificationHelper->getCurrentLevel();
            if ($currentLevel || $vehicleData) {
                $return['level'] = $currentLevel;
                $identificationData = $this->identificationHelper->getSessionData();
                if ($identificationData
                    && !$vehicleData
                    && !empty($identificationData['version'])
                    && !empty($identificationData['ktype'])
                ) {
                    $this->identificationSession->setData(
                        \Avatacar\Vehicle\Helper\Identification::REGISTRATION_KEY,
                        $registrationId
                    );
                    $resultPage = $this->resultPageFactory->create();
                    $return['valid'] = true;
                    $currentModelId = $this->identificationHelper->getCurrentModelId();
                    $bodies = $this->vehicleHelper->getBodiesByModelId($currentModelId);
                    $return['body_html'] = $resultPage->getLayout()
                        ->createBlock('\Avatacar\Vehicle\Block\Identification\Steps')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/versions.phtml')
                        ->setBodies($bodies)
                        ->toHtml();
                    $version = $identificationData['version'];
                    $return['body_title'] = '<span class="select-auto__name">'.$version['model_range'].' ';
                    $return['body_title'] .= $version['body_generation'].' - ';
                    $return['body_title'] .= $version['model_serie']
                        ? $version['model_serie']
                        : $version['model_door_number'].' portes';
                    $return['body_title'] .= ' </span>';

                    $endDate = $startDate = __('Aujourd\'hui');
                    if (strtotime($version['body_end_date']) > \Avatacar\Vehicle\Helper\Data::REFERENCE_TIME) {
                        $endDate = $this->vehicleHelper->formatDate($version['body_end_date']);
                    }
                    if (strtotime($version['body_start_date']) > \Avatacar\Vehicle\Helper\Data::REFERENCE_TIME) {
                        $startDate = $this->vehicleHelper->formatDate($version['body_start_date']);
                    }

                    $return['body_title'] .= '<span class="select-auto__date">'.__(' de ').$startDate
                        .__(' à ').$endDate.'</span>';

                    $ktypes = $this->vehicleHelper->getKtypesByBodyId($this->identificationHelper->getCurrentBodyId());
                    $return['ktype_html'] = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/ktype.phtml')
                        ->setKtypes($ktypes)
                        ->toHtml();
                    $return['ktype_title'] = $identificationData['ktype']['label'];

                    $tires = $this->vehicleHelper->getTiresByKtypeId($this->identificationHelper->getCurrentKTypeId());
                    $return['tire_html'] = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/tires.phtml')
                        ->setTires($tires['dimensions'])
                        ->toHtml();

                    $return['model_title'] = $identificationData['version']['brand_name'].' '.
                        $identificationData['version']['model_name'];
                } elseif ($vehicleData) {
                    $return['model_title'] = __('Je sélectionne ma voiture');
                    $this->identificationSession->setData('registrationId', $registrationId);
                    $resultPage = $this->resultPageFactory->create();
                    $return['force_step'] = \Avatacar\Vehicle\Helper\Identification::BODY;
                    $return['valid'] = true;
                    $bodies = $vehicleData['body'];
                    $formatedBodies = [];
                    foreach ($bodies as $body) {
                        $formatedBodies[] = $this->vehicleHelper->formatBodyForIdentification($body);
                    }
                    $return['body_html'] = $resultPage->getLayout()
                        ->createBlock('Avatacar\Vehicle\Block\Identification\Steps')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/versions.phtml')
                        ->setBodies($formatedBodies)
                        ->setNoCheck(true)
                        ->toHtml();
                    $return['body_title'] = '<span class="select-auto__name">'.
                        __('Je sélectionne ma version').'</span>';

                    $ktype = $vehicleData['ktype'];
                    $return['ktype_html'] = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/ktype.phtml')
                        ->setKtypes([
                            $ktype->getEnergy() => [$this->vehicleHelper->formatKtypeForIdentification($ktype)],
                        ])->toHtml();
                    $return['ktype_title'] = $vehicleData['ktype']->getLabel();

                    $tires = $this->vehicleHelper->getTiresByKtypeId($vehicleData['ktype']->getKtypeId());
                    $return['tire_html'] = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/tires.phtml')
                        ->setTires($tires['dimensions'])
                        ->toHtml();
                }
            }
        }

        $targetCategory = $this->getRequest()->getParam('target_category');
        if ($targetCategory && !$this->getRequest()->getParam('ajax')) {
            $resultRedirect = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getUrl('vehicle/identification'));
            $this->identificationHelper->addCategorySessionData($targetCategory);
            $targetLevel = $this->identificationHelper->getSessionData('target_identification_level');

            if ($this->identificationHelper->checkIdentificationLevel($targetLevel)) {
                $redirectUrl = $this->identificationHelper->getSessionData('return_url');
                $resultRedirect->setUrl($redirectUrl);
            }

            return $resultRedirect;
        }
        if ($categoryId) {
            $this->identificationHelper->addCategorySessionData($categoryId);
            $return['target_level'] = $this->identificationHelper->getSessionData('target_identification_level');
        }
        if ($productSku) {
            $this->identificationHelper->addProductSessionData($productSku);
            $return['target_level'] = $this->identificationHelper->getSessionData('target_identification_level');
        }

        return $result->setData($return);
    }
}

