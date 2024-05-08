# PriceChecking
Magento 2 Price Checking Module
Created by Jared Melkun


# What Does The Module Do?
This module adds three fields to products: 

1) competitor_url - Used to get the URL of a competing product
2) price_snippet - A string containing a regex for extracting the price
3) competitor_price - Used to store the price of a competitor's product

By using this construct, we can automate getting the prices of competitors, allowing us to know in real time when we've been undercut so we can update our pricing. 

# How To Use This Module

To use this module, add the module to the app/code folder of your magento installation. Before installing the module, you will need to create an API for fetching the data you will need, and put the URL of the API in the Jared/PriceChecker/Model/PriceChecker.php file, then run the following commands:

1) bin/magento setup:upgrade
2) bin/magento setup:di:compile
3) bin/magento setup:static-content:deploy

With the module installed, each product will have the fields we talked about earlier added, and the ability to compare and get competitors pricing built into the backend menu.

# Warranty / License

This code follows the MIT License, and there is no warranty (implied or otherwise). Do not bother me with your problems; issues will be fixed if they are relevant to me and I deem them worth correcting. 
