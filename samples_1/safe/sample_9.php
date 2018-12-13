<?php
/**
 * SaveOrderBeforeSalesModelQuoteObserver
*
 * @author    Adrien Illy <adill@smile.fr>
 * @author    Kostiantyn Kovalchuk <kokov@smile.fr>
 * @copyright 2017 Smile
 */
namespace Avatacar\Order\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;

/**
 * Observer class for sales_model_service_quote_submit_before event.
 */
class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{
    /**
     * List of attributes that should be added to an order.
     *
     * @var array
     */
    private $orderAttributes = [
        'garage_information',
        'schedules_information',
        'seller_id',
        'quotation_increment_id'
    ];

    /**
     * List of attributes that should be added to an order item.
     *
     * @var array
     */
    private $orderItemAttributes = [
        'applied_catalog_rules'
    ];

    /**
     * Execute method.
     *
     * @param Observer $observer
     *
     * @return SaveOrderBeforeSalesModelQuoteObserver
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $this->setOrderData($order, $quote, $this->orderAttributes);

        foreach ($order->getItems() as $orderItem) {
            $quoteItem = $quote->getItemById($orderItem->getQuoteItemId());
            $this->setOrderData($orderItem, $quoteItem, $this->orderItemAttributes);
        }

        return $this;
    }

    /**
     * Set order data from quote
     *
     * @param Order|OrderItemInterface $orderObject
     * @param Quote|Item               $quoteObject
     * @param array                    $attributes
     *
     * @return void
     */
    public function setOrderData($orderObject, $quoteObject, $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($quoteObject->hasData($attribute)) {
                $orderObject->setData($attribute, $quoteObject->getData($attribute));
            }
        }
    }
}

