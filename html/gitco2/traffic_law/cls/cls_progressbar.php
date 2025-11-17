<?php
class  CLS_PROGRESSBAR{
    public $total;
    
    function __construct($total) {
        $this->total = $total;
    }
    
    function writeJSON($count, $filePath, array $additionalParams = null){
        if (!file_exists($filePath)){
            $newFile = fopen($filePath, "w") or die("Unable to open file!");
            $a_Data = array(
                "Totali" => $this->total,
                "Contati" => $count
            );
            if(!empty($additionalParams)){
                $a_Data = array_merge($a_Data, $additionalParams);
            }

            fwrite($newFile, json_encode($a_Data));
            fclose($newFile);
        } else {
            $jsonString = file_get_contents($filePath);
            $a_Data = json_decode($jsonString, true);
            $a_Data['Totali'] = $this->total;
            $a_Data['Contati'] = $count;
            if(!empty($additionalParams)){
                $a_Data = array_merge($a_Data, $additionalParams);
            }
            file_put_contents($filePath, json_encode($a_Data));
        }
    }
}