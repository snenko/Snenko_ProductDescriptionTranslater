<?php

class Snenko_ProductDescriptionTranslater_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_EXPORT_ENABLED = 'prodestransl/export/enabled';
    const XML_EXPORT_CATALOG = 'prodestransl/export/catalog';
    const XML_EXPORT_ROW_COUNT = 'prodestransl/export/row_count';
    const XML_EXPORT_STORES = 'prodestransl/export/stores';
    const XML_EXPORT_EXCLUDE_SKUS = 'prodestransl/export/exclude_skus';
    const XML_EXPORT_VISIBILITY = 'prodestransl/export/visibility';
    const XML_EXPORT_IGNORE_OUT_OF_STOCK = 'prodestransl/export/ignore_out_of_stock';
    const XML_EXPORT_FIELDS = 'prodestransl/export/fields';
    const XML_EXPORT_USE_SKU = 'prodestransl/export/use_sku';
    const XML_EXPORT_TYPETOEXPORT = 'prodestransl/export/typetoexport';

    const XML_IMPORT_ENABLED = 'prodestransl/import/enabled';
    const XML_IMPORT_CATALOG = 'prodestransl/import/catalog';
    const XML_IMPORT_CATALOG_IMPORTED = 'prodestransl/import/catalog_imported';

    protected function _getByExplode($xmlPath)
    {
        if($value = Mage::getStoreConfig($xmlPath)){
            return explode(",", $value);
        }
        return null;

    }

    public function isExportEnabled()
    {
        return Mage::getStoreConfig(self::XML_EXPORT_ENABLED);
    }

    public function getExportCatalog()
    {
        return Mage::getStoreConfig(self::XML_EXPORT_CATALOG);
    }

    public function getExportRowCount()
    {
        return Mage::getStoreConfig(self::XML_EXPORT_ROW_COUNT);
    }

    public function getExportStores()
    {
        // TODO: по замовчуванню вибрати
        return $this->_getByExplode(self::XML_EXPORT_STORES);
    }

    public function getExportExcludeSkus()
    {
        return $this->_getByExplode(self::XML_EXPORT_EXCLUDE_SKUS);
    }

    public function getExportVisibilityisibility()
    {
        return Mage::getStoreConfig(self::XML_EXPORT_VISIBILITY);
    }

    public function getExportIsIgnoreOutOfStock()
    {
        return Mage::getStoreConfig(self::XML_EXPORT_IGNORE_OUT_OF_STOCK);
    }

    public function getExportFields()
    {
        return $this->_getByExplode(self::XML_EXPORT_FIELDS);
    }

    public function isExportUseSku()
    {
        return Mage::getStoreConfig(self::XML_EXPORT_USE_SKU);
    }

    public function getTypeToExport()
    {
        return Mage::getStoreConfig(self::XML_EXPORT_TYPETOEXPORT);
    }

    public function isImportEnabled()
    {
        return Mage::getStoreConfig(self::XML_IMPORT_ENABLED);
    }

    public function getImportCatalog()
    {
        return Mage::getStoreConfig(self::XML_IMPORT_CATALOG);
    }

    public function getImportCatalogImported()
    {
        return Mage::getStoreConfig(self::XML_IMPORT_CATALOG_IMPORTED);
    }
}