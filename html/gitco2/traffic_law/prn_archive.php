<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');




$Id = CheckValue('Id','n');



$rs= new CLS_DB();
$rs->SetCharset('utf8');






$str_out ='
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="'.$_SESSION['blazon'].'" style="width:50px;">
					<span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>
				</div>
         	</div>
        </div>
    	<div class="row-fluid">
        	<div class="col-sm-12">
        	    <form name="f_print" action="prn_archive_exe.php" method="post">
                <input type="hidden" name="FineId" value="' .$Id.'">

				<div class="table_label_H col-sm-1">&nbsp;</div>
				<div class="table_label_H col-sm-6">Nominativo</div>
				<div class="table_label_H col-sm-3">Citta</div>
				<div class="table_label_H col-sm-2">Paese</div>

				<div class="clean_row HSpace4"></div>';


$rs_Trespasser = $rs->Select("V_FineTrespasser","(TrespasserTypeId=1 OR TrespasserTypeId=11 OR TrespasserTypeId=2) AND FineId=".$Id);

    while ($r_Trespasser = mysqli_fetch_array($rs_Trespasser)) {
        $str_out.= '
					<div class="table_caption_button col-sm-1" style="text-align:center">
						<input type="checkbox" name="checkbox[]" value="' . $r_Trespasser['TrespasserId'] . '" checked />
					</div>';
        $str_out.= '<div class="table_caption_H col-sm-6">' . $r_Trespasser['CompanyName'] .' '.$r_Trespasser['Surname'].' '.$r_Trespasser['Name'].'</div>';
        $str_out.= '<div class="table_caption_H col-sm-3">' . $r_Trespasser['City'] .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-2">' . $r_Trespasser['CountryTitle'] .'</div>';


        $str_out.= '<div class="clean_row HSpace4"></div>';
    }

    $str_out.= '
        <div class="table_caption_H col-sm-12" style="height:128px">
            Lingua:<br />
            <input type="radio" name="LanguageId" value="1" checked> <img src="' .IMG.'/'.$aLan[1] .'" style="position:relative;top:-5px;width:16px;" />&nbsp;
            <input style="margin-left:40px;" type="radio" name="LanguageId" value="2"> <img src="' .IMG.'/'.$aLan[2] .'" style="position:relative;top:-5px;width:16px" />&nbsp;
            <input style="margin-left:40px;" type="radio" name="LanguageId" value="3"> <img src="' .IMG.'/'.$aLan[3] .'" style="position:relative;top:-5px;width:16px" />&nbsp;
            <input style="margin-left:40px;" type="radio" name="LanguageId" value="4"> <img src="' .IMG.'/'.$aLan[4] .'" style="position:relative;top:-5px;width:16px" />&nbsp;
            <input style="margin-left:40px;" type="radio" name="LanguageId" value="5"> <img src="' .IMG.'/'.$aLan[5] .'" style="position:relative;top:-5px;width:16px" />&nbsp;
            <br /><br />
            E p.c. <input type="checkbox" name="prefect" value="Y"> Prefetto   
                                                        
                                                        
        </div>';

	$strButtons = '<input type="submit" class="btn btn-default"  value="Stampa" style="margin-top:1rem;">';

		$str_out.= '

    <div class="col-sm-12 table_caption_H" style="height:6rem;text-align:center;line-height:6rem;">
    '.$strButtons.'    		 		
    </div>
    </form>
	</div>';


	$str_out.= '<div>
</div>';


	echo $str_out;
?>

<?php
include(INC."/footer.php");
