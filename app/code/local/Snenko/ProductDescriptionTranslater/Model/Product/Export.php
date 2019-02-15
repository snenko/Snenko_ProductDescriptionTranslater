<?php

class Snenko_ProductDescriptionTranslater_Model_Product_Export extends Snenko_ProductDescriptionTranslater_Model_Export
{
    protected $_collection;
    protected $notTranslatedProductIds;
    protected $connection;
    protected $_productIds;

    /**
     * @return mixed
     */
    public function getProductIds()
    {
        return $this->_productIds;
    }

    /**
     * @param mixed $productIds
     */
    public function setProductIds($productIds = null)
    {
        $this->_productIds = $productIds;
    }

    /**
     * @param mixed $notTranslatedProductIds
     */
    public function setNotTranslatedProductIds($notTranslatedProductIds=null)
    {
        $this->notTranslatedProductIds = $notTranslatedProductIds;
    }

    protected function getConnection()
    {
        if(!$this->connection){
            $connection = Mage::getModel("core/resource");
            $this->connection = $connection;
        }
        return $this->connection;
    }

    protected function getTableName($name)
    {
        return $this->getConnection()->getTableName($name);
    }

    protected function query($sql)
    {
        return $this->getConnection()->getConnection("core_read")->query($sql);
    }

    protected function _getAllowedByVisibilityProductIds()
    {
        $strVisibility = Mage::helper("prodestransl")->getExportVisibilityisibility();

        if($strVisibility){
            $catalogProductEntityIntTable = $this->getTableName('catalog_product_entity_int');
            $visibilityAttributeId = $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'visibility')->getId();
            $whereVisibility = "AND entity_id in (SELECT entity_id" .
                "                  FROM {$catalogProductEntityIntTable}" .
                "		          WHERE value in ({$strVisibility})" .
                "                  AND attribute_id = {$visibilityAttributeId})";
            return $whereVisibility;
        }

    }

    public function getFields()
    {
        if(!$this->fields){
            $this->fields = Mage::helper("prodestransl")->getExportFields();
        }
        return $this->fields;
    }

    protected function _getNotTranslatedProductIds($storeId)
    {
        if(!isset($this->notTranslatedProductIds[$storeId])){

            $fields = $this->getFields();

            array_walk($fields, function (&$item, $key){
                $item = "\"{$item}\"";
            });

            $eavAttributeTable = $this->getTableName('eav_attribute');
            $catalogProductEntityTextTable = $this->getTableName('catalog_product_entity_text');

            $sqlAttributeIds = "SELECT attribute_id FROM {$eavAttributeTable} WHERE attribute_code IN (" . implode(",", $fields) . ")";
            $sqlTranslated = "SELECT entity_id FROM {$catalogProductEntityTextTable} WHERE attribute_id IN( {$sqlAttributeIds}) AND store_id = {$storeId}";

            $whereVisibility = $this->_getAllowedByVisibilityProductIds();
            $sql = "SELECT entity_id FROM {$catalogProductEntityTextTable} " .
                "WHERE entity_id NOT IN ({$sqlTranslated}) {$whereVisibility} ".
                "GROUP BY entity_id";

            $result = $this->query($sql);
            $productIds= array();
            foreach ($result as $value) {
                $productIds[] = $value['entity_id'];
            }

            $this->notTranslatedProductIds[$storeId] =  $productIds;
        }

        return $this->notTranslatedProductIds[$storeId];
    }

    /**
     * @param $collection Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addFieldToCollection(&$collection)
    {
        $fields = $this->getFields();
        foreach ($fields as $attributeField) {
            $collection->addAttributeToSelect($attributeField);
        }
    }

    public function getCollection($storeId)
    {
        if(!$this->_collection){
            $helper = Mage::helper("prodestransl");

            $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('sku');

            $this->_addFieldToCollection($collection);

            $productIds = $this->getProductIds() ? :$this->_getNotTranslatedProductIds($storeId);
            if(!empty($productIds))
            {
                $collection->addFieldToFilter('entity_id',array('in'=> $productIds ));

                if(!$this->getProductIds() &&   $excludeSkus = $helper->getExportExcludeSkus()){
                    $collection->addFieldToFilter('sku',array('nin'=> $excludeSkus ));
                }

                if(!$this->getProductIds() && $helper->getExportIsIgnoreOutOfStock()){
                    Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
                }

                if(!$this->getProductIds() && $rowCount = $helper->getExportRowCount()){
                    $collection->getSelect()->limit($rowCount);
                }

                $this->_collection = $collection;
            }

        }

        return $this->_collection;
    }
}