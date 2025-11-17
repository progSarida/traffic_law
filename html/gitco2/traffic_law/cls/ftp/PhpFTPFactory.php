<?php
require_once(CLS."/ftp/PhpFTPInterface.php");
require_once(CLS."/ftp/PhpFTP.php");
require_once(CLS."/ftp/PhpSFTP.php");

class PhpFTPFactory
{
    public static function create($connection_type, $host, $username, $password, $port = null)
    {
        switch($connection_type) {
            case 'FTP':
                $oFtp = new PhpFTP($host, $username, $password, $port);
                break;
            case 'SFTP':
                $oFtp = new PhpSFTP($host, $username, $password, $port);
                break;
            default:
                throw new Exception(
                'Nessun tipo di connessione valido impostato. Impossibile determinare il metodo di connessione.');
        }
        
        // Potential follow-up construction steps
        return $oFtp;
    }
}