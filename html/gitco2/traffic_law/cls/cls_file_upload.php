<?php
class cls_file_upload{
    
    const MSG_UPLOAD_ERR_INI_SIZE = 'Le dimensioni del file caricato eccedono la direttiva upload_max_filesize in php.ini';
    const MSG_UPLOAD_ERR_FORM_SIZE = 'Le dimensioni del file caricato eccedono la direttiva MAX_FILE_SIZE specificata nel form HTML';
    const MSG_UPLOAD_ERR_PARTIAL = 'Il file è stato caricato solo parzialmente';
    const MSG_UPLOAD_ERR_NO_FILE = 'Non è stato caricato alcun file';
    const MSG_UPLOAD_ERR_NO_TMP_DIR = 'Cartella temporanea mancante';
    const MSG_UPLOAD_ERR_CANT_WRITE = 'Scrittura su disco fallita';
    const MSG_UPLOAD_ERR_EXTENSION = 'Un\' estensione PHP ha bloccato il caricamento';
    const MSG_UPLOAD_ERR_UNKNOWN = 'Errore sconosciuto';

    //default settings
    private $destination;
    private $fileName;
    private $maxSize; // megabytes (1 meg = 1048576 bytes)
    private $allowedExtensions; // mime types
    private $printError = TRUE;
    public $error = '';
   
    //START: Functions to Change Default Settings
    public function setDestination($newDestination) {
      $this->destination = $newDestination;
    }
    public function setFileName($newFileName) {
      $this->fileName = $newFileName;
    }
    public function getFileName($newFileName) {
        return $this->fileName;
    }
    public function setPrintError($newValue) {
      $this->printError = $newValue;
    }
    public function setMaxSize($newSize) {
        $this->maxSize = $newSize * 1048576;
    }
    public function setAllowedExtensions($newExtensions) {
      if (is_array($newExtensions)) {
        $this->allowedExtensions = $newExtensions;
      }
      else {
        $this->allowedExtensions = array($newExtensions);
      }
    }
    //END: Functions to Change Default Settings
   
    //START: Process File Functions
    public function upload($file) {
   
      //$this->validate($file);
   
      if ($this->error) {
        if ($this->printError) print $this->error;
      }
      else {
          move_uploaded_file($file['tmp_name'], $this->destination.$this->fileName) or $this->error .= 'Problemi con i permessi della cartella di destinazione.';
        if ($this->error && $this->printError) print $this->error;
      }
    }
    public function delete($file) {
   
      if (file_exists($file)) {
        unlink($file) or $this->error .= 'Problemi con i permessi della cartella di destinazione.';
      }
      else {
        $this->error .= 'File non trovato! Impossibile eliminare: '.$file;
      }
   
      if ($this->error && $this->printError) print $this->error;
    }
    //END: Process File Functions
   
    //START: Helper Functions
    public function validate($file) {
   
      $error = '';
   
      //check file exist
      if (empty($file['name'])) {
          $error .= 'File non trovato.';
          $this->error = $error;
          return;
      }
      //check allowed extensions
      if (!in_array($this->getExtension($file),$this->allowedExtensions)) {
          $error .= 'Estensione non consentita.';
          $this->error = $error;
          return;
      }
      
      //fix the file extension based on the mime type
      if (!empty($this->fixExtension($this->getExtension($file)))){
          $this->fileName = $this->fixExtension($this->getExtension($file));
      }
      
      //check file size
      if ($file['size'] > $this->maxSize) {
          $error .= 'Le dimensioni superano il limite consentito.';
          $this->error = $error;
          return;
      }
   
      $this->error = $error;
    }
    
    public function getExtension($file) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $ext = finfo_file($finfo, $file['tmp_name']);
      finfo_close($finfo);
      return $ext;
    }
    
    public function fixExtension($mime) {
        $fileToRename = pathinfo($this->fileName);
        if ($mime != $this->allowedExtensions['.' . $fileToRename['extension']]) {
            return $fileToRename['filename'] . array_search($mime, $this->allowedExtensions);
        }
    }
    
    public function checkUploadErrors($code)
    {
        switch ($code) {
            case UPLOAD_ERR_OK:
                return true;
            case UPLOAD_ERR_INI_SIZE:
                $this->error = self::MSG_UPLOAD_ERR_INI_SIZE;
                return false;
            case UPLOAD_ERR_FORM_SIZE:
                $this->error = self::MSG_UPLOAD_ERR_FORM_SIZE;
                return false;
            case UPLOAD_ERR_PARTIAL:
                $this->error = self::MSG_UPLOAD_ERR_PARTIAL;
                return false;
            case UPLOAD_ERR_NO_FILE:
                $this->error = self::MSG_UPLOAD_ERR_NO_FILE;
                return false;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->error = self::MSG_UPLOAD_ERR_NO_TMP_DIR;
                return false;
            case UPLOAD_ERR_CANT_WRITE:
                $this->error = self::MSG_UPLOAD_ERR_CANT_WRITE;
                return false;
            case UPLOAD_ERR_EXTENSION:
                $this->error = self::MSG_UPLOAD_ERR_EXTENSION;
                return false;
            default:
                $this->error = self::MSG_UPLOAD_ERR_UNKNOWN;
                return false;
        }
    }
    //END: Helper Functions
   
  }