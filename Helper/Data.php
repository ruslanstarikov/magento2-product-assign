<?php

namespace Triple888\ProductAssign\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class Data
    extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_SKUS = 'triple888_productassign/assign/skus';
    const XML_PATH_CATEGORIES = 'triple888_productassign/assign/categories';
    const XML_PATH_INCLUDE_CONFIGURABLE = 'triple888_productassign/assign/includeConfigurable';

    const XML_PATH_DELETE_SKUS = 'triple888_productassign/delete/skus';
    const XML_PATH_DELETE_CATEGORIES = 'triple888_productassign/delete/categories';
    const XML_PATH_DELETE_INCLUDE_CONFIGURABLE = 'triple888_productassign/delete/includeConfigurable';

    protected $_productModel;
    protected $_productCollectionFactory;
    protected $_skuIdProducts;
    protected $_idSkuProducts;

    public function __construct(Context $context, Configurable $productModel, CollectionFactory $productCollectionFactory)
    {
        $this->_productModel = $productModel;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_skuIdProducts = $this->getSkuIdRelation();
        $this->_idSkuProducts = array_flip($this->_skuIdProducts);
        parent::__construct($context);
    }

    public function getSkus() : array
    {
        $skus =  $this->scopeConfig->getValue($this::XML_PATH_SKUS, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return explode(',', $skus);
    }

    public function getCategories() : array
    {
        $categories = $this->scopeConfig->getValue($this::XML_PATH_CATEGORIES, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return explode(',', $categories);
    }

    public function includeConfigurableParent() : bool
    {
        return $this->scopeConfig->getValue($this::XML_PATH_INCLUDE_CONFIGURABLE, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) == 1;
    }

    public function getSkusToDelete() : array
    {
        $skus =  $this->scopeConfig->getValue($this::XML_PATH_DELETE_SKUS, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return explode(',', $skus);
    }

    public function getCategoriesToDelete() : array
    {
        $categories = $this->scopeConfig->getValue($this::XML_PATH_DELETE_CATEGORIES, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return explode(',', $categories);
    }

    public function includeConfigurableParentToDelete() : bool
    {
        return $this->scopeConfig->getValue($this::XML_PATH_DELETE_INCLUDE_CONFIGURABLE, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) == 1;
    }

    protected function getSkuIdRelation() : array
    {
        $arrayProducts = [];
        $collection = $this->_productCollectionFactory->create()
            ->addFieldToSelect(array('entity_id, sku'));
        foreach ($collection as $item) {
            $arrayProducts[$item->getSku()] = $item->getId();
        }

        return $arrayProducts;
    }

    public function getProductId(string $sku) : string
    {
        return $this->_skuIdProducts[$sku] ?? '';
    }

    public function getProductSku(string $id) : string
    {
        return $this->_idSkuProducts[$id] ?? '';
    }

    public function getConfigurable(string $simpleProductSKU) : string
    {
        $parentSku = '';
        if($simpleProductSKU != ""){
            $productId = $this->getProductId($simpleProductSKU);
            $parentProduct = $this->_productModel->getParentIdsByChild($productId);
            if(isset($parentProduct[0]))
            {
                $parentId = $parentProduct[0];
                $parentSku = $this->getProductSku($parentId);
            }
        }

        return $parentSku;
    }
} 