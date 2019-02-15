<?php

class Snenko_ProductDescriptionTranslater_Model_Export extends Snenko_ProductDescriptionTranslater_Model_Log
{
    protected $storeId;
    protected $fields;

    protected $_logstatus;
    protected $_isController;

    /**
     * @return false|Mage_Core_Model_Abstract|Snenko_ProductDescriptionTranslater_Model_Logstatus
     */
    public function getLogstatus()
    {
        return $this->_logstatus;
    }

    public function __construct()
    {
        $this->_logstatus = Mage::getModel("prodestransl/logstatus");
    }

    /** @var Mage_Catalog_Model_Product $collection*/
    protected $collection;

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getCollection($storeId)
    {
        return $this->collection[$storeId];
    }

    /**
     * @param Mage_Catalog_Model_Product $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId)
    {
        $this->messages="";
        $this->_collection=null;
        $this->notTranslatedProductIds = null;
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @param mixed $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Get the systems temporary directory.
     *
     * @return string
     */
    public static function sys_get_temp_dir()
    {
        if (self::$useUploadTempDirectory) {
            //  use upload-directory when defined to allow running on environments having very restricted
            //      open_basedir configs
            if (ini_get('upload_tmp_dir') !== false) {
                if ($temp = ini_get('upload_tmp_dir')) {
                    if (file_exists($temp)) {
                        return realpath($temp);
                    }
                }
            }
        }

        // sys_get_temp_dir is only available since PHP 5.2.1
        // http://php.net/manual/en/function.sys-get-temp-dir.php#94119
        if (!function_exists('sys_get_temp_dir')) {
            if ($temp = getenv('TMP')) {
                if ((!empty($temp)) && (file_exists($temp))) {
                    return realpath($temp);
                }
            }
            if ($temp = getenv('TEMP')) {
                if ((!empty($temp)) && (file_exists($temp))) {
                    return realpath($temp);
                }
            }
            if ($temp = getenv('TMPDIR')) {
                if ((!empty($temp)) && (file_exists($temp))) {
                    return realpath($temp);
                }
            }

            // trick for creating a file in system's temporary dir
            // without knowing the path of the system's temporary dir
            $temp = tempnam(__FILE__, '');
            if (file_exists($temp)) {
                unlink($temp);
                return realpath(dirname($temp));
            }

            return null;
        }

        // use ordinary built-in PHP function
        //    There should be no problem with the 5.2.4 Suhosin realpath() bug, because this line should only
        //        be called if we're running 5.2.1 or earlier
        return realpath(sys_get_temp_dir());
    }

    public function saveXml()
    {
        $result = false;
        $logstatusValues = array();
        if ($filename = $this->getFilename()) {

            $isUseSku = Mage::helper("prodestransl")->isExportUseSku();
            $storeCode = Mage::getSingleton("core/store")->load($this->getStoreId())->getCode();

            $collection = $this->getCollection($this->getStoreId());
            $xmlProducts = new SimpleXMLElement("<?xml version='1.0' ?>\n" . "<products></products>");
            foreach ($collection as $product) {
                $xmlProduct = $xmlProducts->addChild('product');

                if ($isUseSku) {
                    $xmlProduct->addAttribute('sku', $product->getSku());
                } else {
                    $xmlProduct->addAttribute('id', $product->getId());
                }

                $xmlProduct->addAttribute('store', $storeCode);
                foreach ($this->fields as $attribute) {
                    $valueStr = htmlspecialchars($product->getData($attribute));
                    $valueStr = str_replace('&nbsp;', '', $valueStr);

                    $xmlProduct->addChild($attribute, $valueStr);
                }
            }

            try {
                $logstatusValues = array(
                    'filename' => $filename,
                    'store' => $storeCode,
                    'fields' => implode(",", $this->fields),
                    'count product' => count($collection),
                    "status" => "updated",
                    "message" => "saved XML",
                    Snenko_ProductDescriptionTranslater_Model_Logstatus::CLASS_FIELD => "updated",
                );



                if($this->_isController){
                    $result = $xmlProducts->saveXML($filename);
                    $basename = basename($filename);

                    // Будем передавать PDF
                    header("Content-Type: application/xml; charset=utf-8");

                    // Он будет называться downloaded.pdf
                    header('Content-Disposition: attachment; filename="'.$basename.'"');

                    // Исходный PDF-файл original.pdf
                    readfile($filename);
                    // run export via
//                    header('Content-Type: application/vnd.ms-excel');
//                    header("Content-Disposition: attachment;filename={$filename}");
//                    header('Cache-Control: max-age=0');
//                    $objWriter->save('php://output');
                    exit();
                }else{
                    $result = $xmlProducts->saveXML($filename);
                }

                $this->log(
                    Mage::helper("prodestransl")->__("Saved Xml file[{$filename}] with " . count($collection) . " products.")
                );
            } catch (Exception $e) {
                $logstatusValues["message"] = $e->getMessage();
                $logstatusValues["status"] = $e->getMessage();
                $this->logException($e->getMessage());
                Mage::LogException($e);
            }

            $logstatusValues["messages"] = $this->_toHTMLMessages();
        }
        $this->getLogstatus()->addItemValues($logstatusValues);
        return $result;
    }

