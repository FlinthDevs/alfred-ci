<?php
/**
 * DynamicBundle Renderer block.
 *
 * @author    Theo CHARTIER <thcha@smile.fr>
 * @copyright 2017 Smile
 */

namespace Avatacar\DynamicBundle\Block\Checkout\Cart\Item;

use Avatacar\DynamicBundle\Helper\Catalog\Product\Configuration;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Shopping cart item renderer block.
 */
class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * @var Configuration
     */
    protected $dynamicBundleConfiguration = null;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context                          $context
     * @param \Magento\Catalog\Helper\Product\Configuration                             $productConfig
     * @param \Magento\Checkout\Model\Session                                           $checkoutSession
     * @param \Magento\Catalog\Block\Product\ImageBuilder|\Magento\Catalog\Helper\Image $imageBuilder
     * @param \Magento\Framework\Url\Helper\Data                                        $urlHelper
     * @param \Magento\Framework\Message\ManagerInterface                               $messageManager
     * @param PriceCurrencyInterface                                                    $priceCurrency
     * @param \Magento\Framework\Module\Manager                                         $moduleManager
     * @param InterpretationStrategyInterface                                           $messageInterpretationStrategy
     * @param array                                                                     $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        Configuration $configuration,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data
        );
        $this->dynamicBundleConfiguration = $configuration;
        $this->_isScopePrivate = true;
    }

    /**
     * Get the dynamic bundle option list.
     *
     * @return array
     */
    public function getOptionList()
    {
        return $this->dynamicBundleConfiguration->getOptions($this->getItem());
    }
}

