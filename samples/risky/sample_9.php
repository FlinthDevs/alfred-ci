<?php
/**
 * SaveOrderBeforeSalesModelQuoteObserver
*
 * @author    Adrien Illy <adill@smile.fr>
 * @copyright 2017 Smile
 */
namespace Avatacar\Order\Observer;

/**
 * Observer class for sales_model_service_quote_submit_before event.
 */
class SaveOrderBeforeSalesModelQuoteObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * List of attributes that should be added to an order.
     *
     * @var array
     */
    private $attributes = [
        'garage_information',
        'schedules_information',
        'seller_id',
        'quotation_increment_id',
    ];

    /**
     * Execute method.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return SaveOrderBeforeSalesModelQuoteObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        foreach ($this->attributes as $attribute) {
            if ($quote->hasData($attribute)) {
                $order->setData($attribute, $quote->getData($attribute));
            }
        }

        return $this;
    }
}

