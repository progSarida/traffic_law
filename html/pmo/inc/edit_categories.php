<?php include('header.php');?>
<?php

include("controller_login.php");
include("document_functions.php");
checkLogin();
$user = $_SESSION['user'];
if(isset($_POST['submit'])){
    $document_id =  $_GET['doc_id'];

    $desc_ita = htmlentities($_POST['desc_ita'], ENT_QUOTES, "UTF-8");
    $desc_eng = htmlentities($_POST['desc_eng'], ENT_QUOTES, "UTF-8");
    $desc_ger = htmlentities($_POST['desc_ger'], ENT_QUOTES, "UTF-8");
    $desc_fra = htmlentities($_POST['desc_fra'], ENT_QUOTES, "UTF-8");
    $desc_spa = htmlentities($_POST['desc_spa'], ENT_QUOTES, "UTF-8");
    $order = htmlentities($_POST['anno_ord'], ENT_QUOTES, "UTF-8");


    // $filename."<br><br>";

    $sql = "UPDATE Categories SET Description_Ita = '$desc_ita', 
    Description_Eng='$desc_eng',
    Description_Ger='$desc_ger', 
    Description_Fre='$desc_fra', 
    Description_Spa='$desc_spa', 
    Starting_Validity='$order'
    WHERE ID = '$document_id'";

    if($conn->query($sql) === TRUE)

    {
        header('Location: category_administration.php');


    }

    else {
        $answer = "Errore nell'aggiornamento dei dati";
    }

}

?>
<div class="container">

    <h2>Aggiorna Categoria</h2>
    <p class ="" style="font-size:18px;color:green;"><?php  echo $answer;?></p>
    <hr>


    <?php
    $doc_id =  $_GET['doc_id'];
    $sql = "SELECT * from Categories WHERE Id = '$doc_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            ?>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group row">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Ita</i></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" rows="2" name="desc_ita" required ><?php echo $row['Description_Ita'];?></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Eng</i></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" rows="2" name="desc_eng" required><?php echo $row['Description_Eng'];?></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Ger</i></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" rows="2" name="desc_ger" required><?php echo $row['Description_Ger'];?></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Fre</i></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" rows="2" name="desc_fra" required ><?php echo $row['Description_Fre'];?></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Spa</i></label>
                    <div class="col-sm-8">
                        <textarea class="form-control" rows="2" name="desc_spa" required><?php echo $row['Description_Spa'];?></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Document Name Spa</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" name="anno_ord" value="<?php echo $row['Starting_Validity'];?>" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"></label>
                    <div class="col-sm-8">
                        <button class="btn btn-primary form-control edit_document" name="submit" type="submit" id="<?php echo $doc_id;?>">Submit</button>
                    </div>
                </div>
            </form>
            <?php
        }
    }

    ?>





</div>
</body>
</html>
