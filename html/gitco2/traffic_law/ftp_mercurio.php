<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');


ini_set('max_execution_time', 0);

$path = PUBLIC_FOLDER . "/E555/";
$b_Error = false;



ftp://sarida:@ftp.mercurioservice.it//20180306_SARIDA_AR_03.7z

$ftp_server = 'ftp.mercurioservice.it';
$ftp_username = 'sarida';
$ftp_userpass = '1ftp4sarida';
echo "Login FTP...<br />";


$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");

$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass) or die("Could not Login to $ftp_server");
ftp_pasv($ftp_conn, true);


$timeout = ftp_get_option($ftp_conn, FTP_TIMEOUT_SEC);
$r=ftp_set_option($ftp_conn, FTP_TIMEOUT_SEC, 50000);
$timeout = ftp_get_option($ftp_conn, FTP_TIMEOUT_SEC);
echo "TIMEOUT = $timeout \n";

echo "<br /><br />connecting to sarida folder....";
if (ftp_chdir($ftp_conn, "Recupero_Immagini/AR_EE")) {
    echo "OK...<br />";

    $files = ftp_nlist($ftp_conn, ".");


    //foreach ($files as $file) {
        if (! $b_Error) {
            //$local_file = strtolower($file);
            $local_file = "20180306_SARIDA_AR_01.7z";
            $server_file = "20180306_SARIDA_AR_01.7z";

            echo "<br /><br />copying " . $server_file . "....";

            // download server file
            if (ftp_get($ftp_conn, $path . $local_file, $server_file, FTP_BINARY)) {

                echo "OK...<br />";

                $str_Content = "Successfully downloaded to $local_file file.";

            } else {
                $b_Error = true;
                $str_Content = "Error downloading $server_file.";




            }
            $a_Mail = array(
                array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                array('field' => 'SendTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
                array('field' => 'Object', 'selector' => 'value', 'type' => 'str', 'value' => "Download KRIA file"),
                array('field' => 'Content', 'selector' => 'value', 'type' => 'str', 'value' => $str_Content),
                array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => 31, 'settype' => 'int'),
                array('field' => 'Sender', 'selector' => 'value', 'type' => 'str', 'value' => "Server"),
            );
            $rs->Start_Transaction();
            $rs->Insert('Mail', $a_Mail);
            $rs->End_Transaction();

            echo "<br /><br />unzip " . $local_file . "....";

            if(! $b_Error){
                $zip = new ZipArchive();
                $res = $zip->open($path . $local_file);

                if ($res) {
                    echo "OK...<br />";

                    $zip->extractTo($path);
                    $zip->close();

                    //rename($path . "kria.csv", $path . substr($local_file,0, strlen($local_file)-3) . "csv");
                    $str_Content = "Successfully unzip file $local_file.";

                } else {
                    $b_Error = true;
                    $str_Content = "Problem with unzip file $local_file.";


                }
                $a_Mail = array(
                    array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                    array('field' => 'SendTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
                    array('field' => 'Object', 'selector' => 'value', 'type' => 'str', 'value' => "Unzip KRIA file"),
                    array('field' => 'Content', 'selector' => 'value', 'type' => 'str', 'value' => $str_Content),
                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => 31, 'settype' => 'int'),
                    array('field' => 'Sender', 'selector' => 'value', 'type' => 'str', 'value' => "Server"),
                );
                $rs->Start_Transaction();
                $rs->Insert('Mail', $a_Mail);
                $rs->End_Transaction();

/*
                if(! $b_Error) {

                    echo "<br /><br />delete " . $server_file . "....";

                    if(ftp_delete($ftp_conn, $server_file)){
                        echo "OK...<br />";

                        $str_Content = "Successfully deleted file $server_file on the server." ;
                    } else{
                        $b_Error = true;
                        $str_Content = "Could not delete $server_file on the server.";
                    }

                    $a_Mail = array(
                        array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        array('field' => 'SendTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
                        array('field' => 'Object', 'selector' => 'value', 'type' => 'str', 'value' => "Delete KRIA file"),
                        array('field' => 'Content', 'selector' => 'value', 'type' => 'str', 'value' => $str_Content),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => 31, 'settype' => 'int'),
                        array('field' => 'Sender', 'selector' => 'value', 'type' => 'str', 'value' => "Server"),
                    );

                    $rs->Start_Transaction();
                    $rs->Insert('Mail', $a_Mail);
                    $rs->End_Transaction();
                }
*/
            }

        }

    //}
}


ftp_close($ftp_conn);

