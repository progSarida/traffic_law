<?php

class cls_DateTimeI
{
  private $format;
  private $time;

  public function __construct($Format,$Time = false)
  {
    $this->time = $Time;
    $this->Select_Format($Format);
  }

  public function GetDateDB($Date,$FormatNotDefault = false)
  {
    //$date1;
    try
    {
      if(!$FormatNotDefault)
      {
        if($this->time) $date1 = DateTime::createFromFormat('Y-m-d H:i:s', $this->CorrectDate($Date,'Y-m-d H:i:s'));
        else {$date1 = DateTime::createFromFormat('Y-m-d', $this->CorrectDate($Date,'DB'));}
      }
      else{
        $FormatDate = $this->Select_Format_Return($FormatNotDefault);
        $date1 = DateTime::createFromFormat($FormatDate, $this->CorrectDate($Date,$FormatNotDefault));
      }

      if($date1->format("Y")=="0001") return null;//$this->GetAllZero("DB");
      else return $date1->format($this->Select_Format_Return("DB"));
    }
    catch(Exception $ex)
    {
      return null;
    }

  }

  private function GetAllZero($Format)
  {
    $Format = $this->Select_Format_Return($Format);
    $DateNull = str_replace("Y","0000",$Format);
    $DateNull = str_replace("d","00",$DateNull);
    $DateNull = str_replace("m","00",$DateNull);

    $DateNull = str_replace("H","00",$DateNull);
    $DateNull = str_replace("i","00",$DateNull);
    $DateNull = str_replace("s","00",$DateNull);

    return $DateNull;
  }

  private function Select_Format($Format)
  {
    $hour = "";

    if($this->time) $hour = " H:i:s";
    else $hour = "";

    switch($Format)
    {
      case "IT": $this->format = "d-m-Y".$hour; break;
      case "USA": $this->format = "m-d-Y".$hour; break;
      case "DB": $this->format = "Y-m-d".$hour; break;
      default: $this->format = "d-m-Y".$hour; break;
    }
  }
  private function Select_Format_Return($Stato)
  {
    $hour = "";
    if($this->time) $hour = " H:i:s";
    else $hour = "";

    switch($Stato)
    {
      case "IT": return "d-m-Y".$hour; break;
      case "USA": return "m-d-Y".$hour; break;
      case "DB": return "Y-m-d".$hour; break;
      default: return "d-m-Y".$hour; break;
    }
  }

  private function BuildRegularExpression($Format)
  {
    $Format = str_replace(" ","",$Format);
    $RegularExpression = "/^";
    for($i=0; $i < strlen($Format) ; $i++)
    {
      switch($Format[$i])
      {
        case "Y": $RegularExpression .= "[0-9]{4}"; break;
        case "m": $RegularExpression .= "[0-9]{2}"; break;
        case "d": $RegularExpression .= "[0-9]{2}"; break;
        case "H": $RegularExpression .= "[0-9]{2}"; break;
        case "i": $RegularExpression .= "[0-9]{2}"; break;
        case "s": $RegularExpression .= "[0-9]{2}"; break;
        case "-": $RegularExpression .= "[-]{1}"; break;
        case ":": $RegularExpression .= "[:]{1}"; break;
      }
    }
    $RegularExpression .= "$/";
    return $RegularExpression;
  }

  private function CorrectDate($data,$Format)
  {
    if (strpos($data, '/')) $data = str_replace('/',"-",$data);
    if($this->time && preg_match('/^[0-9-]{10}$/',str_replace("/","-",str_replace(" ","",$data))))
    {
        $data .= " 00:00:00";
        echo "SI + TIME";
    }
    if(!$this->time && preg_match('/^[0-9]{2}[:]{1}[0-9]{2}[:]{1}[0-9]{2}$/',substr(str_replace(" ","",$data),10,18)))
    {
      $data = substr(str_replace(" ","",$data),0,10);
      echo "SI - TIME";
    }

    if($this->time)
    {
      if(!preg_match($this->BuildRegularExpression($this->Select_Format_Return($Format)),str_replace(" ","",$data))|| $this->CheckAllZero(str_replace(" ","",$data)))
      {
          $Format = $this->Select_Format_Return($Format);
          $DateError = str_replace("Y","0001",$Format);
          $DateError = str_replace("d","01",$DateError);
          $DateError = str_replace("m","01",$DateError);

          $DateError = str_replace("H","00",$DateError);
          $DateError = str_replace("i","00",$DateError);
          $DateError = str_replace("s","00",$DateError);

          return $DateError;
      }
      else return substr(str_replace(" ","",$data),0,10)." ".substr(str_replace(" ","",$data),10,18);
    }
    else
    {
      if(!preg_match($this->BuildRegularExpression($this->Select_Format_Return($Format)),str_replace(" ","",$data)) || $this->CheckAllZero(str_replace(" ","",$data)))
      {
          $Format = $this->Select_Format_Return($Format);
          $DateError = str_replace("Y","0001",$Format);
          $DateError = str_replace("d","01",$DateError);
          $DateError = str_replace("m","01",$DateError);

          return $DateError;
      }
      else return str_replace(" ","",$data);

    }
  }

  private function CheckAllZero($data)
  {
    for($i =0; $i< strlen($data); $i++)
    {
      if($data[$i] !== "0" && $data[$i] !== "-" && $data[$i] !== ":")
        return false;
    }
    return true;
  }

  public function changeFormat($Format, $Time = false)
  {
    if($Time == true) $this->time = true;
    if($Time == false) $this->time = false;
    $this->Select_Format($Format);
  }

  public function Get_DateNewFormat($date,$FormatNotDefault = false) {

    //$date1;
    try{
      if(!$FormatNotDefault)
      {
        if($this->time) $date1 = DateTime::createFromFormat('Y-m-d H:i:s', $this->CorrectDate($date,'Y-m-d H:i:s'));
        else {$date1 = DateTime::createFromFormat('Y-m-d', $this->CorrectDate($date,'DB'));}
      }
      else{
        $FormatDate = $this->Select_Format_Return($FormatNotDefault);
        $date1 = DateTime::createFromFormat($FormatDate, $this->CorrectDate($date,$FormatNotDefault));
      }
      if($date1->format("Y") == "0001") return null;
      return str_replace('-',"/",$date1->format($this->format));
    }
    catch(Exception $ex)
    {
      return null;
    }

  }
}

?>
