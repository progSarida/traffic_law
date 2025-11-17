<?php
include_once("_path.php");
include_once(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//TODO rimuovere quando tutte le tabelle saranno convertite in utf8mb4
$rs->SetCharset('utf8mb4');

//Determina se si stà arrivando da tbl_article_city.php.php
$CityPage = isset($CityPage) ? $CityPage : false;
//Determina se si stà arrivando da tbl_article_base.php
$BasePage = isset($BasePage) ? $BasePage : false;

$ArtComune= CheckValue('ArtComune','s');

$Search_Year = CheckValue('Search_Year','s') != '' ? CheckValue('Search_Year','s') : $_SESSION['year'];

// $Article_Search= CheckValue('Article_Search','n');
// $Paragraph_Search= CheckValue('Paragraph_Search','s');
// $Letter_Search = CheckValue('Letter_Search','s');

$Search_Article = CheckValue('Search_Article','s');
$Search_Paragraph = CheckValue('Search_Paragraph','s');
$Search_Letter = CheckValue('Search_Letter','s');
$Search_HasLicensePoints = CheckValue('Search_HasLicensePoints','n');
$Search_LicensePointCode1 = CheckValue('Search_LicensePointCode1','s');
$Search_LicensePointCode2 = CheckValue('Search_LicensePointCode2','s');

$Id1_Search= CheckValue('Id1_Search','n');
$Id2_Search= CheckValue('Id2_Search','s');
$Id3_Search = CheckValue('Id3_Search','s');

$Search_CityId = CheckValue('Search_CityId','s');

$a_Lan = unserialize(LANGUAGE_KEYS);

$str_Where = "1=1 AND RuleTypeId={$_SESSION['ruletypeid']}";

//Se l'utente ha un valore di permessi >50 oppure la pagina di provenienza è tbl_article_city.php
if($_SESSION['usertype']<=50 || $CityPage){
    $str_CustomerSearch = "CityId='".$_SESSION['cityid']."'";
    $str_Where.=" AND CityId='".$_SESSION['cityid']."'";
}
//Se la pagina di provenienza è tbl_article_base.php
else if ($BasePage) {
    $str_CustomerSearch = "CityId='".ENTE_BASE."'";
    $str_Where.=" AND CityId='".ENTE_BASE."'";
} else {
    $str_CustomerSearch = "1=1";
}


$CityId = $_SESSION['cityid'];

if($Search_Article>0){
    $str_Where .= " AND Article=".$Search_Article;
    $str_CurrentPage .= "&Article=".$Search_Article;
}else $Id1 = "";
if($Search_Paragraph!=""){
    $str_Where .= " AND Paragraph='".$Search_Paragraph."'";
    $str_CurrentPage .= "&Paragraph=".$Search_Paragraph;
}
if($Search_Letter!=""){
    $str_Where .= " AND Letter='".$Search_Letter."'";
    $str_CurrentPage .= "&Letter=".$Search_Letter;
}
if($Search_AdditionalSanction > 0){
    $str_Where .= " AND AdditionalSanctionId > 0";
    $str_CurrentPage .= "&Search_AdditionalSanction=".$Search_AdditionalSanction;
}
if($Search_HasLicensePoints > 0){
    $str_Where .= " AND LicensePoint > 0";
    $str_CurrentPage .= "&Search_HasLicensePoints=".$Search_HasLicensePoints;
}
if($Search_LicensePointCode1!=""){
    $str_Where .= " AND LicensePointCode1='".$Search_LicensePointCode1."'";
    $str_CurrentPage .= "&Search_LicensePointCode1=".$Search_LicensePointCode1;
}
if($Search_LicensePointCode2!=""){
    $str_Where .= " AND LicensePointCode2='".$Search_LicensePointCode2."'";
    $str_CurrentPage .= "&Search_LicensePointCode2=".$Search_LicensePointCode2;
}

if($Id1_Search>0){
    $str_Where .= " AND Id1=".$Id1_Search;
    $str_CurrentPage .= "&Id1_Search=".$Id1_Search;
}else $Id1 = "";
if($Id2_Search!=""){
    $str_Where .= " AND Id2='".$Id2_Search."'";
    $str_CurrentPage .= "&Id2_Search=$Id2_Search";
}
if($Id3_Search!=""){
    $str_Where .= " AND Id3='".$Id3_Search."'";
    $str_CurrentPage .= "&Id3_Search=".$Id3_Search;
}
if($ArtComune!=""){
    $str_Where .= " AND ArtComune='".$ArtComune."'";
    $str_CurrentPage .= "&ArtComune=".$ArtComune;
}

if($Search_CityId!=""){
    $str_Where .= " AND CityId='". $Search_CityId ."'";
    $str_CurrentPage .= "&ArtComune='$ArtComune'";
}


if($Id1_Search==0) $Id1_Search = "";
if($Search_Article==0) $Search_Article = "";
$strOrder = "Id Desc";

//Pagina su cui puntare nella ricerca
$FormActionPage = 'tbl_article.php';
if ($CityPage) $FormActionPage = 'tbl_article_city.php';
else if ($BasePage) $FormActionPage = 'tbl_article_base.php';



    $str_out .= '
        <div class="row-fluid">
            <form name="f_search_article" id="f_search_article" action="'.$FormActionPage.$str_GET_Parameter.'" method="post">
                <div class="col-sm-12 table_label_H" style="text-align:center">
                    Ricerca articolo
                </div>

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-11">';
                
    if (!$CityPage && !$BasePage){
        $str_out .= '
                    <div class="col-sm-1 BoxRowLabel">
                        Ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        '. CreateSelect("Customer",$str_CustomerSearch,"CityId","Search_CityId","CityId","ManagerCity",$Search_CityId,false).'
                    </div>';
    }
    $str_out .= '
                    <div class="col-sm-1 BoxRowLabel">
                        Ruolo articoli
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        '.$_SESSION['ruletypetitle'].'
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Anno
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        '.CreateSelectQuery('SELECT DISTINCT Year FROM ArticleTariff ORDER BY Year DESC', 'Search_Year', 'Year', 'Year', ($Search_Year != '' ? $Search_Year : $_SESSION['year']), true, '', 'form-control').'
                    </div>
                    <div class="'.($CityPage || $BasePage ? 'col-sm-8' : 'col-sm-4').' BoxRowLabel">
                    </div>
                    <div class="clean_row HSpace4"></div>
                
                    <div class="col-sm-5" style="padding:0;">
                        <div class="col-sm-2 BoxRowLabel">
                            Art.
                        </div>    
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_numeric" type="text" name="Search_Article" value="'.$Search_Article.'" style="width:5rem">                  
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Comma
                        </div>                        
                        <div class="col-sm-2 BoxRowCaption">
                             <input class="form-control frm_field_string" type="text" name="Search_Paragraph" value="'.$Search_Paragraph.'" style="width:5rem">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Lettera
                        </div>     
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="Search_Letter" value="'.$Search_Letter.'" style="width:5rem">
                        </div>
                    </div>
                    <div class="col-sm-1 BoxRowLabel table_caption_error">
                        Articolo Ente:                   
                    </div>
                    <div class="col-sm-4" style="padding:0;"> 
                        <div class="col-sm-2 BoxRowLabel table_caption_error">
                            Art.
                        </div>  
                        <div class="col-sm-2 BoxRowCaption">
                            <input disabled class="form-control frm_field_numeric" type="text" name="Id1_Search" value="'.$Id1_Search.'" style="width:5rem">                  
                        </div>
                        <div class="col-sm-2 BoxRowLabel table_caption_error">
                            Comma
                        </div>                             
                        <div class="col-sm-2 BoxRowCaption">
                            <input disabled class="form-control frm_field_string" type="text" name="Id2_Search" value="'.$Id2_Search.'" style="width:5rem">
                        </div>
                        <div class="col-sm-2 BoxRowLabel table_caption_error">
                            Lettera
                        </div>         
                        <div class="col-sm-2 BoxRowCaption">
                            <input disabled class="form-control frm_field_string" type="text" name="Id3_Search" value="'.$Id3_Search.'" style="width:5rem">
                        </div>  
                    </div>                       
                    <div class="col-sm-1 BoxRowLabel table_caption_error">
                        Codice ente
                    </div>                    
                    <div class="col-sm-1 BoxRowCaption">
                        <input disabled class="form-control frm_field_string" type="text" name="ArtComune" value="'.$ArtComune.'" style="width:8rem">                  
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-2 BoxRowLabel">
                        Sanzione accessoria
                    </div>                    
                    <div class="col-sm-1 BoxRowCaption">
                    	<!-- Input per checkbox vuota -->
                    	<input value="0" type="hidden" name="Search_AdditionalSanction"> 
                        <input value="1" name="Search_AdditionalSanction" type="checkbox" '.ChkCheckButton($Search_AdditionalSanction).' />                  
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Decurtazione punti
                    </div>                    
                    <div class="col-sm-1 BoxRowCaption">
                    	<!-- Input per checkbox vuota -->
                    	<input value="0" type="hidden" name="Search_HasLicensePoints"> 
                        <input value="1" name="Search_HasLicensePoints" type="checkbox" '.ChkCheckButton($Search_HasLicensePoints).' />                  
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Cod. decurtazione
                    </div>                    
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Search_LicensePointCode1" value="'.$Search_LicensePointCode1.'">                  
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Cod. decurtazione (recidiva)
                    </div>                    
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Search_LicensePointCode2" value="'.$Search_LicensePointCode2.'">                  
                    </div>
                </div>
                <div class="col-sm-1">
                    <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                        <button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:6.8rem;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
                    </div>
                </div>

                <div class="clean_row HSpace4"></div>
            </form>
        </div>
    ';


$str_out .='
            <div class="row-fluid">
                <div class="table_label_H col-sm-1">Id</div>
                <div class="table_label_H col-sm-2">Comune</div>
                <div class="table_label_H col-sm-1">Articolo C.D.S</div>
                <div class="table_label_H col-sm-1">Articolo Ente</div>
                <div class="table_label_H col-sm-1">Violazione</div>
                <div class="table_label_H col-sm-2">Particella verbale</div>
                <div class="table_label_H col-sm-1">Min / Max</div>
                <div class="table_label_H col-sm-1">Prefettura</div>
                <div class="table_label_H col-sm-1">Anno</div>
                
                        
                <div class="table_add_button col-sm-1 right">
                    '.ChkButton($aUserButton, 'add','<a href="tbl_article_add.php'.$str_GET_Parameter.'"><span data-container="body" data-toggle="tooltip" data-placement="left" title="Inserisci" class="glyphicon glyphicon-plus-sign tooltip-r add_button" style="height:2.5rem;margin-right:0.3rem; line-height:2.3rem;"></span></a>').'
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="table_label_H col-sm-7">Testo</div>
                <div class="table_label_H col-sm-4"></div>
                <div class="table_label_H col-sm-1"></div>
                <div class="clean_row HSpace4"></div>
                ';

$articles = $rs->SelectQuery(
    "SELECT A.*,AT.*,C.Title AS CityTitle,R.Title AS RuleTitle,V.Title AS ViolationTitle 
    FROM Article A
    JOIN ArticleTariff AT ON AT.ArticleId=A.Id AND AT.Year=$Search_Year
    JOIN ViolationType V ON A.ViolationTypeId=V.Id
    JOIN ".MAIN_DB.".Rule R ON V.RuleTypeId=R.Id
    LEFT JOIN sarida.City C ON A.CityId=C.Id
    WHERE $str_Where AND Year=$Search_Year ORDER BY $strOrder LIMIT $pagelimit,".PAGE_NUMBER);
//$articles = $rs->Select('V_ArticleCity',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);
$RowNumber = mysqli_num_rows($articles);

if ($RowNumber == 0) {
    $str_out.= 'Nessun record presente';
} else {
    while ($article = mysqli_fetch_array($articles)) {
        if($article['LicensePointCode1'] > 0){
            $LicensePointCode1P1 = trim(substr($article['LicensePointCode1'],0,4));
            $LicensePointCode1P2 = trim(substr($article['LicensePointCode1'],4,2));
            $LicensePointCode1P3 = trim(substr($article['LicensePointCode1'],6,2));
        } else {
            $LicensePointCode1P1 = $LicensePointCode1P2 = $LicensePointCode1P3 = '';
        }
        
        if($article['LicensePointCode2'] > 0){
            $LicensePointCode2P1 = trim(substr($article['LicensePointCode2'],0,4));
            $LicensePointCode2P2 = trim(substr($article['LicensePointCode2'],4,2));
            $LicensePointCode2P3 = trim(substr($article['LicensePointCode2'],6,2));
        } else {
            $LicensePointCode2P1 = $LicensePointCode2P2 = $LicensePointCode2P3 = '';
        }
        
        $str_out.= '        
            <div class="table_caption_H col-sm-1">' . $article['ArticleId'] .'</div>
            <div class="table_caption_H col-sm-2">' . $article['CityId'] .' '. $article['CityTitle'] .'</div>
            <div class="table_caption_H col-sm-1">' . $article['Article'] .' - '.$article['Paragraph'].' - '.$article['Letter'].'</div>
            <div class="table_caption_H table_caption_error BoxRowLabel col-sm-1">' . $article['Id1'].' - '.$article['Id2'].' - '.$article['Id3'].'</div>
            <div class="table_caption_H col-sm-1">' . utf8_encode($article['ViolationTitle']) .'</div>
            <div class="table_caption_H col-sm-2">' . $article['ArticleLetterAssigned'] .'</div>
            <div class="table_caption_H col-sm-1">' . $article['Fee'] .' / '. $article['MaxFee'] .'</div>
            <div class="table_caption_H col-sm-1">' . CheckbuttonOutDB($article['PrefectureFixed']) .'</div>
            <div class="table_caption_H col-sm-1">' . $article['Year'] .'</div>
            ';

        $fineArticlesQuery=$rs->SelectQuery("SELECT FineId FROM FineArticle WHERE ArticleId=". $article['ArticleId']);
        
        if(mysqli_num_rows($fineArticlesQuery) > 0)
            $removeButton='';
        else
            $removeButton= ChkButton($aUserButton, 'del','<a href="tbl_article_del.php?Id=' . $article['ArticleId'] . '&CityId=' . $article['CityId'] . '"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Elimina" class="glyphicon glyphicon-remove-sign tooltip-r"></span></a>&nbsp;');
        
        $str_out.= '
            <div class="table_caption_button col-sm-1">

            '.ChkButton($aUserButton, 'viw','<a href="tbl_article_viw.php'.$str_GET_Parameter.'&Id='. $article['ArticleId'] . '&Year=' . $Search_Year . '"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Visualizza" class="glyphicon glyphicon-eye-open tooltip-r"></span></a>&nbsp;').'
            '.ChkButton($aUserButton, 'upd','<a href="tbl_article_upd.php'.$str_GET_Parameter.'&Id=' . $article['ArticleId'] . '&Year=' . $Search_Year . '"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Modifica" class="glyphicon glyphicon-pencil tooltip-r"></span></a>&nbsp;').'
            '.$removeButton.'
            '.ChkButton($aUserButton, 'dpc','<a href="tbl_article_upd.php'.$str_GET_Parameter.'&Id=' . $article['ArticleId'] . '&Year=' . $Search_Year . '&Duplicate=1"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Duplica" class="glyphicon glyphicon-duplicate tooltip-r"></span></a>').'
            </div>
            <div class="clean_row HSpace4"></div>

            <div class="table_caption_H col-sm-1"><img src="' .IMG.'/f_'.strtolower($a_Lan['Italiano']) .'.png" style="width:16px" /> Italiano</div>
            <div class="table_caption_H col-sm-6" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">' . $article['DescriptionIta'] .'</div>
            <div class="table_caption_H col-sm-1 table_caption_I" style="font-size:1rem">Pt. Patente</div>
            <div class="table_caption_H col-sm-1">'.$article['LicensePoint'].'</div>
            <div class="table_caption_H col-sm-1 table_caption_I" style="font-size:1rem">Pt. Neopatentati</div>
            <div class="table_caption_H col-sm-1">'.$article['YoungLicensePoint'].'</div>
            <div class="table_caption_button col-sm-1"></div>
            <div class="clean_row HSpace4"></div>
            <div class="table_caption_H col-sm-1"><img src="' .IMG.'/f_'.strtolower($a_Lan['Inglese']) .'.png" style="width:16px" /> Inglese</div>
            <div class="table_caption_H col-sm-6" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">' . $article['DescriptionEng'] .'</div>
            <div class="table_caption_H col-sm-1 table_caption_I" style="font-size:1rem">Cod. Decurtazione</div>
            <div class="table_caption_H col-sm-1">'.$LicensePointCode1P1.' '.$LicensePointCode1P2.' '.$LicensePointCode1P3.'</div>
            <div class="table_caption_H col-sm-1 table_caption_I" style="font-size:1rem">Cod. Recidiva</div>
            <div class="table_caption_H col-sm-1">'.$LicensePointCode2P1.' '.$LicensePointCode2P2.' '.$LicensePointCode2P3.'</div>
            <div class="table_caption_button col-sm-1"></div>
            <div class="clean_row HSpace4"></div>
            <div class="table_caption_H col-sm-1"><img src="' .IMG.'/f_'.strtolower($a_Lan['Tedesco']) .'.png" style="width:16px" /> Tedesco</div>
            <div class="table_caption_H col-sm-6" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">' . $article['DescriptionGer'] .'</div>
            <div class="table_caption_H col-sm-2"></div>
            <div class="table_caption_H col-sm-2"></div>
            <div class="table_caption_button col-sm-1"></div>
            <div class="clean_row HSpace4"></div>
            <div class="table_caption_H col-sm-1"><img src="' .IMG.'/f_'.strtolower($a_Lan['Spagnolo']) .'.png" style="width:16px" /> Spagnolo</div>
            <div class="table_caption_H col-sm-6" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">' . $article['DescriptionSpa'] .'</div>
            <div class="table_caption_H col-sm-2"></div>
            <div class="table_caption_H col-sm-2"></div>
            <div class="table_caption_button col-sm-1"></div>
            <div class="clean_row HSpace4"></div>
            <div class="table_caption_H col-sm-1"><img src="' .IMG.'/f_'.strtolower($a_Lan['Francese']) .'.png" style="width:16px" /> Francese</div>
            <div class="table_caption_H col-sm-6" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">' . $article['DescriptionFre'] .'</div>
            <div class="table_caption_H col-sm-2"></div>
            <div class="table_caption_H col-sm-2"></div>
            <div class="table_caption_button col-sm-1"></div>
            <div class="clean_row HSpace48"></div>
            ';


    }
}
$table_users_number = $rs->SelectQuery(
    "SELECT ArticleId
    FROM Article A
    JOIN ArticleTariff AT ON AT.ArticleId=A.Id AND AT.Year=$Search_Year
    JOIN ViolationType V ON A.ViolationTypeId=V.Id
    JOIN ".MAIN_DB.".Rule R ON V.RuleTypeId=R.Id
    LEFT JOIN sarida.City C ON A.CityId=C.Id
    WHERE $str_Where AND Year=$Search_Year");
$UserNumberTotal = mysqli_num_rows($table_users_number);

$str_out.= '</div>';

$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,"");


echo $str_out;
?>



<?php
include(INC."/footer.php");