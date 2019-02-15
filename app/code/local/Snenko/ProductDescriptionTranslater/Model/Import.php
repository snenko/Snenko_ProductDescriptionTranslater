<?php

class Snenko_ProductDescriptionTranslater_Model_Import extends Snenko_ProductDescriptionTranslater_Model_Log
{
    protected $items;

    const STORE_FIELD = 'store_id';
    const FILENAME_FIELD = 'file_name';
    const SKU_FIELD = 'sku';
    const STORECODE_FIELD = 'store_code';
    const IMPORT_STATUS_FIELD = 'import_status';
    const MESSAGE_FIELD = 'message';

    protected $_successUpdated = 0;
    protected $_errorUpdated = 0;

    /** @var Mage_Core_Model_Abstract|Snenko_ProductDescriptionTranslater_Model_Logstatus */
    protected $_logstatus;

    public function __construct()
    {
        $this->_logstatus = Mage::getModel("prodestransl/logstatus");
    }

    /**
     * @return false|Mage_Core_Model_Abstract|Snenko_ProductDescriptionTranslater_Model_Logstatus
     */
    public function getLogstatus()
    {
        return $this->_logstatus;
    }
    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    public function update($filename)
    {
        //$this->load($filename);
        $extension = Mage::getModel("prodestransl/files")->getExtension($filename);
        switch ($extension) {
            case "xml":
                $this->loadXml($filename);
                break;
            case "xlsx":
                $this->loadXlsx($filename);
                break;
            case "xls":
                $this->loadXlsx($filename);
                break;
            default:
                ;
                break;
        }
        $this->updateProducts();
        return true;

    }

    protected function _setImportStatus(&$importStatus, $value)
    {
        if($importStatus!="error"){
            $importStatus = $value;
        }
    }

    protected function loadXml($filename)
    {
        $xmlProducts = simplexml_load_file($filename);


        foreach ($xmlProducts->product as $xmlProduct) {
            $prodestranslLogModel = Mage::getSingleton("prodestransl/log");
            $prodestranslLogModel->cleanMessages();

            $arrProduct = (array)$xmlProduct;
            $attributes = $arrProduct['@attributes'];
            $sku = isset($attributes['sku'])?$attributes['sku']:null;
            $id = isset($attributes['id'])?$attributes['id']:null;
            $storeCode = isset($attributes['store'])?$attributes['store']:null;
            $importStatus = null;

            $store = Mage::getSingleton("prodestransl/store")->getStoreIdByCode($storeCode);

            if(!$store){
                $this->_setImportStatus($importStatus, "error");
                $prodestranslLogModel->logException("Can't find store=\"{$storeCode}\"");
            }

            if($sku){
                $id = Mage::getSingleton("catalog/product")->getIdBySku($sku);
                if(!$id){
                    $prodestranslLogModel->logException(Mage::helper("prodestransl")->__("Not find product with sku=\"{$sku}\""));
                    $id = uniqid();
                    $this->_setImportStatus($importStatus, "error");
                }
            }

            $this->items[$id] = array(
                self::STORE_FIELD => $store,
                self::STORECODE_FIELD => $storeCode,
                self::FILENAME_FIELD => basename($filename),
                self::SKU_FIELD => $sku,
                self::IMPORT_STATUS_FIELD => $importStatus
            );

            foreach ($arrProduct as $field=>$value) {
                if($field == '@attributes') {continue;}

                if(Mage::getSingleton("prodestransl/attribute")->isExist($field)){
                    $this->items[$id][$field] = htmlspecialchars_decode($value);
                }else{
                    $prodestranslLogModel->logException("Product's attribute [{$field}] not exist");
                }
            }

            $this->items[$id][self::MESSAGE_FIELD] = $prodestranslLogModel->getMessages() ? $prodestranslLogModel->_toHTMLMessages() : null;

        }
    }

    protected function loadXlsx($filename)
    {
        $helper = Mage::helper("prodestransl/excel");
        Mage::log("run {$filename}",null, 'exportproductsfortranslate.log');

        require_once(Mage::getBaseDir("lib") . "/xl-reader/PHPExcel/IOFactory.php");
        /** @var PHPExcel $objPHPExcel */

        $inputFileName = $filename;

        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $titles = $helper->getTitle($sheet);
        $xlsProducts = $helper->getRows($sheet, $titles);

        foreach ($xlsProducts as $arrProduct) {
            $prodestranslLogModel = Mage::getSingleton("prodestransl/log");
            $prodestranslLogModel->cleanMessages();

//            $attributes = $arrProduct['@attributes'];
            $sku = isset($arrProduct['sku'])?$arrProduct['sku']:null;
            $id = isset($arrProduct['id'])?$arrProduct['id']:null;
            $storeCode = isset($arrProduct['store'])?$arrProduct['store']:null;
            $importStatus = null;

            $store = Mage::getSingleton("prodestransl/store")->getStoreIdByCode($storeCode);

            if(!$store){
                $this->_setImportStatus($importStatus, "error");
                $prodestranslLogModel->logException("Can't find store=\"{$storeCode}\"");
            }

            if($sku){
                $id = Mage::getSingleton("catalog/product")->getIdBySku($sku);
                if(!$id){
                    $prodestranslLogModel->logException(Mage::helper("prodestransl")->__("Not find product with sku=\"{$sku}\""));
                    $id = uniqid();
                    $this->_setImportStatus($importStatus, "error");
                }
            }

            unset($arrProduct["id"]);
            unset($arrProduct["sku"]);
            unset($arrProduct["store"]);

            $this->items[$id] = array(
                self::STORE_FIELD => $store,
                self::STORECODE_FIELD => $storeCode,
                self::FILENAME_FIELD => basename($filename),
                self::SKU_FIELD => $sku,
                self::IMPORT_STATUS_FIELD => $importStatus
            );

            foreach ($arrProduct as $field=>$value) {
                if($field == '@attributes') {continue;}

                if(Mage::getSingleton("prodestransl/attribute")->isExist($field)){
                    $this->items[$id][$field] = htmlspecialchars_decode($value);
                }else{
                    $prodestranslLogModel->logException("Product's attribute [{$field}] not exist");
                }
            }

            $this->items[$id][self::MESSAGE_FIELD] = $prodestranslLogModel->getMessages() ? $prodestranslLogModel->_toHTMLMessages() : null;

        }

    }

