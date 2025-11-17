<?php
function getFormVariables($Id,$CityId,int $FormTypeId, int $NationalityId=1, int $n_LanguageId=1 ){
    global $rs;
    //Determina quale formtype usare come generico in base alla nazionalità
    $GenericFormTypeId = $NationalityId == 1 ? 3 : 52;
    $a_Types = array();
    $rs_variable = $rs->Select('FormVariable',"Id='$Id' AND FormTypeId=$FormTypeId AND CityId='$CityId' AND LanguageId=$n_LanguageId And NationalityId=$NationalityId");
    
    //Se non trova nulla per il formtype passato, cerca per tipo generico, altrimenti verifica che ogni tipo di sottotesto non sia disabilitato, se sì prende la versione generica
    if (mysqli_num_rows($rs_variable) < 1){
            $rs_variable = $rs->Select('FormVariable',"Id='$Id' AND FormTypeId=$GenericFormTypeId AND CityId='$CityId' AND LanguageId=$n_LanguageId And NationalityId=$NationalityId");
            while ($r_variable = mysqli_fetch_array($rs_variable)){
                $a_Types[$r_variable['Type']] = StringOutDB($r_variable['Content']);
            }
    } else {
        while ($r_variable = mysqli_fetch_array($rs_variable)){
            if($r_variable['Disabled']==0)
                $a_Types[$r_variable['Type']] = StringOutDB($r_variable['Content']);
            else
                $a_Types[$r_variable['Type']] = mysqli_fetch_array($rs->Select('FormVariable',"Type={$r_variable['Type']} and Id='$Id' AND FormTypeId=$GenericFormTypeId AND CityId='$CityId' AND LanguageId=$n_LanguageId And NationalityId=$NationalityId"))['Content'];
        }
    }
    return $a_Types;
}

function getDynamicContent(int $FormTypeId,string $CityId,$NationalityId=1, $LanguageId=1){
    global $rs;
    //Determina quale formtype usare come generico in base alla nazionalità
    $GenericFormTypeId = $NationalityId == 1 ? 3 : 52;
    $forms = $rs->Select('FormDynamic', "FormTypeId=" . $FormTypeId . " AND CityId='$CityId' AND LanguageId=$LanguageId AND NationalityId=$NationalityId");
    if(mysqli_num_rows($forms)<1){
        return mysqli_fetch_array($rs->Select("FormDynamic","FormTypeId=$GenericFormTypeId AND CityId='$CityId' AND LanguageId=$LanguageId AND NationalityId=$NationalityId ","CityId desc"))['Content'];
    }
    return mysqli_fetch_array($forms)['Content'];
}

function getStaticContent(int $FormTypeId,string $CityId,$NationalityId=1, $LanguageId=1){
  global $rs;
    $forms = $rs->Select('Form', "FormTypeId=" . $FormTypeId . " AND CityId='$CityId' AND LanguageId=$LanguageId");
    if(mysqli_num_rows($forms)<1)
    if($NationalityId==1)
        return mysqli_fetch_array($rs->Select("Form","FormTypeId=3 AND CityId='$CityId'","CityId desc"))['Content'];
        else
        return mysqli_fetch_array($rs->Select("Form","FormTypeId=52 AND CityId='$CityId' AND LanguageId=$LanguageId ","CityId desc"))['Content'];
    return mysqli_fetch_array($forms)['Content'];
}

function createPDFColumns($pdf, $columnWidths, $values, $height,$header=false,$alignments=null)
{
    $y = $pdf->getY();
    if($header)
        $pdf->SetFont('arial', 'B', 8, '', true);
    else
        $pdf->SetFont('arial', '', 8, '', true);
    $x = 10;
    $arrlength = count($columnWidths);
    for ($i = 0; $i <$arrlength ; $i++) {
        if($alignments!=null)
            $alignment=$alignments[$i];
                else
            $alignment='C';
        $pdf->writeHTMLCell($columnWidths[$i], $height, $x, $y, $values[$i], 1, 0, 1, true, $alignment, true);
        $x += $columnWidths[$i];
    }
    $pdf->LN($height);
}

function getFormDynamicTitle(int $FineTypeId,int $StatusTypeId,string $CityId,string $CountryId, $TrespasserCountryId,int $ViolationTypeId){
    global $rs;
    if ($FineTypeId == 4){
        //Verbale contratto 40
        return 'Verbale contratto';
    } else if ($StatusTypeId == 8 || $StatusTypeId == 9) {
        //Invio bonario 81/82
        $rs_FormDynamicTitle = $rs->SelectQuery("
    SELECT F.Title
    FROM FormDynamic F
    left JOIN Country C ON C.Id = '$TrespasserCountryId'
    WHERE F.FormTypeId = ".($CountryId == 'Z000' ? 81 : 82)." AND F.CityId = '$CityId'".($CountryId != 'Z000' ? ' AND F.LanguageId = C.LanguageId' : '')
        );

return mysqli_fetch_assoc($rs_FormDynamicTitle)['Title'];
    } else {
        $rs_FormDynamicTitle = $rs->SelectQuery("
        SELECT F.Title
        FROM ViolationType V
        JOIN FormDynamic F ON V.".($CountryId == 'Z000' ? 'NationalFormId' : 'ForeignFormId')." = F.FormTypeId
        left JOIN Country C ON C.Id = '$TrespasserCountryId'
        WHERE V.Id = $ViolationTypeId AND F.CityId = '$CityId'".($CountryId != 'Z000' ? ' AND F.LanguageId = C.LanguageId' : ''));
        return mysqli_fetch_assoc($rs_FormDynamicTitle)['Title'];
    }
}

?>