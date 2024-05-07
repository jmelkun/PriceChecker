<?php

namespace Jared\PriceChecker\Controller\Adminhtml\PriceChecker;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Jared\PriceChecker\Model\PriceChecker;

class Check extends Action
{
    protected $priceChecker;

    public function __construct(
        Context $context,
        PriceChecker $priceChecker
    ) {
        parent::__construct($context);
        $this->priceChecker = $priceChecker;
    }

    public function execute()
    {
        try {
            $this->priceChecker->checkPrices();
            $this->messageManager->addSuccessMessage(__('Price checking completed successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while checking prices: %1', $e->getMessage()));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Jared_PriceChecker::check_prices');
    }
}