    protected function updateProducts()
    {
        $helper = Mage::helper("prodestransl");
        $items = $this->getItems();

        $model = Mage::getSingleton('catalog/product_action');
        $this->_updated = 0;

        foreach ($items as $productId=>$data) {
            $prodestranslLogModel = Mage::getSingleton("prodestransl/log");
            $prodestranslLogModel->cleanMessages();

            $storeId = isset($data[self::STORE_FIELD])?$data[self::STORE_FIELD]:null;
            $importStatus = isset($data[self::IMPORT_STATUS_FIELD]) ? $data[self::IMPORT_STATUS_FIELD] : null;

            $logstatusValues = array(
                self::FILENAME_FIELD => isset($data[self::FILENAME_FIELD])?$data[self::FILENAME_FIELD]:null,
                self::SKU_FIELD => isset($data[self::SKU_FIELD])?$data[self::SKU_FIELD]:null,
                self::STORECODE_FIELD => isset($data[self::STORECODE_FIELD])?$data[self::STORECODE_FIELD]:null,
                "status" => $importStatus ? $importStatus : $helper->__("updated"),
                "message" => isset($data[self::MESSAGE_FIELD])?$data[self::MESSAGE_FIELD]: null,
                Snenko_ProductDescriptionTranslater_Model_Logstatus::CLASS_FIELD => $importStatus?$importStatus:"updated",
            );

            unset($data[self::STORE_FIELD]);
            unset($data[self::FILENAME_FIELD]);
            unset($data[self::SKU_FIELD]);
            unset($data[self::STORECODE_FIELD]);
            unset($data[self::IMPORT_STATUS_FIELD]);
            unset($data[self::MESSAGE_FIELD]);



            if ($importStatus != "error" || true) {
                try {
                    $res = Mage::getSingleton('catalog/product_action')
                        ->updateAttributes(array($productId), $data, $storeId);
                    if (!$res) {
                        $logstatusValues["status"] = $helper->__("not updated");
                        $logstatusValues[Snenko_ProductDescriptionTranslater_Model_Logstatus::CLASS_FIELD] = "not-updated";
                    }
                    $this->_successUpdated++;
                } catch (Exception $e) {
                    $logstatusValues["status"] = $helper->__("error");
                    $logstatusValues[Snenko_ProductDescriptionTranslater_Model_Logstatus::CLASS_FIELD] = "error";
                    $prodestranslLogModel->logException($e->getMessage());
                    Mage::logException($e);
                    $this->_errorUpdated++;
                }
            }else{
                $this->_errorUpdated++;
            }

            $logstatusValues["message"] .= $prodestranslLogModel->getMessages()? $prodestranslLogModel->_toHTMLMessages():null;

            $this->getLogstatus()->addItemValues($logstatusValues);
        }

        return true;
        
    }

    /**
     * @return int
     */
    public function getSuccessUpdated()
    {
        return $this->_successUpdated;
    }

    /**
     * @return int
     */
    public function getErrorUpdated()
    {
        return $this->_errorUpdated;
    }

    protected function _getStoreId(&$data)
    {
        if (!key_exists(self::STORE_FIELD, $data)) {
            return false;
        }

        $storeId = $data[self::STORE_FIELD];
        unset($data[self::STORE_FIELD]);

        return $storeId;
    }


    protected function _getFilename(&$data)
    {
        if (!key_exists(self::FILENAME_FIELD, $data)) {
            return false;
        }

        $storeId = $data[self::FILENAME_FIELD];
        unset($data[self::FILENAME_FIELD]);

        return $storeId;
    }

    public function updateProducts1()
    {
        /** @var Mage_Catalog_Model_Product_Action $model */
        $model = Mage::getSingleton('catalog/product_action');
        foreach ($this->getRows() as $id=>$data) {
            $fileName = $this->_getFilename($data);
            if($storeId = $this->_getStoreId($data)){
                try {
                    $model->updateAttributes(array($id), $data, $storeId);
                    Mage::log("updated product_id={$id}",null, 'exportproductsfortranslate-success.log');
                    echo "updated product_id={$id}";
                }catch (Exception $e) {
                    Mage::log("product_id={$id}; file={$fileName}; exception_code={$e->getCode()}; message={$e->getMessage()}",null, 'exportproductsfortranslate-error.log');
                    echo "product_id={$id}; file={$fileName}; exception_code={$e->getCode()}; message={$e->getMessage()}";
                }
            }

        }
    }

}