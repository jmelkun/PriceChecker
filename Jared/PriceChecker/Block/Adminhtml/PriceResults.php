<?php

namespace Jared\PriceChecker\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Jared\PriceChecker\Model\PriceChecker as PriceCheckerModel;

class PriceResults extends Template
{
    protected $priceChecker;

    public function __construct(
        Context $context,
        PriceCheckerModel $priceChecker,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->priceChecker = $priceChecker;
    }

    public function getCompetitivePricingProducts()
    {
        return $this->priceChecker->getCompetitivePricingProducts(); 
    }
}
