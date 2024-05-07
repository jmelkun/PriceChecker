<?php
namespace Jared\PriceChecker\Cron;

use Jared\PriceChecker\Model\PriceChecker;

class CheckPrices
{
    protected $priceChecker;

    public function __construct(PriceChecker $priceChecker)
    {
        $this->priceChecker = $priceChecker;
    }

    public function execute()
    {
        $this->priceChecker->checkPrices();
    }
}