    public function saveExcel()
    {
        $result = false;
        $logstatusValues=array();
        if($filename = $this->getFilename(null, "xlsx")){

            $isUseSku = Mage::helper("prodestransl")->isExportUseSku();
            $storeCode = Mage::getSingleton("core/store")->load($this->getStoreId())->getCode();

            $collection = $this->getCollection($this->getStoreId());

            require_once(Mage::getBaseDir("lib") . DS . "xl-reader" . DS . "PHPExcel.php");

            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            $rowCount = 1;

            // TITLE
            $sheet->SetCellValue('A'.$rowCount, 'store');
            $sheet->SetCellValue('B'.$rowCount, $isUseSku?'sku':'id');
            foreach ($this->fields as $key=>$attribute) {
                $colSymbol = Mage::helper("prodestransl/excel")->getSymbol(2+$key);
                $sheet->SetCellValue( $colSymbol.$rowCount, $attribute);

            }

            // ROWS
            $rowCount = 2;
            foreach ($collection as $product) {
                $sheet->SetCellValue('A' . $rowCount, $storeCode);
                $sheet->SetCellValue('B' . $rowCount, $isUseSku ? $product->getSku() : $product->getId());
                foreach ($this->fields as $key=>$attribute) {
                    $colSymbol = Mage::helper("prodestransl/excel")->getSymbol(2+$key);

                    $valueStr =  htmlspecialchars($product->getData($attribute));
                    $valueStr  = str_replace('&nbsp;', '', $valueStr);
                    $sheet->SetCellValue( $colSymbol.$rowCount, $valueStr);
                }
                $rowCount++;
            }

            try
            {
                $logstatusValues = array(
                    'filename' => $filename,
                    'store' => $storeCode,
                    'fields' => implode(",", $this->fields),
                    'count product' => count($collection),
                    "status" => "updated",
                    "message" => "saved EXCEL",
                    Snenko_ProductDescriptionTranslater_Model_Logstatus::CLASS_FIELD => "updated",
                );

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

                if($this->_isController){
                    $filename = basename($filename);
                    // run export via
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment;filename={$filename}");
                    header('Cache-Control: max-age=0');
                    $objWriter->save('php://output');
                    exit();
                }else{
                    $objWriter->save($filename);
                }

                $this->log(
                    Mage::helper("prodestransl")->__("Saved Xml file[{$filename}] with ".count($collection)." products.")
                );
//                return true;
            } catch(Exception $e){
                $logstatusValues["message"] = $e->getMessage();
                $logstatusValues["status"] = $e->getMessage();
                $this->logException($e->getMessage());
                Mage::LogException($e);
            }

            $logstatusValues["messages"] = $this->_toHTMLMessages();
        }

        $this->getLogstatus()->addItemValues($logstatusValues);

        return $result;
    }

    /**
     * @param mixed $isController
     */
    public function setIsController($isController)
    {
        $this->_isController = $isController;
    }
    
    protected function getFilename($fileMask = null, $extension = "xml")
    {
        $filename = "";
        $files = Mage::getSingleton("prodestransl/files");

        if( $fullDir = $files->createDir(Mage::helper("prodestransl")->getExportCatalog()) ){

            $store = $this->getStoreId()? Mage::app()->getStore($this->getStoreId())->getCode():null;

            $filename = $fullDir . "/" .  $files->getFilenameByMask($fileMask, $extension, $store);
        }
        if($files->getMessages()){
            $this->addMessage($files->getMessages());
        }
        return $filename;
    }
}