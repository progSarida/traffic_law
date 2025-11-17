<?php
require_once(INC.'/phpSecLib3/autoload.php');

class PhpSFTP implements PhpFTPInterface
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
        return $this->connection != null && $this->connection->isAuthenticated();
    }
    
    public function connect()
    {
        try {
            $this->connection = new \phpseclib3\Net\SFTP($this->host, $this->port);
            $login = $this->connection->login($this->username, $this->password);
        } catch (Exception $e) {
            $this->connection = null;
            $this->handle_error(PhpFTPInterface::ERR_CONN, $e->getMessage());
            return false;
        }
        
        if(!$login) {
            $this->handle_error(PhpFTPInterface::ERR_CONN_LOGIN, $this->connection->getLastSFTPError());
            return false;
        }
        
        return true;
    }
    
    public function disconnect()
    {
        if(!$this->connection_check()) return false;
        
        $this->connection->disconnect();
        $this->connection = null;
        
        return true;
    }
    
    public function make_dir($dirToMake)
    {
        if(!$this->connection_check()) return false;
        
        if(!$this->connection->mkdir($dirToMake)){
            $this->handle_error(PhpFTPInterface::ERR_MKD, $dirToMake, $this->connection->getLastSFTPError());
            return false;
        } else return true;
    }

    public function dir_list($dirToList = '.')
    {
        if(!$this->connection_check()) return false;
        
        if(!$list = $this->connection->nlist($dirToList)){
            $this->handle_error(PhpFTPInterface::ERR_LST, $dirToList, $this->connection->getLastSFTPError());
            return false;
        } else return $list;
    }

    public function remote_filemtime($pathToFile)
    {
        if(!$this->connection_check()) return false;
        
        if(!$time = $this->connection->filemtime($pathToFile)){
            $this->handle_error(PhpFTPInterface::ERR_MTM, $pathToFile, $this->connection->getLastSFTPError());
            return false;
        } else return $time;
    }

    public function put($localFile, $remoteFile)
    {
        if(!$this->connection_check()) return false;
        
        try {
            if(!$this->connection->put($remoteFile, $localFile, \phpseclib3\Net\SFTP::SOURCE_LOCAL_FILE)){
                $this->handle_error(PhpFTPInterface::ERR_PUT, $localFile, $remoteFile, $this->connection->getLastSFTPError());
                return false;
            } else return true;
        } catch (Exception $e) {
            $this->handle_error(PhpFTPInterface::ERR_PUT, $localFile, $remoteFile, $e->getMessage());
            return false;
        }
    }
    
    public function get($localFile, $remoteFile)
    {
        if(!$this->connection_check()) return false;
        
        try {
            if(!$this->connection->get($remoteFile, $localFile)){
                $this->handle_error(PhpFTPInterface::ERR_GET, $localFile, $remoteFile, $this->connection->getLastSFTPError());
                return false;
            } else return true;
        } catch (Exception $e) {
            $this->handle_error(PhpFTPInterface::ERR_GET, $localFile, $remoteFile, $e->getMessage());
            return false;
        }
    }
    
    public function rename($oldName, $newName)
    {
        if(!$this->connection_check()) return false;
        
        if(!$this->connection->rename($oldName, $newName)){
            $this->handle_error(PhpFTPInterface::ERR_REN, $oldName, $newName, $this->connection->getLastSFTPError());
            return false;
        } else return true;
    }
    
    public function delete($fileToDelete)
    {
        if(!$this->connection_check()) return false;
        
        if(!$this->connection->delete($fileToDelete)){
            $this->handle_error(PhpFTPInterface::ERR_DEL, $fileToDelete, $this->connection->getLastSFTPError());
            return false;
        } else return true;
    }

    public function change_dir($dirToMoveTo)
    {
        if(!$this->connection_check()) return false;
        
        if(!$this->connection->chdir($dirToMoveTo)){
            $this->handle_error(PhpFTPInterface::ERR_CHD, $dirToMoveTo, $this->connection->getLastSFTPError());
            return false;
        } else return true;
    }

    public function pwd()
    {
        if(!$this->connection_check()) return false;
        
        if(!$pwd = $this->connection->pwd()){
            $this->handle_error(PhpFTPInterface::ERR_PWD, $this->connection->getLastSFTPError());
            return false;
        } else return $pwd;
    }
    
    public function file_exists($fileName)
    {
        if(!$this->connection_check()) return false;
        
        return $this->connection->file_exists($fileName);
    }
}