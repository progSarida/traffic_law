<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
$rs= new CLS_DB();

$answer = "";

$rs->Start_Transaction();

if (isset($_POST['submit'])){
    $city_id = $_POST['city_id'];
    $toponym = $_POST['toponym_id'];
    $title = strtoupper($_POST['title']);
    $zip = $_POST['first_zip'];
    $strada = $_POST['strada'];
    $separate = explode(',', $toponym);
    $toponym_id = $separate[0];
    $toponymname = $separate[1];
    $streetname = strtoupper($toponymname . ' ' . $title);
    $nr = strlen($zip);
    if ($nr > 6) {
        $answer = "Il codice postale non può essere più di 6 numeri!";
    } else {
        
        
        
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
            $type = $_POST['type'];
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
                    array('field' => 'FromNumberLetter', 'selector' => 'value', 'type' => 'str', 'value' => isset( $first[$i][1]) ? $first[$i][1] : NULL),
                    array('field' => 'ToNumber', 'selector' => 'value', 'type' => 'int', 'value' => $second[$i][0], 'settype' => 'int'),
                    array('field' => 'ToNumberLetter', 'selector' => 'value', 'type' => 'str', 'value' => isset( $second[$i][1]) ? $second[$i][1] : NULL),
                    array('field' => 'NumberType', 'selector' => 'value', 'type' => 'int', 'value' => $nero[$i], 'settype' => 'int'),
                );

                $rs->Insert('sarida.ZIPAddress', $a_ZipAdress);
            }
            $answer = "Inserito con successo";

        }
        
        $rs->End_Transaction();
        
        header("Location: mgmt_zipcity.php?PageTitle=Gestione/Zipcity&answer=Inserito con successo!");
    }
}elseif(isset($_POST['edit'])){
    $ZipcityId = $_POST['Zcity_id'];
    $city_id = $_POST['city_id'];
    $toponym = $_POST['toponym_id'];
    $title = strtoupper($_POST['title']);
    $zip = $_POST['first_zip'];
    $strada = $_POST['strada'];
    $separate = explode(',', $toponym);
    $toponym_id = $separate[0];
    $toponymname = $separate[1];
    $streetname = strtoupper($toponymname . ' ' . $title);
    
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
    $update = $rs->Update('sarida.ZIPCity', $a_ZipCity,"Id='".$ZipcityId."'");

    if (isset($_POST['type'])&& isset($_POST['from'])&& isset($_POST['zip'])){
        $type = $_POST['type'];
        $from = $_POST['from'];
        $to = $_POST['to'];
        $nero = $_POST['nero'];
        $zip = $_POST['zip'];
        if(isset($_POST['zipaddress_id'])) $exist = $_POST['zipaddress_id'];
        else $exist = null;
        if(isset($exist)){
            $exist = $_POST['zipaddress_id'];
            for($i=0;$i<count($exist);$i++){

                $first[$i] = explode("/",$from[$i]);
                $second[$i]= explode("/",$to[$i]);

                $a_ZipAdress = array(
                    array('field' => 'GroupTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $type[$i], 'settype' => 'int'),
                    array('field' => 'ZIP', 'selector' => 'value', 'type' => 'int', 'value' => $zip[$i], 'settype' => 'int'),
                    array('field' => 'FromNumber', 'selector' => 'value', 'type' => 'int', 'value' => $first[$i][0], 'settype' => 'int'),
                    array('field' => 'FromNumberLetter', 'selector' => 'value', 'type' => 'str', 'value' => isset( $first[$i][1]) ? $first[$i][1] : NULL),
                    array('field' => 'ToNumber', 'selector' => 'value', 'type' => 'int', 'value' => $second[$i][0], 'settype' => 'int'),
                    array('field' => 'ToNumberLetter', 'selector' => 'value', 'type' => 'str', 'value' => isset( $second[$i][1]) ? $second[$i][1] : NULL),
                    array('field' => 'NumberType', 'selector' => 'value', 'type' => 'int', 'value' => $nero[$i], 'settype' => 'int'),
                );
                $update = $rs->Update('sarida.ZIPAddress',$a_ZipAdress,"Id='".$exist[$i]."'");

            }
        }
        if($exist==null) $i=0;
        else if (count($exist)<count($type)) $i=count($exist);
            for(;$i<count($type);$i++){
                $first[$i] = explode("/",$from[$i]);
                $second[$i]= explode("/",$to[$i]);
                $a_ZipAdress = array(
                    array('field' => 'ZIPCityId', 'selector' => 'value', 'type' => 'int', 'value' => $ZipcityId, 'settype' => 'int'),
                    array('field' => 'GroupTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $type[$i], 'settype' => 'int'),
                    array('field' => 'ZIP', 'selector' => 'value', 'type' => 'int', 'value' => $zip[$i], 'settype' => 'int'),
                    array('field' => 'FromNumber', 'selector' => 'value', 'type' => 'int', 'value' => $first[$i][0], 'settype' => 'int'),
                    array('field' => 'FromNumberLetter', 'selector' => 'value', 'type' => 'str', 'value' => isset( $first[$i][1]) ? $first[$i][1] : NULL),
                    array('field' => 'ToNumber', 'selector' => 'value', 'type' => 'int', 'value' => $second[$i][0], 'settype' => 'int'),
                    array('field' => 'ToNumberLetter', 'selector' => 'value', 'type' => 'str', 'value' => isset( $second[$i][1]) ? $second[$i][1] : NULL),
                    array('field' => 'NumberType', 'selector' => 'value', 'type' => 'int', 'value' => $nero[$i], 'settype' => 'int'),
                );

                $insert =$rs->Insert('sarida.ZIPAddress',$a_ZipAdress);

            }
    }
    
    $rs->End_Transaction();
    
    header("Location: mgmt_zipcity.php?PageTitle=Gestione/Zipcity&answer=Aggiornato con successo");
}