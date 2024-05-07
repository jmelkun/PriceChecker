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

                try {
                    // Random delay between 5 to 10 seconds
                    $delayInSeconds = rand(5, 10);
                    sleep($delayInSeconds);
                    
                    $this->httpClient->get($url);
                    $response = $this->httpClient->getBody();

                    if ($this->httpClient->getStatus() == 200) {
                        $html = $response;
                        $this->logger->info('Regex Used:' . $snippet);

                        // Escape the snippet to use it as a regex pattern
                        $pattern = '#' . $snippet . '#';

                        // Search for the matching HTML tag in the text
                        if (preg_match($pattern, $html, $matches)) {
                            $competitorPrice = $matches[1];

                            // Store the competitor's price in the 'competitor_price' attribute
                            $product->setCustomAttribute('competitor_price', $competitorPrice);
                            $this->productRepository->save($product); // Save each product individually
                        } else {
                            $this->logger->warning(
                                sprintf(
                                    'No matching price found for product SKU: %s, Product ID: %s, URL: %s',
                                    $product->getSku(),
                                    $product->getId(),
                                    $url
                                )
                            );
                        }
                    } else {
                        $this->logger->warning(
                            sprintf(
                                'Failed to check price for product SKU: %s, Product ID: %s, URL: %s, Status code: %s',
                                $product->getSku(),
                                $product->getId(),
                                $url,
                                $this->httpClient->getStatus()
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
}
