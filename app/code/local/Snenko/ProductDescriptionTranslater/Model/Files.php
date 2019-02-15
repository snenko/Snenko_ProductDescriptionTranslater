<?php

class Snenko_ProductDescriptionTranslater_Model_Files extends Snenko_ProductDescriptionTranslater_Model_Log
{
    protected $_catalogImported = "";
    protected $_catalogImport = "";
    protected $_catalogExport = "";

    protected $_fileMask = "products{store}-{datetime}{extension}";


    public function getFilenameByMask($fileMask=null, $extension="", $store=null)
    {
        $currDatetime = Mage::getModel('core/date')->date('Y-m-d-H-i-s');
        if($store){$store = "-".$store;}

        $fileMask = $fileMask?:$this->_fileMask;
        $fileMask = str_replace("{datetime}", $currDatetime, $fileMask);
        $fileMask = str_replace("{extension}", ".".$extension, $fileMask);
        $fileMask = str_replace("{store}", $store, $fileMask);

        return $fileMask;
    }

    public function createDir($dir, $isBaseDir=true)
    {
        $baseDir = $isBaseDir ? Mage::getBaseDir() . DS : "";
        $fullDir = $baseDir . $dir;
        if(!is_dir($fullDir)) {
            try {
                mkdir($fullDir, 0777, true);
            } catch(Exception $e){
                $this->logException($e->getMessage());
                return false;
            }
        }
        return $fullDir;
    }

    public function moveFile($filename, $dirFrom, $dirTo)
    {
        $fileFrom = $dirFrom . DS . $filename;
        $fileTo = $dirTo . DS . $this->_fileMask;

        try {
            rename($fileFrom, $fileTo);
            return true;
//            $this->log(" [{$fileFrom}] file was moved to [{$destinationFile}]");
        } catch(Exception $e){
            $this->logException($e->getMessage());
        }

        return false;
    }

    public function getFileNamesByDir($dir)
    {
        $files = $this->getFilesByDir($dir);
        $baseDir = Mage::getBaseDir() . DS . $dir . DS;

        $fileNames = array();


        foreach ($files as $file) {
            $fileNames[] = $baseDir . $file;
        }

        return $fileNames;
    }

    public function getFilesByDir($dir)
    {
        $files = array();
        $baseDir = Mage::getBaseDir() . DS . $dir;

        $allFiels = scandir($dir);
        foreach ($allFiels as $file) {
            if($file !="." && $file != ".." && is_file($dir . DS .$file)){
                $files[] = $file;
            }
        }

        return $files;
    }

    function getExtension($filename) {
        $path_info = pathinfo($filename);
        return $path_info['extension'];
    }
}