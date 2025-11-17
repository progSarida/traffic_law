<?php

function decodeFineCommunicationStatus(int $status,array $a_LicensePointMex,$licensePointId=null): string
  {
  switch ($status)
    {
    case 0:
      return "Pronto per l'elaborazione";
    case 1:
      return "Comunicazione esclusa da operatore";
    case 3:
        return 'Riscontrata anomalia <i class="fa fa-info-circle tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.(!is_null($licensePointId) ? "({$licensePointId}) ".($a_LicensePointMex[$licensePointId] ?? 'Anomalia sconosciuta') : 'Anomalia sconosciuta').'"></i>';
    case 5:
      return "Punti decurtati";
    case 9:
      return "Pronto da riattribuire";
    }
  return "";
  }
$html = '<div class="clean_row HSpace16"></div>
<div class="col-sm-6 BoxRowTitle">
STORICO DECURTAZIONE:
</div>
<div class="clean_row HSpace4"></div>
  <div class="col-sm-2 table_label_H">
    Data/Ora reg.
  </div>
  <div class="col-sm-2 table_label_H">
    Data com.
  </div>
  <div class="col-sm-3 table_label_H">
    Trasgressore
  </div>
  <div class="col-sm-4 table_label_H">
    Stato
  </div>
  <div class="col-sm-1 table_label_H">
    Punti
  </div>';

$fineCommunicationHistory_rs = $rs->SelectQuery("select * from FineCommunicationHistory fch join Trespasser t on (t.Id=fch.TrespasserId) where FineId=$FineId order by CommunicationDate desc");
$a_LicensePointMex = getLicensePointCodeMex($rs);
while ($history = mysqli_fetch_array($fineCommunicationHistory_rs))
  {
  if ($history['CommunicationStatus'] != 5)
    $point_sign = "<span class='col-sm-12' style='color:red;'>-{$history['ReducedPoint']}</span>";
  else
    $point_sign = "<span class='col-sm-12' style='color:green;'>+{$history['ReducedPoint']}</span>";
    $html .= 
       "<div class='table_caption_H col-sm-2'>" . DateOutDB($history['RegDate']) ." ".TimeOutDB($history['RegTime']) ."</div>
        <div class='table_caption_H col-sm-2'>" . DateOutDB($history['CommunicationDate']) ."</div>
        <div class='table_caption_H col-sm-3'>" . StringOutDB((! empty($history['CompanyName']) ? $history['CompanyName'] . ' ' : '') . $history['Surname'] . ' ' . $history['Name']) . "</div>
        <div class='table_caption_H col-sm-4'>" . decodeFineCommunicationStatus($history['CommunicationStatus'], $a_LicensePointMex, $history['LicensePointId'] ?? null) . "</div>
        <div class='table_caption_H col-sm-1 text-center'>$point_sign</div>";
  }
echo $html;
?>