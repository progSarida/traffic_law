<?php

class LOG
{
  private $name;
  private $path;
  private $CompletePath;
  private $dimension;

    public function __construct($name="logFile",$dimension = 10,$path=null)
    {
        //inizializzazione della proprietà $name
        $this->name = $name.".log";
        $this->dimension = $dimension;

        if($path == null)
        {
          //$docRoot = explode("/",$_SERVER['DOCUMENT_ROOT']);
          //$pageRoot = explode("\\",__DIR__);
          $this->path = __DIR__."/";
        }
        $this->CompletePath = $this->path.$this->name;

        if(!file_exists($this->CompletePath)){
          $file = fopen($this->CompletePath, "w");
          fclose($file);
        }
    }

    public function info($message){

      $this->checkDimension();

      $myfile = fopen($this->CompletePath, "a") or die("Unable to open file!");

      $txt = "-- INFO [".date('d/m/Y H:i:s')."]\nPage -> ".$_SERVER['SCRIPT_FILENAME'];
      if(isset(debug_backtrace()[1]['class']))
        $txt .= "\nClass -> ".debug_backtrace()[1]['class'];
      if(isset(debug_backtrace()[1]['function']))
        $txt .= "\nFunction -> ".debug_backtrace()[1]['function'];
      $txt .= "\nMessage-> ".$message."\n";

      fwrite($myfile, "\n". $txt);
      fclose($myfile);
    }

    public function warning($message){

        $this->checkDimension();

        $myfile = fopen($this->CompletePath, "a") or die("Unable to open file!");

        $txt = "-- WARNING [".date('d/m/Y H:i:s')."]\nPage -> ".$_SERVER['SCRIPT_FILENAME'];
        if(isset(debug_backtrace()[1]['class']))
          $txt .= "\nClass -> ".debug_backtrace()[1]['class'];
        if(isset(debug_backtrace()[1]['function']))
          $txt .= "\nFunction -> ".debug_backtrace()[1]['function'];
        $txt .= "\nMessage-> ".$message."\n";

        fwrite($myfile, "\n". $txt);
        fclose($myfile);
    }

    public function error($message){

        $this->checkDimension();

        $myfile = fopen($this->CompletePath, "a") or die("Unable to open file!");

        $txt = "-- ERROR [".date('d/m/Y H:i:s')."]\nPage -> ".$_SERVER['SCRIPT_FILENAME'];
        if(isset(debug_backtrace()[1]['class']))
          $txt .= "\nClass -> ".debug_backtrace()[1]['class'];
        if(isset(debug_backtrace()[1]['function']))
          $txt .= "\nFunction -> ".debug_backtrace()[1]['function'];
        $txt .= "\nMessage-> ".$message."\n";

        fwrite($myfile, "\n". $txt);
        fclose($myfile);
    }

    private function checkDimension()
    {
      $res = $this->formatSizeUnits(filesize($this->CompletePath));

      if($res["unit"] == "MB")
      {
          if($res["number"] > $this->dimension)
          {
              $text = file_get_contents($this->CompletePath);
              $text = substr($text,1048576);
              $text = substr($text,strpos($text,"--"));

              $myfile = fopen($this->CompletePath, "w") or die("Unable to open file!");
              fwrite($myfile, $text);
              fclose($myfile);
          }
      }
    }

    private function formatSizeUnits($bytes)
    {
        $return = array();
        if ($bytes >= 1073741824)
        {
            $return["number"] = number_format($bytes / 1073741824, 2);
            $return["unit"] = "GB";
        }
        elseif ($bytes >= 1048576)
        {
            //$bytes = number_format($bytes / 1048576, 2) . ' MB';
            $return["number"] = number_format($bytes / 1048576, 2);
            $return["unit"] = "MB";
        }
        elseif ($bytes >= 1024)
        {
            //$bytes = number_format($bytes / 1024, 2) . ' KB';
            $return["number"] = number_format($bytes / 1024, 2);
            $return["unit"] = "KB";

        }
        elseif ($bytes > 1)
        {
            //$bytes = $bytes . ' bytes';
            $return["number"] = $bytes;
            $return["unit"] = "bytes";
        }
        elseif ($bytes == 1)
        {
            //$bytes = $bytes . ' byte';
            $return["number"] = $bytes;
            $return["unit"] = "byte";
        }
        else
        {
            //$bytes = '0 bytes';
            $return["number"] = 0;
            $return["unit"] = "bytes";
        }

        return $return;
      }
}
?>
