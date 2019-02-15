<?php

class Snenko_ProductDescriptionTranslater_Adminhtml_ExportController extends Mage_Adminhtml_Controller_Action
{
    //TODO: реалізувати експорт через ексель
    public function runAction()
    {
        $helper = Mage::helper("prodestransl");

        $model = Mage::getModel('prodestransl/product_export');
        $stores = $helper->getExportStores();
        foreach ($stores as $storeId) {
            $model->setStoreId($storeId);

            if(Snenko_ProductDescriptionTranslater_Model_System_Config_Source_Typetoexport::TYPE_EXCEL == $helper->getTypeToExport()){
                $res = $model->saveExcel();
            }else{
                $res = $model->saveXml();
            }
        }

        $result = array(
            'status' => "success",
            'message' => $helper->__("Export Done"),
            'messages' => $model->getLogstatus()->_toHtml(),
        );

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        return true;
    }

    public function massrunAction()
    {
        $productIds = Mage::app()->getRequest()->getParam("product");

        $fields = Mage::app()->getRequest()->getParam("fields");
        $typeFile = Mage::app()->getRequest()->getParam("type_file");
        $stores = Mage::app()->getRequest()->getParam("stores");
        $stores = !is_array($stores) ? explode(",", $stores) : $stores;

        if (!$productIds) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('prodestransl')->__('Please select product(s)'));
        } elseif (!$typeFile) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('prodestransl')->__('Please select type file'));
        } elseif (!$stores) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('prodestransl')->__('Please select store(s)'));
        } else {

            $model = Mage::getModel('prodestransl/product_export');
            $model->setFields($fields);
            $model->setProductIds($productIds);
            $model->setIsController(true);

            foreach ($stores as $storeId) {
                $model->setStoreId($storeId);

                if(Snenko_ProductDescriptionTranslater_Model_System_Config_Source_Typetoexport::TYPE_EXCEL == $typeFile){
                    $res = $model->saveExcel();
                }else{
                    $res = $model->saveXml();
                }

                if($res){
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $model->getLogstatus()->_toStringMessage()
                    );
                }else{
                    Mage::getSingleton('adminhtml/session')->addError(
                        $model->getLogstatus()->_toStringMessage()
                    );
                }
            }
        }
        $this->_redirect("adminhtml/catalog_product/index");

        return true;

    }
}