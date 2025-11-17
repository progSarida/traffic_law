<?php
require_once(PGFN."/fn_prn_anag_anomalies.php");
/**
 * Class cls_290
 */
class cls_290
{
    public $file = "";
    public $cityCode;
    public $countRows = 0;
    public $countN2 = 0;
    public $countN4 = 0;
    public $totalAmountN4 = 0;

    public function __construct($cityCode)
    {
        $this->cityCode = $cityCode;
    }

    public function addRow($row, $rowCount=1, $endLine=1){
        $this->file.= $row;

        if($endLine==1)
            $this->file.= "\r\n";

        if($rowCount==1)
            $this->countRows++;
    }

    public function saveFile($filePath){
        $OutFile = fopen($filePath, "w");
        if(!$OutFile)
            die("Unable to open file!");

        fwrite($OutFile, $this->file);
        fclose($OutFile);
        chmod($filePath, 0777);
    }

    public function showFile(){
        echo "<pre>".$this->file."</pre>";
    }

    public function N0(){
        $row = "N0";//2
        $row.= substr($this->cityCode,1);//5
        $row.= Date("Ymd");//8
        $row.= "99";//2
        $row.= $this->addFiller(290-strlen($row) );

        $this->addRow($row);
    }

    public function N1(){
        $row = "N1";
        $row.= $this->cityCode;
        $row.= "01";
        $row.= $this->addFiller(4,"0");
        $row.= $this->addFiller(2,"0");
        $row.= "1";
        $row.= $this->addFiller(4,"0");
        $row.= "1";
        $row.= $this->addFiller(290-strlen($row) );

        $this->addRow($row);
    }

    public function N5(){

        $row = "N5";
        $row.= $this->cityCode;
        $row.= "01";
        $row.= $this->addNumberToFile(7, $this->countRows);
        $row.= $this->addNumberToFile(7, $this->countN2);
        $row.= $this->addNumberToFile(7, 0);
        $row.= $this->addNumberToFile(7, $this->countN4);
        $row.= $this->addNumberToFile(15, $this->totalAmountN4*100);

        $row.= $this->addFiller(290-strlen($row) );

        $this->addRow($row);
    }

    public function N9(){
        $this->countRows++;

        $row = "N9";//2
        $row.= substr($this->cityCode,1);//5
        $row.= $this->addNumberToFile(7, $this->countRows);
        $row.= $this->addNumberToFile(7, 1);
        $row.= $this->addNumberToFile(7, $this->countN2);
        $row.= $this->addNumberToFile(7, 0);
        $row.= $this->addNumberToFile(7, $this->countN4);
        $row.= $this->addNumberToFile(7, 1);
        $row.= $this->addFiller(290-strlen($row) );

        $this->addRow($row,0, 0);
    }

    private function setData($a_data){
        $this->a_data = $a_data;
    }

    public function N2($a_data){
        $this->setData($a_data);
        $this->countN2++;

        $row = "N2";
        $row.= $this->cityCode;
        $row.= "01";
        $row.= $this->addStringToFile(14, $this->a_data['FineId']);

        $actualGenre = checkActualGenre($this->a_data['Genre'], $this->a_data['TaxCode'], $this->a_data['VatCode']);
        
        if($actualGenre=="D")
            $row.= $this->addStringToFile(16, $this->a_data['TaxCode'],1);
        else
            $row.= $this->addStringToFile(16, $this->a_data['TaxCode']);

        $row.= $this->addStringToFile(10, $this->a_data['FineId']);
        $row.= $this->addFiller(6);
        $row.= $this->addStringToFile(30, substr($this->a_data['Address'],0,30));
        $row.= $this->addFiller(13);
        $row.= $this->addStringToFile(5, substr( trim($this->a_data['ZIP']) ,0,5));
        $row.= $this->addStringToFile(4, $this->a_data['TrespasserCityId']);

        $row.= $this->addStringToFile(21, substr( explode("(",$this->a_data['City'])[0],0,21));
        $row.= $this->addFiller(6,"0");
        $row.= $this->addFiller(30);
        $row.= $this->addFiller(5,"0");
        $row.= $this->addFiller(2);
        $row.= $this->addFiller(11,"0");
        $row.= $this->addFiller(25);

        if($actualGenre=="D"){
            $row.= "2";
            $row.= $this->addStringToFile(76, substr($this->a_data['CompanyName'].(!empty($this->a_data['LegalFormSign']) ? ' '.strtoupper($this->a_data['LegalFormSign'])  : '' ), 0, 75));
        }
        else {
            $row .= "1";
            $row.= $this->addStringToFile(24, substr($this->a_data['Surname'],0,24));
            $row.= $this->addStringToFile(20, substr($this->a_data['Name'],0,20));
            $row.= $this->a_data['Genre'];
            if(trim($this->a_data['BornDate'])!=''){
                $row.= date('d',strtotime($this->a_data['BornDate']));
                $row.= date('m',strtotime($this->a_data['BornDate']));
                $row.= date('Y',strtotime($this->a_data['BornDate']));
            }
            else
                $row.= $this->addFiller(8);

            if(trim($this->a_data['BornCountryId'])!=null)
                $row.= $this->addStringToFile(4,$this->a_data['BornCountryId']);
            else
                $row.= $this->addFiller(4);

            $row.= $this->addFiller(19);
        }

        $row.= $this->addFiller(290-strlen($row) );

        $this->addRow($row);
    }

