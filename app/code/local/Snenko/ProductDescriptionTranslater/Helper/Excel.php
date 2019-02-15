<?php
class Snenko_ProductDescriptionTranslater_Helper_Excel extends Mage_Core_Helper_Abstract{

    protected $_abc = array(
        "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K",
        "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V",
        "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF"
    );

    public function getAbc()
    {
        return $this->_abc;
    }

    public function getNumber($symbol)
    {
        return array_search($symbol, $this->_abc);
    }

    public function getSymbol($index)
    {
        return $this->_abc[$index];
    }

    public function getHigestColumnBySheet(PHPExcel_Worksheet $sheet)
    {
        $result="";
        $range = $sheet->rangeToArray('A1:AF1',null, true, false);
        foreach ($range[0] as $key=>$item) {
            if($item==null){
                if($key==0){
                    return false;
                }else{
                    return $this->getSymbol($key-1);
                }
            }

        }
        return $result;
    }

    public function getTitle(PHPExcel_Worksheet $sheet)
    {
        $higestColumn = $this->getHigestColumnBySheet($sheet);
        $range = $sheet->rangeToArray("A1:{$higestColumn}1",null, true, false);
        if(count($range)>0){
            return $range[0];
        }
        return false;
    }

    public function getRows(PHPExcel_Worksheet $sheet, $titles, $startRow=2, $startCol="A")
    {
        $rows = array();
        $highestRow = $sheet->getHighestRow();
        $higestColumn = $this->getSymbol(count($titles)-1);
        $range = $sheet->rangeToArray("{$startCol}{$startRow}:{$higestColumn}{$highestRow}",null, true, false);
        if(count($range)>0){
            foreach ($range as $key=>$item) {
                foreach ($titles as $keyTitle=>$title) {
                    $rows[$key][$title] = $item[$keyTitle];
                    $r=1;
                }
            }
            return $rows;
        }
        return false;
    }
}