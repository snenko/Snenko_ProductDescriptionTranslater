<?php

class Snenko_ProductDescriptionTranslater_Model_Logstatus extends Varien_Data_Collection
{
    const CLASS_FIELD = "class";

    public function addItemValues(array $values)
    {
        $item = new Varien_Object($values);
        $this->addItem($item);
    }

    public function _toHtml($titleTable=null)
    {
        $row = array();

        // TITLE
        $cell = array();
        foreach ($this->getFirstItem()->getData() as $field=>$value) {
            if($field==self::CLASS_FIELD) continue;
            $cell[] = "<td class=\"title\" style='font-weight: bold'>{$field}</td>";
        }
        $row[] = "<tr>" . implode("", $cell) . "</tr>";

        // ROWS
        $items = $this->getItems();
        foreach ($items as $key=>$item) {
            $cell = array();
            $class = $item[self::CLASS_FIELD];
            foreach ($item->getData() as $field=>$value) {
                if($field==self::CLASS_FIELD) continue;
                $cell[] = "<td class=\"{$field}\">{$value}</td>";
            }
            $row[] = "<tr class=\"{$class}\">" . implode("", $cell) . "</tr>";
        }

        $caption = $titleTable ? "<caption>{$titleTable}</caption>" :  null;

        // TABLE
        $tableHtml = "<table class=\"logstatus\">{$caption}" . implode("", $row) . "</table>";

        return $tableHtml;
    }

    public function _toStringMessage()
    {
        $row = array();

        $items = $this->getItems();
        foreach ($items as $key=>$item) {
            $row[] = $item->getData("messages");
        }

        return implode(";", $row);
    }
}