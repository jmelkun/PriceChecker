<?php

namespace Jared\PriceChecker\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class InstallSchema implements InstallSchemaInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // Adding Competitor Price attribute
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'competitor_price',
            [
                'type' => 'decimal',
                'label' => 'Competitor Price',
                'input' => 'price',
                'required' => false,
                'sort_order' => 50,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'used_in_product_listing' => true,
                'visible_on_front' => false,
                'apply_to' => '',
                'group' => 'General',
            ]
        );

        // Adding Competitor URL attribute
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'competitor_url',
            [
                'type' => 'varchar',
                'label' => 'Competitor URL',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
                'is_visible' => true,
                'is_used_in_grid' => true
            ]
        );

        // Adding Price Snippet attribute
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'price_snippet',
            [
                'type' => 'varchar',
                'label' => 'Price Regex',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
                'default' => 'default_price_snippet',
                'is_visible' => true,
                'is_used_in_grid' => true
            ]
        );

        $setup->endSetup();
    }
}
