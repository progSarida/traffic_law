<?php
class PhpFTP implements PhpFTPInterface
{
    private $connection;
    private $host;
    private $username;
    private $password;
    private $port;
    private $fullHost;
    private $errors = array();
    
    public function __construct($host, $username, $password, $port = null)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->fullHost = "$username@$host".(isset($port) ? ":$port" : '');
    }
    
    public function handle_error($message, ...$messageArgs)
    {
        $messageArgs = array_filter($messageArgs, function($v){return !is_null($v) && $v !== '';});
        $expectedArgs = preg_match_all('~[^%]%[bcdeEfFgGosuxX]~', $message);
        $messageArgs = array_pad($messageArgs, $expectedArgs, '?');
        
        $fullError = $this->fullHost." -> ".(vsprintf($message, $messageArgs) ?: $message);
        
        $this->errors[] = $fullError;
        trigger_error("<PhpFTP> ERRORE -> $fullError", E_USER_WARNING);
    }
    
    public function errors()
    {
        return $this->errors;
    }
    
    public function connection_check()
    {
        return is_resource($this->connection);
    }
    
    public function connect()
    {
        error_clear_last();
        
        if(!is_resource($this->connection = ftp_connect($this->host, $this->port, 10))){
            $this->handle_error(PhpFTPInterface::ERR_CONN, error_get_last()['message']);
            return false;
        }
        if(@!ftp_login($this->connection , $this->username, $this->password)){
            $this->handle_error(PhpFTPInterface::ERR_CONN_LOGIN, error_get_last()['message']);
            return false;
        }
        if(@!ftp_pasv($this->connection , true)){
            $this->handle_error(PhpFTPInterface::ERR_CONN_PASSIVE, error_get_last()['message']);
            return false;
        }
        
        return true;
    }
    
    public function disconnect()
    {
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(!@ftp_close($this->connection)){
            $this->handle_error(PhpFTPInterface::ERR_DSCN, error_get_last()['message']);
            return false;
        } else {
            $this->connection = null;
            return true;
        }
    }
    
    public function make_dir($dirToMake){
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(@ftp_mkdir($this->connection, $dirToMake)){
            $this->handle_error(PhpFTPInterface::ERR_MKD, $dirToMake, error_get_last()['message']);
            return false;
        } else return true;
    }
    
    public function dir_list($dirToList = '.'){
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(!$dirList = @ftp_nlist($this->connection, $dirToList)){
            $this->handle_error(PhpFTPInterface::ERR_LST, $dirToList, error_get_last()['message']);
            return false;
        } else return $dirList;
    }
    
    public function remote_filemtime($pathToFile){
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(!@ftp_mdtm($this->connection, $pathToFile)){
            $this->handle_error(PhpFTPInterface::ERR_MTM, $pathToFile, error_get_last()['message']);
            return false;
        } else return true;
    }
    
    public function put($localFile, $remoteFile)
    {
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(@!ftp_put($this->connection, $remoteFile, $localFile, FTP_BINARY)){
            $this->handle_error(PhpFTPInterface::ERR_PUT, $localFile, $remoteFile, error_get_last()['message']);
            return false;
        } else return true;
    }
    
    public function get($localFile, $remoteFile)
    {
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(@!ftp_get($this->connection, $localFile, $remoteFile, FTP_BINARY)){
            $this->handle_error(PhpFTPInterface::ERR_GET, $localFile, $remoteFile, error_get_last()['message']);
            return false;
        } else return false;
    }
    
    public function rename($oldName, $newName)
    {
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(@!ftp_rename($this->connection, $oldName, $newName)){
            $this->handle_error(PhpFTPInterface::ERR_REN, $oldName, $newName, error_get_last()['message']);
            return false;
        } else return true;
    }
    
    public function delete($fileToDelete)
    {
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(@!ftp_delete($this->connection, $fileToDelete)){
            $this->handle_error(PhpFTPInterface::ERR_DEL, $fileToDelete, error_get_last()['message']);
            return false;
        } else return true;
    }
    
    public function change_dir($dirToMoveTo)
    {
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(!@ftp_chdir($this->connection, $dirToMoveTo)){
            $this->handle_error(PhpFTPInterface::ERR_CHD, $dirToMoveTo, error_get_last()['message']);
            return false;
        } else return true;
    }
    
    public function pwd()
    {
        if(!$this->connection_check()) return false;
        
        error_clear_last();
        if(!$pwd = @ftp_pwd($this->connection)){
            $this->handle_error(PhpFTPInterface::ERR_PWD, error_get_last()['message']);
            return false;
        } else return $pwd;
    }
    
    public function file_exists($fileName)
    {
        if(!$this->connection_check()) return false;
        
        return ftp_size($this->connection, $fileName) < 0;
    }
}
