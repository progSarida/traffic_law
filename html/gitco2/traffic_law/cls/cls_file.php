<?php

class cls_file{

    function getExtension($fileType){
        switch(strtolower($fileType)){
            case "pdf":
                $extension = "pdf";
                break;
            case "excel":
            case "xls":
                $extension = "xls";
                break;
            default:
                $extension = false;
        }
        return $extension;
    }

    function iconExtensionType($extension){
        switch(strtolower($extension)){
            case "jpg":
            case "png":
            case "jpeg":
            case "gif":
                $fileIcon = "icon_img.png";
                break;
            case "pdf":
                $fileIcon = "icon_pdf.png";
                break;
            case "txt":
                $fileIcon = "icon_txt.png";
                break;
            case "rar":
            case "zip":
                $fileIcon = "icon_rar.png";
                break;
            case "xls":
            case "xlsx":
                $fileIcon = "icon_excel.png";
                break;
            case "csv":
                $fileIcon = "icon_csv.png";
                break;
            case "doc":
            case "docx":
                $fileIcon = "icon_doc.png";
                break;
            case "xml":
                $fileIcon = "icon_xml.png";
                break;
            default:
                $fileIcon = "icon_unknown.png";
        }

        return $fileIcon;
    }

    function fileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach($arBytes as $arItem)
        {
            if($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    function folderCreation( $path )
    {
        $folder = explode("/",$path);

        $control_path = $folder[0];

        for($l=1;$l<count($folder);$l++)
        {
            $control_path .= "/".$folder[$l];
            if( is_dir( $control_path ) === false )
            {
                mkdir( $control_path );
            }
        }

        return $path;
    }

    function getWebPath ( $path )
    {
        return substr( $path , strpos( $path , "/archivio/" ));
    }

    function encryptIt( $q ) {
        $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
        $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
        return( $qEncoded );
    }

    function decryptIt( $q ) {
        $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
        $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
        return( $qDecoded );
    }

    function imageSize($imgPath, $maxWidth, $maxHeight)
    {
        $a_size = array();
        if (file_exists($imgPath)){
            $dim = getimagesize($imgPath);
            if($dim[0] > $dim[1]){
                $width = $maxWidth;
                $height = $dim[1]*($width/$dim[0]);

                if($height>$maxHeight){
                    $height = $maxHeight;
                    $width = $dim[0]*($height/$dim[1]);
                }
            }
            else if($dim[0] < $dim[1]){
                $height = $maxHeight;
                $width = $dim[0]*($height/$dim[1]);

                if($width>$maxWidth){
                    $width = $maxWidth;
                    $height = $dim[1]*($width/$dim[0]);
                }
            }
            else if($dim[0] == $dim[1]){
                if($maxWidth<$maxHeight){
                    $width = $maxWidth;
                    $height = $maxWidth;
                }
                else{
                    $width = $maxHeight;
                    $height = $maxHeight;
                }
            }

            $a_size[0] = $width;
            $a_size[1] = $height;

        }
        else{
            $cls_help = new cls_help();
            $cls_help->alert("Il file ".$imgPath." non esiste (/librerie/php/file_function.php)");
            $a_size[0] = 0;
            $a_size[1] = 0;
        }
        return $a_size;
    }

    /**
     * Cancella i file creati da un determinato numero di giorni
     * @param path string
     * percorso file
     * @param days int
     * giorni passati dall'ultima modifica del file
     */
    function removeFiles ($path, $days){
        $handle = opendir($path);

        while (($file = readdir($handle)) != false){
            if($file!="." && $file!=".."){
                $data_modifica = date('Y-m-d',filemtime($path."/".$file));
                $differenzaDate = ( strtotime (date('Y-m-d')) - strtotime ($data_modifica) ) / (60 * 60 * 24);

                if ($differenzaDate >= $days)
                    unlink ($path."/".$file);
            }
        }

        closedir($handle);
    }

    public function createArchive ($archiveFile, $fileToArchive, $a_attachments=null){
        $rarPath = $this->checkRarExe();

        if($rarPath!==false){
            $archiveFile = str_replace("Program Files (x86)", "Progra~2", $archiveFile);
            $archiveFile = str_replace("Programmi", "Progra~1", $archiveFile);
            $fileToArchive = str_replace("Program Files (x86)", "Progra~2", $fileToArchive);
            $fileToArchive = str_replace("Programmi", "Progra~1", $fileToArchive);

            $expFile = explode ("/", $fileToArchive);
            $fileName = $expFile[count($expFile)-1];
            $filePath = substr($fileToArchive, 0, -strlen($fileName));

            if(is_file($archiveFile))
                unlink($archiveFile);

            $cwd = getcwd();

            $str_zip = $rarPath . "/rar.exe a ";
            $str_zip.= $archiveFile .  " " . $fileName;

            chdir($filePath);
            exec ($str_zip);

            if(is_array($a_attachments)){
                for($i=0;$i<count($a_attachments);$i++){
                    $expFile = explode ("/", $a_attachments[$i]);
                    $fileName = $expFile[count($expFile)-1];
                    $filePath = substr($a_attachments[$i], 0, -strlen($fileName));

                    $str_zip = $rarPath . "/rar.exe a ";
                    $str_zip.= $archiveFile .  " " . $fileName;

                    chdir($filePath);
                    exec ($str_zip);

                }
            }

            chdir($cwd);

            return $fileToArchive;
        }
    }

    public function checkRarExe(){
        if (is_dir("C:/Progra~1/WinRAR"))
            return "C:/Progra~1/WinRAR";
        else if (is_dir("C:/Progra~2/WinRAR"))
            return "C:/Progra~2/WinRAR";
        else{
            $cls_help = new cls_help();
            $testo_alert = "Nel server non c'e' il programma WINRAR!!!";

            $cls_help->alert( $testo_alert );

            return false;
        }
    }

    public function getFilesFromPath($path){
        $files = scandir($path,1);
        $count = 0;
        $a_file = array();
        if(count($files)>0){
            for($i=count($files)-1;$i>=0;$i--) {
                if($files[$i]!="." && $files[$i]!=".."){
                    $fileExp = explode(".", $files[$i]);

                    $a_file[$count]['icon'] = IMG."/".$this->iconExtensionType($fileExp[count($fileExp)-1]);
                    $a_file[$count]['fileName'] = $files[$i];
                    $a_file[$count]['file'] = $path."/".$files[$i];
                    $a_file[$count]['fileWeb'] = $this->getWebPath($path)."/".$files[$i];
                    $count++;
                }

            }
        }
        return $a_file;
    }

    public function multipartFile($path){
        $a_file = array();
        if (! is_readable ( $path )) {
            throw new \Exception ( "Il file non e' leggibile!" );
        }

        $a_file['path'] = $path;
        $a_file['content_type'] = mime_content_type( $path );
        $a_file['name'] = basename( $path );

        $expPath = explode(".",$path);
        $a_file['extension'] = $expPath[count($expPath)-1];
        unset($expPath[count($expPath)-1]);
        $a_file['nameNoExt'] = implode(".",$expPath);

        return $a_file;
    }

    /**
     * @param $filePath
     * @param string $keyType   "header" prende la prima riga del csv ed utilizza le intestazioni come chiavi per l'array
     *                          "numeric" salta la prima riga del csv e utilizza un contatore numerico come chiave per l'array
     *                          null prende l'intero csv e utilizza un contatore numerico come chiave per l'array
     */
    public function getArrayFromCsv($filePath, $delimiter=";", $keyType = "header"){
        ini_set("auto_detect_line_endings", true);
        $lines = file($filePath);
        $a_header = array();
        $a_csv = array();
        foreach ($lines as $key_line => $a_line)
        {
            $a_row = str_getcsv($a_line,$delimiter);
            foreach ($a_row as $key => $value){
                if($keyType=="header"){
                    if($key_line==0){
                        $a_header[$key] = str_replace("ï»¿","",utf8_encode(trim($value)));
                    }
                    else{
                        $a_csv[$key_line-1][$a_header[$key]] = trim($value);
                    }
                }
                else if($keyType=="numeric"){
                    if($key_line>0)
                        $a_csv[$key_line-1][$key] = trim($value);
                }
                else if(is_null($keyType)){
                    $a_csv[$key_line][$key] = trim($value);
                }
            }
        }
        return $a_csv;
    }

    public function checkFileHeaderInArray($a_csv_header, $a_header){
        $a_return = null;
        foreach($a_header as $headerToSearch){
            if(array_search($headerToSearch, $a_csv_header)===false)
                $a_return[] = $headerToSearch;
        }
        return $a_return;
    }

}


?>