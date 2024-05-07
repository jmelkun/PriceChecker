<?php

namespace Jared\PriceChecker\Block\Adminhtml\PriceChecker;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\UrlInterface;

class CheckButton extends Template
{
    protected $urlBuilder;

    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
    }

    public function getCheckUrl()
    {
        return $this->urlBuilder->getUrl('pricechecker/priceChecker/check');
    }
}