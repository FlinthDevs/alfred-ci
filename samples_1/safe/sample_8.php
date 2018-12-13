<?php
/**
 * Data.
 *
 * @author    Adrien Illy <adill@smile.fr>
 * @copyright 2017 Smile
 */

namespace Avatacar\Order\Helper;

use Magento\Newsletter\Model\SubscriberFactory;
use Avatacar\Vehicle\Helper\Data;
use Avatacar\Customer\Helper\Data as CustomerData;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Data\Rule as SalesRule;

/**
 * Export Helper.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Export extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Subscriber factory.
     *
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * Be2bill helper data.
     *
     * @var \Quadra\Be2bill\Helper\Data
     */
    protected $be2billHelperData;

    /**
     * Vehicle Helper.
     *
     * @var \Avatacar\Vehicle\Helper\DAta
     */
    protected $vehicleHelper;

    /**
     * Garage Helper.
     *
     * @var \Avatacar\Garage\Helper\Address
     */
    protected $garageAddressHelper;

    /**
     * Sale rule Repository.
     *
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    protected $saleRuleRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Fields map.
     *
     * @var array
     */
    protected $fieldsmap = [
        'adresses' => 'address',
        'lignes' => 'ligne',
        'promotions' => 'promotion',
        'items' => 'item',
        'echeances' => 'echeance',
    ];

    /**
     * Payment fields map.
     *
     * @var array
     */
    protected $paymentMethodMapping = [
        'be2bill' => 'B2',
        'paypal_express' => 'PA',
        'mercanet' => 'MR',
    ];

    /**
     * Product Type to ignore a lot of time so they are in variable now.
     *
     * @var array
     */
    protected $productTypeFilter = [
        \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
        \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
        \Avatacar\DynamicBundle\Model\Product\Type::TYPE_CODE,
    ];

    /**
     * Checkout Session.
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Tax calculation.
     *
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $taxCalculation;

    /**
     * Customer Repository.
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Catalog Rule Repository
     *
     * @var \Magento\CatalogRule\Model\CatalogRuleRepository $catalogRuleRepository
     */
    protected $catalogRuleRepository;

    /**
     * Items catalog promotions
     *
     * @var array
     */
    protected $itemsCatalogPromotions = [];

    /**
     * Export constructor.
     *
     * @param SubscriberFactory                                 $subscriberFactory
     * @param \Quadra\Be2bill\Helper\Data                       $be2billHelperData
     * @param \Avatacar\Garage\Helper\Address                   $garageAddressHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Tax\Api\TaxCalculationInterface          $taxCalculation
     * @param \Magento\Framework\App\Helper\Context             $context
     * @param Data                                              $vehicleHelper
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface    $saleRuleRepository
     * @param \Magento\Checkout\Model\Session                   $checkoutSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface   $productRepository
     * @param \Magento\CatalogRule\Model\CatalogRuleRepository  $catalogRuleRepository
     */
    public function __construct(
        SubscriberFactory                                 $subscriberFactory,
        \Quadra\Be2bill\Helper\Data                       $be2billHelperData,
        \Avatacar\Garage\Helper\Address                   $garageAddressHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation,
        \Magento\Framework\App\Helper\Context $context,
        \Avatacar\Vehicle\Helper\Data $vehicleHelper,
        \Magento\SalesRule\Api\RuleRepositoryInterface $saleRuleRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogRule\Model\CatalogRuleRepository $catalogRuleRepository
    ) {
        parent::__construct($context);
        $this->subscriberFactory   = $subscriberFactory;
        $this->be2billHelperData   = $be2billHelperData;
        $this->garageAddressHelper = $garageAddressHelper;
        $this->vehicleHelper       = $vehicleHelper;
        $this->saleRuleRepository  = $saleRuleRepository;
        $this->checkoutSession     = $checkoutSession;
        $this->taxCalculation      = $taxCalculation;
        $this->customerRepository  = $customerRepository;
        $this->productRepository = $productRepository;
        $this->catalogRuleRepository = $catalogRuleRepository;
    }

    /**
     * Build Xml content from order data array.
     *
     * @param Order $order
     *
     * @return \SimpleXMLElement
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildXml($order)
    {
        $orderDataForXml = $this->orderToArray($order);

        return $this->generateXml($orderDataForXml);
    }

    /**
     * Transform the order in data array for xml build.
     *
     * @param Order $order
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function orderToArray($order)
    {
        $orderItemData = $this->getOrderItemData($order);
        $orderData = [
            'client' => $this->getCustomerData($order),
            'paiement' => $this->getPaymentData($order),
            'commande' => $this->getOrderData($order),
            'vehicule' => [],
            'lignes' => $orderItemData,
            'rendezVous' => $this->getRdvData($order),
        ];

        // Loop through each product to search one with vehicule data
        foreach ($order->getItemsCollection() as $item) {
            $vehiculeData = $this->getVehiculeData($item);

            // Save this vehicule data as the order vehicule data if not empty
            if (!empty($vehiculeData['body']) || !empty($vehiculeData['model'])) {
                $orderData['vehicule'] = $vehiculeData;
                break;
            }
        }

        return $orderData;
    }

    /**
     * Get the customer data associated to an order.
     *
     * @param Order $order
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getCustomerData($order)
    {
        $storeName = 'CR';
        $lastname = $firstname = '';
        $customer = null;
        $loyaltyCard = '';
        if (strpos($order->getStoreName(), 'avatacar')) {
            $storeName = 'AV';
        }
        if ($order->getCustomerLastname()) {
            $lastname = $order->getCustomerLastname();
        } elseif ($order->getBillingAddress()) {
            $lastname = $order->getBillingAddress()->getLastname();
        }
        if ($order->getCustomerFirstname()) {
            $firstname = $order->getCustomerFirstname();
        } elseif ($order->getBillingAddress()) {
            $firstname = $order->getBillingAddress()->getFirstname();
        }
        if (!$order->getCustomerIsGuest()) {
            $customer = $this->customerRepository->getById($order->getCustomerId());
        }
        if ($customer && $customer->getCustomAttribute(CustomerData::ATTRIBUTE_CODE_CARREFOUR_CARD)) {
            $loyaltyCard = $customer->getCustomAttribute(CustomerData::ATTRIBUTE_CODE_CARREFOUR_CARD)->getValue();
        }

        $customerData = [
            'CarteFidelite' => $loyaltyCard,
            'autorisationMarketing' => $this->isNewsletterSubscriber($order->getCustomerEmail()),
            'nom' => $lastname,
            'prenom' => $firstname,
            'password' => $order->getCustomer() ? $order->getCustomer()->getPasswordHash() : '',
            'societe' => $order->getBillingAddress() ? $order->getBillingAddress()->getCompany() : '',
            'telephone' => $order->getBillingAddress() ? $order->getBillingAddress()->getTelephone() : '',
            'email' => $order->getCustomerEmail(),
            'boutique' => $storeName,
            'idClient' => $order->getCustomerId(),
        ];
        $shippingAddress = $billingAddress = [];
        if ($order->getShippingAddress()) {
            $shippingAddress = [
                'adresseType' => $order->getShippingAddress()->getAddressType(),
                'nom' => $order->getShippingAddress()->getLastname(),
                'prenom' => $order->getShippingAddress()->getFirstname(),
                'email' => $order->getShippingAddress()->getEmail(),
                'telephone' => $order->getShippingAddress()->getTelephone(),
                'rue' => $this->formatStreet($order->getShippingAddress()->getStreet()),
                'ville' => $order->getShippingAddress()->getCity(),
                'codePostal' => $order->getShippingAddress()->getPostcode(),
                'pays' => $order->getShippingAddress()->getCountryId(),
            ];
        }
        if ($order->getBillingAddress()) {
            $billingAddress = [
                'adresseType' => $order->getBillingAddress()->getAddressType(),
                'nom' => $order->getBillingAddress()->getLastname(),
                'prenom' => $order->getBillingAddress()->getFirstname(),
                'email' => $order->getBillingAddress()->getEmail(),
                'telephone' => $order->getBillingAddress()->getTelephone(),
                'rue' => $this->formatStreet($order->getBillingAddress()->getStreet()),
                'ville' => $order->getBillingAddress()->getCity(),
                'codePostal' => $order->getBillingAddress()->getPostcode(),
                'pays' => $order->getBillingAddress()->getCountryId(),
            ];
        }
        $interventionAddress = [];
        $garageAddress = $this->garageAddressHelper->getOrderInterventionAddress($order->getIncrementId());
        $garageInformation = $order->getGarageInformation() ? unserialize($order->getGarageInformation()) : [];
        $garageLastname = $garageFirstname = '';

        if ($garageAddress) {
            $garageLastname = $garageAddress->getLastname();
            $garageFirstname = $garageAddress->getFirstname();

            $interventionAddress = [
                'adresseType' => 'intervention',
                'nom' => $garageLastname,
                'prenom' => $garageFirstname,
                'rue' => $garageAddress->getStreet(),
                'ville' => $garageAddress->getCity(),
                'codePostal' => $garageAddress->getPostcode(),
                'pays' => $garageAddress->getCountryId(),
            ];
        }
            $customerData['adresses'][] = $shippingAddress;
            $customerData['adresses'][] = $billingAddress;
            $customerData['adresses'][] = $interventionAddress;

            return $customerData;
    }

    /**
     * Get the payment data associated to an order.
     *
     * @param Order $order
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getPaymentData($order)
    {
        $paymentAdditionalInformation = $order->getPayment()->getAdditionalInformation();
        $organisme = $paymentType = '';
        $echeance = [];
        if (isset($this->paymentMethodMapping[$order->getPayment()->getMethod()])) {
            $organisme = $this->paymentMethodMapping[$order->getPayment()->getMethod()];
        }

        if ($order->getPayment()->getMethod() == 'be2bill'
            && strpos($paymentAdditionalInformation['account'], 'CB') !== false
            && ($paymentAdditionalInformation['options'] == 'standard'
            || $paymentAdditionalInformation['options'] == 'delivery')
        ) {
            $paymentType = 'CB';
        } elseif ($order->getPayment()->getMethod() == 'be2bill'
            && strpos($paymentAdditionalInformation['account'], 'CB') !== false
            && $paymentAdditionalInformation['options'] == 'ntimes'
        ) {
            $paymentType = 'CB3';
            $echeance = $this->getPaymentScheduleData(
                $paymentAdditionalInformation,
                $order->getCreatedAt(),
                $order->getGrandTotal()
            );
        } elseif (strpos($order->getPayment()->getMethod(), 'paypal') !== false) {
            $paymentType = 'PA';
        }

        $paymentData = [
            'paiementType' => $paymentType,
            'organisme' => $organisme,
            'montant' => $order->getPayment()->getAmountOrdered(),
            'echeances' => $echeance,
        ];

        $be2billExecCode = $be2billMessage = $be2billOrderId = $be2billTransactionId = '';
        if ($order->getPayment()->getMethod() == 'be2bill') {
            $paymentAdditionalData = $order->getPayment()->getAdditionalData() ?
                unserialize($order->getPayment()->getAdditionalData()) :
                [];
            $be2billTransactionId = $order->getPayment()->getLastTransId();
            $be2billOrderId = $order->getIncrementId();
            $be2billMessage = isset($paymentAdditionalData['message']) ? $paymentAdditionalData['message'] : '';
            $be2billExecCode = isset($paymentAdditionalData['execCode']) ? $paymentAdditionalData['execCode'] : '';
        }
        $b2billData = [
            'execCode' => $be2billExecCode,
            'message' => $be2billMessage,
            'orderId' => $be2billOrderId,
            'transactionId' => $be2billTransactionId,
        ];
        $paymentData['b2Bill'] = $b2billData;

        return $paymentData;
    }

    /**
     * Get the order data associated to an order.
     *
     * @param Order $order
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    protected function getOrderData($order)
    {
        $createdAt = date('dmY H:i:s');
        $paht = $fdpAchatHT = 0;
        if ($order->getCreatedAt()) {
            $createdAt = date('dmY H:i:s', strtotime($order->getCreatedAt()));
        }
        $taxeRate = $this->taxCalculation->getCalculatedRate(
            $order->getItemsCollection()->getFirstItem()->getProduct()->getTaxClassId(),
            $order->getCustomerId(),
            $order->getStoreId()
        );
        $productTypesFilter = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
        ];
        foreach ($order->getItemsCollection($productTypesFilter) as $item) {
            $paht += $item->getProduct()->getCost() * $item->getQtyOrdered();
            $fdpAchatHT += $item->getProduct()->getPortpaht();
        }
        $orderData = [
            'numero' => $order->getIncrementId(),
            'id' => $order->getId(),
            'statut' => $order->getStatus(),
            'dateCreation' => $createdAt,
            'paHT' => $paht,
            'pBTTC' => $this->getItemsTotalCost($order),
            'pNTTC' => $order->getGrandTotal(),
            'coeffTva' => $taxeRate / 100,
            'fdpAchatHT' => $fdpAchatHT,
            'fdpTTC' => $order->getShippingAmount(),
            'sellerId' => $order->getSellerId(),
            'quoteId' => $order->getQuotationIncrementId(),
        ];
        $comData = ['message' => null];
        $orderData['commentaires'] = $comData;
        $promotionData = $this->getPromotionDataForOrder($order);
        $orderData['promotions'] = $promotionData;

        return $orderData;
    }

    /**
     * Get the vehicle data associated to an order.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getVehiculeData($item)
    {
        $additionalData = $item->getAdditionalData() ? unserialize($item->getAdditionalData()) : [];
        $bodyAvatacarId = '';
        $ktypeAvatacarId = '';
        $ktype = $registration = '';
        $name = '';
        $brand = '';
        $model = '';
        $body = '';
        $gamme = '';
        $serie = '';
        $energy = '';
        $ktypeId = '';
        $kilometrageVehiculeSaisi = '';
        $ageVehiculeSaisi = '';

        if (!empty($additionalData['vehicle'])) {
            $identificationData = $additionalData['vehicle'];
            $name = $this->getVehicleName($identificationData);
            if (!empty($identificationData['version'])) {
                $bodyAvatacarId = $identificationData['version']['avatacar_vehicle_version_id'];
                $brand = $identificationData['version']['brand_name'];
                $model = $identificationData['version']['model_range'].' '.
                    $identificationData['version']['body_generation'];
                $body = $identificationData['version']['model_serie'] ?
                    $identificationData['version']['model_serie'] :
                    $identificationData['version']['model_door_number'].' portes';
                $gamme = $identificationData['version']['model_range'];
                $serie = $identificationData['version']['body_generation'];
            }
            if (!empty($identificationData['ktype'])) {
                $ktypeAvatacarId = $identificationData['ktype']['avatacar_vehicle_version_id'];
                $ktype = $identificationData['ktype']['label'];
                $ktypeId = $identificationData['ktype']['id'];
                $energy = $identificationData['ktype']['energy'];
            }

            if (isset($identificationData['registrationId'])) {
                $registration = $identificationData['registrationId'];
            } elseif (isset($additionalData['registrationId'])) {
                $registration = $additionalData['registrationId'];
            }
        }

        if (!empty($additionalData['maintenance_plan'])) {
            $maintenancePlan = $additionalData['maintenance_plan'];
            $kilometrageVehiculeSaisi = !empty($maintenancePlan['kms']) ? $maintenancePlan['kms'] : '';
            $ageVehiculeSaisi = [
                'an' => !empty($maintenancePlan['an']) ? $maintenancePlan['an'] : '',
                'mois' => !empty($maintenancePlan['mois']) ? $maintenancePlan['mois'] : '',
            ];
        }
        $vehiculeData = [
            'nom' => $name,
            'brand' => $brand,
            'model' => $model,
            'body' => $body,
            'gamme' => $gamme,
            'serie' => $serie,
            'bodyAvatacarVersionVehiculeId' => $bodyAvatacarId,
            'ktype' => $ktype,
            'energy' => $energy,
            'ktypeAvatacarVersionVehiculeId' => $ktypeAvatacarId,
            'ktypeId' => $ktypeId,
            'immatriculation' => $registration,
            'kilometrageVehiculeSaisi' => $kilometrageVehiculeSaisi,
            'ageVehiculeSaisi' => $ageVehiculeSaisi,
        ];

        return $vehiculeData;
    }

    /**
     * Get Vehicle Name.
     *
     * @param array $identificationData
     *
     * @return string
     */
    protected function getVehicleName($identificationData)
    {
        $name = '';
        if (!empty($identificationData['version'])) {
            $name .= $identificationData['version']['brand_name'].' '
                .$identificationData['version']['model_range'].' '
                .$identificationData['version']['body_generation'].' - ';
            $name .= $identificationData['version']['model_serie'] ?
                $identificationData['version']['model_serie'] :
                $identificationData['version']['model_door_number'].' portes';

            $endDate = $startDate = __('Aujourd\'hui');
            if (strtotime($identificationData['version']['body_end_date']) > Data::REFERENCE_TIME) {
                $endDate = $this->vehicleHelper->formatDate($identificationData['version']['body_end_date']);
            }
            if (strtotime($identificationData['version']['body_start_date']) > Data::REFERENCE_TIME) {
                $startDate = $this->vehicleHelper->formatDate($identificationData['version']['body_start_date']);
            }

            $name .= __(' de ').$startDate.__(' à ').$endDate.' - ';

        }
        if (!empty($identificationData['ktype'])) {
            $ktype = $identificationData['ktype']['label'];
            $name .= $ktype;
        }

        return $name;
    }

    /**
     * Get the rendez-vous data associated to an order.
     *
     * @param Order $order
     *
     * @return array
     */
    protected function getRdvData($order)
    {
        $garageInformation = $order->getGarageInformation() ? unserialize($order->getGarageInformation()) : [];
        $garageId = $dateRDV = $prestation = $demiJourId = '';
        if (!empty($garageInformation)) {
            $garageId = $garageInformation['garage_id'];
            $dateRDV = $garageInformation['selected_date'];
            $prestation = !empty($garageInformation['prestation']) ? $garageInformation['prestation'] : '';
            $demiJourId = !empty($garageInformation['demiJourId']) ? $garageInformation['demiJourId'] : '';
        }
        $rdvData = [
            'garageId' => $garageId,
            'dateRDV' => $dateRDV,
            'prestations' => $prestation,
            'demiJourId' => $demiJourId,
        ];

        return $rdvData;
    }

    /**
     * Get the item data associated to an order.
     *
     * @param Order $order
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function getOrderItemData($order)
    {
        $itemsData = [];
        foreach ($order->getItemsCollection() as $item) {
            /* @var $item \Magento\Sales\Model\Order\Item */
            $productType = 'produit';
            $packType = '';
            $product = $item->getProduct();
            $puNTTC = $item->getPriceInclTax();
            $puBTTC = $item->getBaseOriginalPrice();
            $maintenanceKm = null;

            if ($item->getProductType() == \Avatacar\Catalog\Helper\Data::PRODUCT_TYPE_ID_CONFIGURABLE) {
                $productType = 'pack';
                $packType = $item->getSku();
                $puBTTC = $item->getPriceInclTax();
            } elseif (!in_array($item->getProductType(), $this->productTypeFilter)) {
                $productType = 'pack';
                $packType = $item->getSku();
                //Calculate unit prices for bundle products.
                $tmpPuBTTC = $this->getBundleItemBrutPrice($item->getId(), $order, true);
                $tmpPuNTTC = $this->getBundleItemNetPrice($item->getId(), $order, true);
                $puBTTC = $tmpPuBTTC ? $tmpPuBTTC : $puBTTC;
                $puNTTC = $tmpPuNTTC ? $tmpPuNTTC : $puNTTC;
            } elseif ($item->getProductType() == \Avatacar\DynamicBundle\Model\Product\Type::TYPE_CODE) {
                $productType = 'service';
                $puBTTC = $item->getBasePriceInclTax();
                $data = $item->getAdditionalData();
                if (!empty($data) && $data = unserialize($data)) {
                    if (!empty($data['maintenance_plan'])) {
                        $maintenanceKm = $data['maintenance_plan']['kms'];
                    }
                }

            } elseif ($item->getParentItemId() && !$puBTTC) {
                $puBTTC = $item->getPriceInclTax() - $item->getDiscountAmount();
            } elseif ($item->getParentItemId() && $item->getDiscountAmount() > 0) {
                //If line has discount, we have to set the NTTC price by dividing discount by qtyOrdered.
                $puNTTC = $item->getPriceInclTax() - ( $item->getDiscountAmount() / $item->getQtyOrdered() );
            }
            $additionalData = unserialize($item->getAdditionalData());
            $taxeRate = $this->taxCalculation->getCalculatedRate(
                $product->getTaxClassId(),
                $order->getCustomerId(),
                $order->getStoreId()
            );
            $fournisseurLabel = '';
            if ($additionalData && isset($additionalData['fournisseur'])) {
                $fournisseurLabel = $additionalData['fournisseur'];
            } elseif ($product->getFournisseur()) {
                $fournisseurLabel = $product->getFournisseur();
            }

            if (!$puNTTC && $product->getFinalPrice()) {
                $puNTTC = $product->getFinalPrice();
            }

            if (!$puBTTC && $product->getFinalPrice()) {
                $puBTTC = $product->getFinalPrice();
            }

            $itemData = [
                'type' => $productType,
                'packType' => $packType,
                'logCode' => $additionalData && isset($additionalData['logId']) ? $additionalData['logId'] : '',
                'sku' => $product->getSku(),
                'ean' => $product->getEan(),
                'reference' => $product->getReference(),
                'fournisseurLabel' => $fournisseurLabel,
                'nom' => $item->getName(),
                'quantite' => $item->getQtyOrdered(),
                'paHT' => $product->getCost(),
                'puBTTC' => $puBTTC,
                'puNTTC' => $puNTTC,
                'coeffTva' => $taxeRate / 100,
                'fdpAchatHT' => $product->getPortpaht(),
                'fdpTTC' => $item->getFraisDePort(),
                'fddTTC' => '',
                'anomalie' => '',
                'estFacturable' => '',
                'decalageRdv' => $additionalData && isset($additionalData['decalage_rdv']) ?
                    $additionalData['decalage_rdv'] :
                    '',
                'temps_prestation' => $item->getProduct()->getTempsPrestation(),
            ];
            $promotionData = $this->getPromotionDataForItem($item, $order);
            $itemData['promotions'] = $promotionData;

            if (!empty($maintenanceKm)) {
                $itemData['planRevisionConstructeur'] = $maintenanceKm;
            }

            if (isset($itemsData[$item->getParentItemId()])) {
                $itemsData[$item->getParentItemId()]['items'][] = $itemData;
                $promotion = $itemsData[$item->getParentItemId()]['promotions'];
                $itemsData[$item->getParentItemId()]['promotions'] = array_merge($promotion, $itemData['promotions']);
            } else {
                $itemsData[$item->getId()] = $itemData;

                $itemProductOption = $this->getItemProductOptionData($item, $taxeRate);
                if (!empty($itemProductOption)) {
                    $itemsData[$item->getId()]['items'][] = $itemProductOption;
                }
            }
        }
        return $itemsData;
    }



    /**
     * Generate the XML Element.
     *
     * @param array             $array
     * @param \SimpleXMLElement $rootElement
     * @param \SimpleXMLElement $xml
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function generateXml($array, $rootElement = null, $xml = null)
    {
        if ($xml === null) {
            $root = $rootElement !== null ?
                $rootElement :
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><export/>';
            $xml = new \SimpleXMLElement($root);
        }

        foreach ($array as $k => $v) {
            if (is_array($v)) { //nested array
                if (is_numeric($k) && $k != null || $k === 0) {
                    $k = $this->fieldsmap[$rootElement];
                }
                $this->generateXml($v, $k, $xml->addChild($k));
            } else {
                $xml->addChild($k, (string) htmlspecialchars($v));
            }
        }

        return $xml->asXML();
    }

    /**
     * Format the street.
     *
     * @param array $street
     *
     * @return string
     */
    protected function formatStreet($street)
    {
        $streetName = '';
        foreach ($street as $streetLine) {
            $streetName .= $streetLine.'|';
        }

        return rtrim($streetName, '|');
    }

    /**
     * Get the promotion Data related t the order.
     *
     * @param Order $order
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPromotionDataForOrder($order)
    {
        $discountAmount = abs($order->getDiscountAmount());
        $catalogDiscountAmount = $this->getCatalogDiscountAmount($order);

        return $this->getPromotion(
            $order->getAppliedRuleIds(),
            $catalogDiscountAmount,
            $discountAmount,
            $order->getCouponCode()
        );
    }

    /**
     * Get the promotion Data related to a single item.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param Order      $order
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPromotionDataForItem($item, $order)
    {
        $catalogDiscountAmount = 0;
        $discountAmount = abs($item->getDiscountAmount());
        if (!$discountAmount) {
            $catalogDiscountAmount = $item->getBaseOriginalPrice() - $item->getPriceInclTax();
            if ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $itemId = $item->getId();
                //We want catalog discount amount for whole line, meaning we need to use qty.
                $catalogDiscountAmount = ($this->getBundleItemBrutPrice($itemId, $order) - $item->getPriceInclTax())
                    * $item->getQtyOrdered();
            }
        }

        return $this->getPromotion(
            $item->getAppliedRuleIds(),
            $catalogDiscountAmount,
            $discountAmount,
            $order->getCouponCode(),
            $item
        );
    }

    /**
     * Get Items total cost.
     *
     * @param Order $order
     *
     * @return int
     */
    protected function getItemsTotalCost($order)
    {
        $total = 0;
        $parentItem = [];
        foreach ($order->getItemsCollection() as $item) {
            /* @var $item \Magento\Sales\Model\Order\Item */
            $brutPrice = 0;
            if (!in_array($item->getParentItemId(), $parentItem)) {
                $brutPrice = $item->getBaseOriginalPrice();
            }
            if (!in_array($item->getProductType(), $this->productTypeFilter)) {
                $tmpBrutPrice = $this->getBundleItemBrutPrice($item->getId(), $order, true);
                if ($tmpBrutPrice) {
                    $brutPrice = $tmpBrutPrice;
                }
                $parentItem[] = $item->getId();
            } elseif ($item->getProductType() == \Avatacar\DynamicBundle\Model\Product\Type::TYPE_CODE) {
                $brutPrice = $item->getBasePriceInclTax();
                $parentItem[] = $item->getId();
            }
            $total += $brutPrice * $item->getQtyOrdered();
        }

        return $total;
    }

    /**
     * Get Catalog Rule Amount.
     *
     * @param Order $order
     *
     * @return int
     */
    protected function getCatalogDiscountAmount($order)
    {

        return $this->getItemsTotalCost($order) + $order->getShippingAmount() - abs($order->getDiscountAmount())
            - $order->getGrandTotal();
    }

    /**
     * Get and format promotions.
     *
     * @param string $appliedRuleIds
     * @param float $catalogDiscountAmount
     * @param float $discountAmount
     * @param string $couponCode
     * @param OrderItemInterface|boolean $item
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPromotion(
        $appliedRuleIds,
        $catalogDiscountAmount,
        $discountAmount,
        $couponCode,
        $item = false
    ) {
        $promotions = [];
        if ($appliedRuleIds) {
            foreach (explode(',', $appliedRuleIds) as $ruleId) {
                $name = $type = $priority = $discountType = $origin = '';
                $rule = $this->saleRuleRepository->getById($ruleId);
                /* @var $rule SalesRule */
                if ($rule) {
                    $name = $rule->getName();
                    $type = $rule->getSimpleAction();
                    $priority = $rule->getSortOrder();
                    $discountType = $rule->getDiscountAmount();
                    $origin = 'cart';
                }
                $promotions[] = [
                    'nom' => $name,
                    'type' => $type,
                    'priorite' => $priority,
                    'valeurRemise' => $discountType,
                    'montantRemise' => $discountAmount,
                    'origine' => $origin,
                    'codeCoupon' => $couponCode,
                ];
            }
        }
        if (round($catalogDiscountAmount, 2) > 0) {
            $origin = 'catalog';
            if ($item) {
                $catalogRule = json_decode($item->getAppliedCatalogRules(), true);

                if ($catalogRule['rules_from_product']) {
                    foreach ($catalogRule['rules_from_product'] as $productRule) {
                        $rule = $this->catalogRuleRepository->get($productRule['rule_id']);
                        if (!$productPrice = $item->getProduct()->getPrice()) {
                            continue;
                        }
                        $catalogDiscountAmount = $this->getCatalogDiscountAmountByRule($productPrice, $rule);
                        $promotions[] = [
                            'nom' => $rule->getName(),
                            'type' => $productRule['action_operator'],
                            'priorite' => $productRule['sort_order'],
                            'valeurRemise' => $productRule['action_amount'],
                            'montantRemise' => $catalogDiscountAmount,
                            'origine' => $origin,
                            'codeCoupon' => '',
                        ];
                    }
                    $this->itemsCatalogPromotions = array_merge($this->itemsCatalogPromotions, $promotions);
                }

            }
            else {
                $promotions = $this->itemsCatalogPromotions;
            }
        }

        return $promotions;
    }

    /**
     * Get Catalog Discount Amount By Rule
     *
     * @param float $productPrice
     * @param SalesRule $rule
     *
     * @return float
     */
    public function getCatalogDiscountAmountByRule($productPrice, $rule)
    {
        $ruleType = $rule->getSimpleAction();
        $discountAmount = $rule->getDiscountAmount();
        switch ($ruleType) {
            case 'to_fixed':
                $catalogDiscountAmount = $productPrice - $discountAmount;
                break;
            case 'by_fixed':
                $catalogDiscountAmount = $discountAmount;
                break;
            case 'to_percent':
                $catalogDiscountAmount = $productPrice * ((100 - $discountAmount) / 100);
                break;
            case 'by_percent':
                $catalogDiscountAmount = $productPrice * ($discountAmount / 100);
                break;
            default:
                $catalogDiscountAmount = 0;
        }

        return $catalogDiscountAmount;
    }

    /**
     * Get Payment scheduled data.
     *
     * @param string $schedulesInformation
     * @param string $createdAt
     * @param string $grandTotal
     *
     * @return array
     */
    protected function getPaymentScheduleData($schedulesInformation, $createdAt, $grandTotal)
    {
        $ntimes = (int)$schedulesInformation['ntimes'];
        $amount = round($grandTotal, 2) * 100;
        $startDate = new \DateTime($createdAt);

        $schedulesData = [];
        $schedules = $this->be2billHelperData->getSchedule($amount, $ntimes, $startDate);

        if (!empty($schedules)) {
            foreach ($schedules as $date => $amount) {
                $schedulesData[] = ['date' => implode('-', array_reverse(explode('-', $date))),
                                    'montant' => ($amount/100)];
            }
        }
        return $schedulesData;
    }

    /**
     * Get bundle Item Brut Price (without discount).
     *
     * @param int                        $itemId
     * @param Order $order
     * @param bool                       $unitPrice
     *
     * @return float
     */
    protected function getBundleItemBrutPrice($itemId, $order, $unitPrice = true)
    {
        $price = 0;
        foreach ($order->getItemsCollection() as $item) {
            /* @var $item \Magento\Sales\Model\Order\Item */
            $qty = $item->getQtyOrdered();
            if ($unitPrice) {
                $qty = 1;
            }
            if ($item->getParentItemId() == $itemId) {
                $basePrice = $item->getBaseOriginalPrice() ? $item->getBaseOriginalPrice() : $item->getBasePrice();
                $price += $basePrice * $qty;
            }
        }

        return $price;
    }

    /**
     * Get bundle Item Net Price (with discount).
     *
     * @param int                        $itemId
     * @param Order $order
     * @param bool                       $unitPrice
     *
     * @return float
     */
    protected function getBundleItemNetPrice($itemId, $order, $unitPrice = true)
    {
        $price = 0;
        foreach ($order->getItemsCollection() as $item) {
            /* @var $item \Magento\Sales\Model\Order\Item */
            $qty = $item->getQtyOrdered();
            if ($item->getParentItemId() == $itemId) {
                $rowPrice = $item->getRowTotalInclTax() - $item->getDiscountAmount();
                if ($unitPrice) {
                    $rowPrice = $rowPrice / (int)$qty;
                }
                $price += $rowPrice;
            }
        }

        return $price;
    }

    /**
     * Check if customer subscribed to the newsletter.
     *
     * @param string $email
     *
     * @return bool
     */
    protected function isNewsletterSubscriber($email)
    {
        $subscriber = $this->subscriberFactory->create()->loadByEmail($email);
        return $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED;
    }

    /**
     * Get item product option data
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param integer $taxeRate
     *
     * @return array
     */
    protected function getItemProductOptionData($item, $taxeRate)
    {
        $itemData = [];

        if ($item->getProductType() == \Avatacar\Catalog\Helper\Data::PRODUCT_TYPE_ID_CONFIGURABLE) {
            $itemProductOptions = $item->getProductOptions();
            if (!empty($itemProductOptions) && isset($itemProductOptions['options'])) {
                $orderItemOption = $itemProductOptions['options'][0];

                $optionPrice = 0;
                $sku = '';
                foreach ($item->getProduct()->getOptions() as $option) {
                    foreach ($option->getValues() as $value) {
                        if ($orderItemOption['option_value'] == $value->getOptionTypeId()) {
                            $optionPrice = $value->getPrice();
                            $sku = $value->getSku();
                            if (!is_null($sku)) {
                                try {
                                    $product = $this->productRepository->get($sku);
                                } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
                                    $product = null;
                                }
                            }
                            break;
                        }
                    }
                }

                $productType = 'service';
                $itemData = [
                    'type' => $productType,
                    'packType' => '',
                    'logCode' => '',
                    'sku' => $sku,
                    'ean' => ($product) ? $product->getEan() : '',
                    'reference' => ($product) ? $product->getReference() :'',
                    'fournisseurLabel' => '',
                    'nom' => $orderItemOption['label'] . ': ' . $orderItemOption['value'],
                    'quantite' => $item->getQtyOrdered(),
                    'paHT' => ($product) ? $product->getCost() :'',
                    'puBTTC' => $optionPrice,
                    'puNTTC' => $optionPrice,
                    'coeffTva' => $taxeRate / 100,
                    'fdpAchatHT' => null,
                    'fdpTTC' => null,
                    'fddTTC' => null,
                    'anomalie' => null,
                    'estFacturable' => null,
                    'decalageRdv' => null,
                    'temps_prestation' => ($product) ? $product->getTempsPrestation() :'',
                ];
            }
        }

        return $itemData;
    }
}

