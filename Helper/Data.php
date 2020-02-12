<?php

namespace Triple888\ProductAssign\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Category;

class Data
    extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_SKUS = 'triple888_productassign/assign/skus';
    const XML_PATH_CATEGORIES = 'triple888_productassign/assign/category';
    const XML_PATH_INCLUDE_CONFIGURABLE = 'triple888_productassign/assign/includeConfigurable';

    const XML_PATH_DELETE_SKUS = 'triple888_productassign/delete/skus';
    const XML_PATH_DELETE_CATEGORIES = 'triple888_productassign/delete/categories';
    const XML_PATH_DELETE_INCLUDE_CONFIGURABLE = 'triple888_productassign/delete/includeConfigurable';

    protected $_productModel;
    protected $_productCollectionFactory;
    protected $_skuIdProducts;
    protected $_idSkuProducts;
    protected $_resourceConnection;
    protected $_category;

    public function __construct(Context $context, Configurable $productModel, CollectionFactory $productCollectionFactory,
                                ResourceConnection $resourceConnection, Category $category)
    {
        $this->_productModel = $productModel;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resourceConnection = $resourceConnection;
        $this->_category = $category;
        $this->_skuIdProducts = $this->getSkuIdRelation();
        $this->_idSkuProducts = array_flip($this->_skuIdProducts);
        parent::__construct($context);
    }

    public function exist(string $categoryId) : bool
    {
        return $this->_category->load($categoryId) == true;
    }
    public function getSkus() : array
    {
        $skus =  $this->scopeConfig->getValue($this::XML_PATH_SKUS, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return explode(',', preg_replace('/\s+/','',$skus));
    }

    public function getCategory() : string
    {
        $categories = $this->scopeConfig->getValue($this::XML_PATH_CATEGORIES, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return preg_replace('/\s+/','',$categories);
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

    public function getDisabledProducts(array $SKUs) : array
    {
        $arrayProducts = [];
        $collection = $this->_productCollectionFactory->create()
            ->addFieldToSelect(array('entity_id, sku, status'))
            ->addFieldToFilter('sku', ['in' => $SKUs])
            ->addFieldToFilter('status', ProductStatus::STATUS_DISABLED);
        foreach ($collection as $item) {
            $arrayProducts[$item->getSku()] = $item->getId();
        }

        return $arrayProducts;
    }

    public function getExistentItems(array $skus) : array
    {
        return  array_intersect(array_keys($this->_skuIdProducts), $skus);
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

    public function assignProductToCategories (array $SKUs, $category) : int
    {
        $tableName = $this->_resourceConnection->getTableName('catalog_category_product');
        $data = $this->generateData($SKUs, $category);

        return  $this->_resourceConnection->getConnection()->insertOnDuplicate($tableName, $data, ['category_id', 'product_id']);
    }

    private function generateData(array $SKUs, $category) : array
    {
        $data = [];
        foreach ($SKUs as $sku) {
            $id = $this->getProductId($sku);
            if (!empty($id)) {
                $data[] = ['category_id' => $category, 'product_id' => $id, 'position' => 0];
            }
        }

        return $data;
    }
} 