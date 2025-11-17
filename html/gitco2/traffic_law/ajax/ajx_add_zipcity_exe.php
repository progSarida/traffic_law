<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$answer = "";
$city_id = $_POST['city_id'];
$toponym = $_POST['toponym_id'];
$title = strtoupper($_POST['title']);
$zip = $_POST['first_zip'];
//$strada = $_POST['strada'];
$separate = explode(',', $toponym);
$toponym_id = $separate[0];
$toponymname = $separate[1];
$streetname = strtoupper($toponymname . ' ' . $title);
$nr = strlen($zip);
$message = "";



if ($nr > 5) {
    $answer = "NO";
    $message = "Il codice postale non può essere più di 6 numeri!";
} else {
    
    $rs->Start_Transaction();
    
    $a_ZipCity = array(
        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $city_id),
        array('field' => 'StreetName', 'selector' => 'value', 'type' => 'str', 'value' => $streetname),
        array('field' => 'ToponymId', 'selector' => 'value', 'type' => 'int', 'value' => $toponym_id, 'settype' => 'int'),
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => $title),
        array('field' => 'Num_Civici', 'selector' => 'value', 'type' => 'str', 'value' => ''),
        array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $zip),
        //array('field' => 'Strada', 'selector' => 'value', 'type' => 'str', 'value' => $strada),
    );
    $insert = $rs->Insert('sarida.ZIPCity', $a_ZipCity);
    
    if (isset($_POST['type'])) {
        $type = $_POST['type'];
        $bad_number = false;
        $from =$_POST['from'];
        $to=$_POST['to'];
        $nero = $_POST['nero'];
        $nr = count($type);
        $zip = $_POST['zip'];
        
        for ($i = 0; $i < $nr; $i++) {
            
            $first[$i] = explode("/",$from[$i]);
            $second[$i]= explode("/",$to[$i]);
            
            $a_ZipAdress = array(
                array('field' => 'ZIPCityId', 'selector' => 'value', 'type' => 'int', 'value' => $insert, 'settype' => 'int'),
                array('field' => 'GroupTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $type[$i], 'settype' => 'int'),
                array('field' => 'ZIP', 'selector' => 'value', 'type' => 'int', 'value' => $zip[$i], 'settype' => 'int'),
                array('field' => 'FromNumber', 'selector' => 'value', 'type' => 'int', 'value' => $first[$i][0], 'settype' => 'int'),
                array('field' => 'FromNumberLetter', 'selector' => 'value', 'type' => 'str', 'value' =>isset( $first[$i][1]) ? $first[$i][1] : NULL),
                array('field' => 'ToNumber', 'selector' => 'value', 'type' => 'int', 'value' => $second[$i][0], 'settype' => 'int'),
                array('field' => 'ToNumberLetter', 'selector' => 'value', 'type' => 'str', 'value' => isset( $second[$i][1]) ? $second[$i][1] : NULL),
                array('field' => 'NumberType', 'selector' => 'value', 'type' => 'int', 'value' => $nero[$i], 'settype' => 'int'),
            );
            
            $rs->Insert('sarida.ZIPAddress', $a_ZipAdress);
        }
        
    }
    
    $answer = "OK";
    
    $rs->End_Transaction();
    
}

echo json_encode(
    array(
        "Message" => $message,
        "Answer" => $answer,
    )
);
