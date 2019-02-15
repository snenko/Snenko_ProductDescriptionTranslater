<?php

class Snenko_ProductDescriptionTranslater_Model_Log extends Snenko_ProductDescriptionTranslater_Model_Logstatus
{
    protected $logFilename;
    protected $logExceptionFilename;
    protected $messages;

    public function cleanMessages()
    {
        $this->messages = array();
        return $this;
    }

    public function addMessage($message = array())
    {
        if(!empty($message)){
            $this->messages = array_merge($this->messages, $message);
        }
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function _toHTMLMessages()
    {
        $result = "";
        if($this->getMessages()){
            $messages = "";
            foreach ($this->getMessages() as $text) {
                $messages[] = "<li>{$text}</li>";
            }
            $result ="<ul style=\"list-style: disc;\">" . implode("", $messages) . "</ul>";
        }
        return $result;
    }

    function getLogFilename()
    {
        if (!$this->logFilename) {
            $dayDate = Mage::getModel('core/date')->date('Y-m-d');
            $filename = "scvimport{$dayDate}.log";
            $this->logFilename = $filename;
        }
        return $this->logFilename;
    }

    function getLogSheduleFilename()
    {
        if (!$this->logExceptionFilename) {
            $dayDate = Mage::getModel('core/date')->date('Y-m-d');
            $filename = "scvimport-shedule-{$dayDate}.log";
            $this->logExceptionFilename = $filename;
        }
        return $this->logExceptionFilename;
    }

    function getLogExceptionFilename()
    {
        if (!$this->logExceptionFilename) {
            $dayDate = Mage::getModel('core/date')->date('Y-m-d');
            $filename = "scvimport-error{$dayDate}.log";
            $this->logExceptionFilename = $filename;
        }
        return $this->logExceptionFilename;
    }

    public function log($message)
    {
        $message = "message: {$message}";
//        echo"<p style=\"color:blue\" >{$message}</p>";
        $this->messages[] = "<p style=\"color:blue\" >{$message}</p>";
        Mage::log($message, null, $this->getLogFilename());
    }

    public function logException($message)
    {
//        $message = "message: {$message}";
//        echo"<p style=\"color:red\" >{$message}</p>";
        $this->messages[] = "<p style=\"color:red\" >{$message}</p>";
        Mage::log($message, null, $this->getLogExceptionFilename());
    }

    public function logShedule($message, $isError)
    {
        $message = "message: {$message}";
//        echo"<p style=\"color:red\" >{$message}</p>";
        if($isError){
            $this->messages[] = "<p style=\"color:red\" >{$message}</p>";
        }else{
            $this->messages[] = "<p style=\"color:blue\" >{$message}</p>";
        }

        Mage::log($message, null, $this->getLogSheduleFilename());
    }

}