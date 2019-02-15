<?php

class Snenko_ProductDescriptionTranslater_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action
{
    public function runAction()
    {
        $result = array();
        $helper = Mage::helper("prodestransl");

        $dir = Mage::helper("prodestransl")->getImportCatalog();
        if(is_dir($dir)){

            $fileNames = Mage::getModel('prodestransl/files')->getFileNamesByDir($dir);
            $model = Mage::getSingleton('prodestransl/product_import');
            foreach ($fileNames as $key=>$fileName) {
                $res = $model->update($fileName);
            }
            $rowsCount = count($model->getItems());
            $successUpdated = $model->getSuccessUpdated();
            $errorUpdated = $model->getErrorUpdated();

            $result = array(
                'status' => "success",
                'message' => $helper->__("Import Done"),
                'messages' => $model->getLogstatus()->_toHtml($helper->__("Results: count/success/error=<b>{$rowsCount}/{$successUpdated}/{$errorUpdated}</b>")),
            );

            // TODO: тести на помилки: відсутній продукт
            // TODO: тести на помилки: відсутній store
            // TODO: REINDEX FOR TRANSTATED PRODUCTS
//            $result = $model->update();
            // reindex
//            $indexer = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
//            $indexer->reindexEverything();
        }else{
            $result = array(
                'status' => "success",
                'message' => $helper->__("dir [\"{$dir}\"] is not exist."),
                'messages' => "",
            );
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        return true;
    }
}