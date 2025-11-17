<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");
require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
ini_set('max_execution_time', 0);

$path = PUBLIC_FOLDER ."/_VIOLATION_/U480/";
$ftp_server = 'https://webcps-1180730805w.selea.network:8999/';
$ftp_username = 'pietropastorino';
$ftp_userpass = 'Nico13031959';

$b_Error = false;

echo "Login FTP...<br />";
$ftp_conn = ftp_connect($ftp_server) or die("Impossibile connettersi al server $ftp_server"."Contattare gli amministratori di sistema.");
$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass) or die("Login fallito su $ftp_server" ."Contattare gli amministratori di sistema.");
ftp_pasv($ftp_conn, true);
$timeout = ftp_get_option($ftp_conn, FTP_TIMEOUT_SEC);
$r=ftp_set_option($ftp_conn, FTP_TIMEOUT_SEC, 50000);
$timeout = ftp_get_option($ftp_conn, FTP_TIMEOUT_SEC);
echo "TIMEOUT = $timeout \n". "Attendere prego.";

echo "<br /><br />In collegamento con la cartella sarida...";
if (ftp_chdir($ftp_conn, "sarida/eventi")) {
    echo "OK...<br />";
    $files = ftp_nlist($ftp_conn, ".");
    if(!$files)
	    die("Il file per l'importazione non è presente sul server. Non è possibile proseguire con la procedura di trasferimento alle cartelle per l'importazione Kria. Elaborazione terminata.");
    foreach ($files as $file) {
        if (strtolower(substr($file, 0, 7)) == "export_" && ! $b_Error) {
            $local_file = str_replace("export", "import", strtolower($file));
            $server_file = $file;
            echo "<br /><br />copia in corso " . $server_file . "....";

            // download server file
            if (ftp_get($ftp_conn, $path . $local_file, $server_file, FTP_BINARY)) {
                echo "OK...<br />";
                $str_Content = "File scaricato con successo in $local_file .";
            } else {
                $b_Error = true;
                $str_Content = "Errore nello scaricare $server_file.";
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
            echo "<br /><br />Unzip " . $local_file . " in corso ....";
            if(! $b_Error){
                $zip = new ZipArchive();
                $res = $zip->open($path . $local_file);
                if ($res) {
                    echo "OK...<br />";
                    $zip->extractTo($path);
                    $zip->close();
                    rename($path . "kria.csv", $path . substr($local_file,0, strlen($local_file)-3) . "csv");
                    $str_Content = "File scompattato con successo in  $local_file.";
                } else {
                    $b_Error = true;
                    $str_Content = "Problema nello scompattare il file in $local_file.";
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

                if(! $b_Error) {
                    echo "<br /><br />Cancellazione " . $server_file . "in corso....";
                    if(ftp_delete($ftp_conn, $server_file)){
                        echo "OK...<br />";
                        $str_Content = "File $server_file cancellato con successo dal server." ;
                    } else{
                        $b_Error = true;
                        $str_Content = "Il file $server_file non può essere cancellato dal server.";
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

            }

        }

    }
}


ftp_close($ftp_conn);