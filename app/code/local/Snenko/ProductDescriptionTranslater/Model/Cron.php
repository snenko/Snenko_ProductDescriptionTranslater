<?php

class Snenko_ProductDescriptionTranslater_Model_Cron extends Snenko_ProductDescriptionTranslater_Model_Log
{

    public function import()
    {
        $dir = Mage::helper("prodestransl")->getImportCatalog();
        if(is_dir($dir)){
            $fileNames = Mage::getModel('prodestransl/files')->getFileNamesByDir($dir);
            $model = Mage::getModel('prodestransl/product_import');
            foreach ($fileNames as $fileName) {
                $result = $model->update($fileName);
            }
//            $result = $model->update();
            // reindex
//            $indexer = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
//            $indexer->reindexEverything();
        }

    }

    public function export()
    {
        $stores = Mage::helper("prodestransl")->getExportStores();
        foreach ($stores as $storeId) {
            $model = Mage::getModel('prodestransl/product_export');
            $result = $model->setStoreId($storeId)->saveXml();
//            $result = $modelProducts->setStore($storeId)->save();
            $d=1;
        }
    }

}