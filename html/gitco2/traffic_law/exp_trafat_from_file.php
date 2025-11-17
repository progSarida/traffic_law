<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$str_out.= '    
    <div class="row-fluid">
        <div class="col-sm-12">
            <form action="exp_trafat_from_file_exe.php" id="f_Export" method="post" enctype="multipart/form-data">
                <div class="col-sm-6 BoxRowLabel">
                    Caricare il file da creare:
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    <input type="file" name="fileToUpload" id="fileToUpload">
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    <i class="glyphicon glyphicon-ok" style="position:absolute;top:4px;right:10px;font-size:2rem"></i>
                </div>
            </form>
            <div class="clean_row HSpace4"></div>
            ';



echo $str_out;

?>

    <script type="text/javascript">

        $(document).ready(function () {

            $(".glyphicon-ok").click(function(){
                $("#f_Export").submit();
            });

        });
    </script>
<?php
include(INC."/footer.php");