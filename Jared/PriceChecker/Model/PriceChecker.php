<?php

namespace Jared\PriceChecker\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\HTTP\Client\Curl as HttpClient;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class PriceChecker
{
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $httpClient;
    protected $scopeConfig;
    protected $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        HttpClient $httpClient,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->httpClient = $httpClient;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function checkPrices(){
        $products = $this->productRepository->getList($this->searchCriteriaBuilder->create());
    
        foreach ($products->getItems() as $product) {
            $competitorUrl = $product->getCustomAttribute('competitor_url');
            $priceSnippet = $product->getCustomAttribute('price_snippet');
    
            if ($competitorUrl && $competitorUrl->getValue() && $priceSnippet && $priceSnippet->getValue()) {
                $url = $competitorUrl->getValue();
                $snippet = $priceSnippet->getValue();
    
                $client = $this->httpClient;
                $client->setHeaders(['Content-Type' => 'application/json']);
                $client->post('YOUR AWS LAMBDA API HERE', json_encode(['url' => $url, 'snippet' => $snippet]));
    
                try {
                    $body = json_decode($client->getBody(), true);
                    if ($client->getStatus() == 200 && isset($body['body'])) {
                    
                        $competitorPrice = $body['body'];
    
                        // Store the competitor's price in the 'competitor_price' attribute
                        $product->setCustomAttribute('competitor_price', $competitorPrice);
                        $this->productRepository->save($product); // Save each product individually
                    } else {
                        $this->logger->warning(
                            sprintf(
                                'No matching price found for product SKU: %s, Product ID: %s, URL: %s, body: %s',
                                $product->getSku(),
                                $product->getId(),
                                $url,
                                $body["body"]
                            )
                        );
                    }
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf(
                            'Error occurred while checking price for product SKU: %s, Product ID: %s, URL: %s, Message: %s',
                            $product->getSku(),
                            $product->getId(),
                            $url,
                            $e->getMessage()
                        )
                    );
                }
            }
        }
    }
    
    

    public function getCompetitivePricingProducts()
{
    $searchCriteria = $this->searchCriteriaBuilder
        ->addFilter('price', 0, 'gt') // Ensure the price is greater than 0
        ->addFilter('competitor_price', 0, 'gt') // Ensure competitor price is greater than 0
        ->create();

    $products = $this->productRepository->getList($searchCriteria)->getItems();

    $competitiveProducts = [];
    foreach ($products as $product) {
        $storePrice = (float) $product->getPrice();
        $competitorPrice = (float) $product->getCustomAttribute('competitor_price')->getValue();
        // Check if competitor's price is lower than store price
        if ($competitorPrice > 0 && $storePrice > $competitorPrice) {
            $competitiveProducts[] = $product;
        }
    }

    return $competitiveProducts;
}

}
