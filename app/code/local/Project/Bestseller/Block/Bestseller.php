<?php

/**
 * Created by PhpStorm.
 * User: rhutterer
 * Date: 17.11.14
 * Time: 13:05
 */
class Project_Bestseller_Block_Bestseller extends Mage_Catalog_Block_Product_Abstract
{
    public function __construct()
    {
        $this->setHeader(Mage::getStoreConfig("bestseller/general/heading"));
        $this->setImageHeight((int)Mage::getStoreConfig("bestseller/general/thumbnail_height"));
        $this->setImageWidth((int)Mage::getStoreConfig("bestseller/general/thumbnail_width"));
        $this->setTimePeriod((int)Mage::getStoreConfig("bestseller/general/time_period"));
        $this->setAddToCart((bool)Mage::getStoreConfig('bestseller/general/add_to_cart'));
        $this->setWishlist((bool)Mage::getStoreConfigFlag("bestseller/general/active"));
        $this->setAddToCompare((bool)Mage::getStoreConfig("bestseller/general/add_to_compare"));
        $this->setShowPrice((bool)Mage::getStoreConfig('bestseller/general/products_price'));
    }

    public function isEnabled()
    {
        return (bool)Mage::getStoreConfig('bestseller/general/enabled');
    }


    function getBestsellerProducts()
    {
        $storeId = (int)Mage::app()->getStore()->getId();
        // Date
        $date = new Zend_Date();
        $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDate = $date->subYear(1)->getDate()->get('Y-MM-dd');
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->addPriceData()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->setPage(1, (int)Mage::getStoreConfig('bestseller/general/number_of_items'));
        $collection->getSelect()
            ->joinLeft(
                array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_yearly')),
                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
                array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )
            ->group('e.entity_id')
            ->order(array('sold_quantity DESC', 'e.created_at'));

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        Mage::log($collection->getSize());

        return $collection->load();


    }
}