    public function N4($code, $amount, $a_splitPayment=null){
        $this->countN4++;
        $this->totalAmountN4+= $amount;

        $row = "N4";
        $row .= $this->cityCode;
        $row .= "01";
        $row.= $this->addStringToFile(14, $this->a_data['FineId']);
        $row.= $this->addStringToFile(4, $this->a_data['ProtocolYear']);
        $row .= $this->addStringToFile(4, $code);
        $row.= $this->addFiller(13,"0");
        $row.= $this->addNumberToFile(13, $amount*100);
        $row.= $this->addFiller(2);

        $row.= date('d',strtotime($this->a_data['InterestDate']));
        $row.= date('m',strtotime($this->a_data['InterestDate']));
        $row.= date('Y',strtotime($this->a_data['InterestDate']));

        $row.= $this->addFiller(2);
        $row.= $this->addStringToFile(75, $this->a_data['InfoCartella']);
        $row.= "SVE";
        $row.= $this->addStringToFile(12, $this->a_data['FineId']);

        $row.= date('d',strtotime($this->a_data['FineDate']));
        $row.= date('m',strtotime($this->a_data['FineDate']));
        $row.= date('y',strtotime($this->a_data['FineDate']));
        $row.= $this->addStringToFile(12, $this->a_data['VehiclePlate']);

        if($code=="S_02" && $a_splitPayment!=null)
            $row.= $this->splitPaymentSarida($a_splitPayment);

        $row.= $this->addFiller(290-strlen($row) );
        $this->addRow($row);
    }

    private function splitPaymentSarida($a_splitPayment){

        $row = $this->addNumberToFile(9, $a_splitPayment['Fee']);
        $row.= $this->addFiller(9);
        $row.= $this->addNumberToFile(6, $a_splitPayment['NotificationFee']);
        $row.= $this->addNumberToFile(6, $a_splitPayment['ResearchFee']);

        return $row;
    }

    public function addFiller($fillerNumber, $char=" "){
        $filler = "";
        for($i=1;$i<=$fillerNumber;$i++) {
            $filler .= $char;
        }
        return $filler;
    }

    public function addNumberToFile($size, $number, $type=1, $pos = "R")
    {
        $a_number = explode(".", $number);
        $int = $a_number[0];
        if (isset($a_number[1]))
            $flt = (strlen($a_number[1]) == 2) ? $a_number[1] : $a_number[1] . "0";
        else
            $flt = "";

        $out_number = $int . $flt;
        if($type==0)
            return $this->addMissingSpace($size, $out_number);
        else if($type==1)
            return $this->addMissingZero($size, $out_number);
    }

    public function addStringToFile($size, $string, $type=0, $pos = "L")
    {
        if($type==0)
            return $this->addMissingSpace($size, trim($string), $pos);
        else if($type==1)
            return $this->addMissingZero($size, trim($string), $pos);
    }

    public function addMissingZero($size, $dataToInsert, $pos= "R"){
        $zeros = "";
        for ($i = 1; $i <= ($size - strlen($dataToInsert)); $i++) {
            $zeros .= "0";
        }
        if($pos=="R")
            $str_out = $zeros.$dataToInsert;
        else
            $str_out = $dataToInsert.$zeros;
        return $str_out;
    }

    public function addMissingSpace($size, $dataToInsert, $pos= "L"){
        $spaces = "";
        for ($i = 1; $i <= ($size - strlen($dataToInsert)); $i++) {
            $spaces .= " ";
        }
        if($pos=="R")
            $str_out = $spaces.$dataToInsert;
        else
            $str_out = $dataToInsert.$spaces;
        return $str_out;
    }

}