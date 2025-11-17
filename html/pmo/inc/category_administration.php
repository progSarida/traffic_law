
<?php

include("controller_login.php");
include("document_functions.php");
checkLogin();
$get_category = $_GET['id'];
if(isset($_POST['submit'])){

    $targetfolder = UPLOAD_FOLDER . basename( $_FILES['file']['name']) ;

    /*$name_ita = htmlentities($_POST['name_ita'], ENT_QUOTES, "UTF-8");
    $name_eng = htmlentities($_POST['name_eng'], ENT_QUOTES, "UTF-8");
    $name_ger = htmlentities($_POST['name_ger'], ENT_QUOTES, "UTF-8");
    $name_fra = htmlentities($_POST['name_fra'], ENT_QUOTES, "UTF-8");
    $name_spa = htmlentities($_POST['name_spa'], ENT_QUOTES, "UTF-8");*/
    $desc_ita = htmlentities($_POST['desc_ita'], ENT_QUOTES, "UTF-8");
    $desc_eng = htmlentities($_POST['desc_eng'], ENT_QUOTES, "UTF-8");
    $desc_ger = htmlentities($_POST['desc_ger'], ENT_QUOTES, "UTF-8");
    $desc_fra = htmlentities($_POST['desc_fra'], ENT_QUOTES, "UTF-8");
    $desc_spa = htmlentities($_POST['desc_spa'], ENT_QUOTES, "UTF-8");
    $anno_ordinamento = htmlentities($_POST['anno_ord'], ENT_QUOTES, "UTF-8");
    //$category = $_POST['select'];
    //$main_category = $_POST['main_category'];
    //$filename = basename( $_FILES['file']['name']);
    $sql = "INSERT INTO `Categories` VALUES 
        (NULL, '$desc_ita', '$desc_eng', '$desc_ger', '$desc_fra', '$desc_spa','".date("Y-m-d")."','$anno_ordinamento');";
    if(/*move_uploaded_file($_FILES['file']['tmp_name'], $targetfolder) &&*/ $conn->query($sql) === TRUE)

    {

        $answer =  "Salvataggio riuscito";

    }
    else {

        $answer = "Errore di salvataggio";

    }


}

?>
<?php include('header.php');?>
<div class="container">
    <div class="row tabledocuments">

        <!--<div class="pull-left">


            <select class="custom-select form-control" onchange="location = this.options[this.selectedIndex].value;">

                <?php
                $get_category = $_GET['id'];
                ?>
                <option value="<?php echo 'document_administration.php'; ?>">ALL DOCUMENTS</option>

                <?php
                foreach($MAIN_CATEGORIES as $key => $value){
                    $isSelected  = $value == $get_category ? "selected": "";
                    ?>
                    <option value="<?php echo $_SERVER['PHP_SELF']."?"."id=$value"; ?>" <?php echo $isSelected;?>><?php echo $value;?></option>
                    <?php
                }
                ?>
                <?php
                ?>

            </select>
        </div>-->
        <button type="button" class="btn btn-info btn-sm pull-right" data-toggle="modal" data-target="#myModal">Add New File</button><br><hr>
        <?php echo $answer;?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">#</th>
                <!--<th scope="col">Main Category</th>
                <th scope="col">ID Categoria</th>-->
                <th scope="col">Nome Ita</th>
                <th scope="col">Name Eng</th>
                <th scope="col">Name Ger</th>
                <th scope="col">Name Fre</th>
                <th scope="col">Name Spa</th>
                <th scope="col">Download</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php
            /*if($get_category == ''){
                $sql = "select Documents.*,Categories.* from Documents LEFT JOIN Categories on Documents.Category_ID = Categories.Id order by Documents.ID desc
                ";
            }else{
                $sql = "select Documents.*,Categories.* from Documents LEFT JOIN Categories on Documents.Category_ID = Categories.Id WHERE Documents.mainCategory='$get_category' order by Documents.ID desc";

            }*/
            $sql = "select * from Categories order by Starting_Validity desc";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
            ?>
            <tr id="remove<?php echo $row['Id'];?>">

                <td scope="row"><?php echo $row['Id']; ?></td>
                <!--<td scope="row"><?php echo "";//$row['mainCategory']; ?></th>
                <td scope="row">
                    <?php
                    /*if($row['Description_Ita'] == 'Null'){
                        echo "No Category";
                    }else{
                        echo $row['Description_Ita'];
                    }*/
                    ?>
                </th>-->
                <td><?php echo $row['Description_Ita']; ?></td>
                <td><?php echo $row['Description_Eng']; ?></td>
                <td><?php echo $row['Description_Ger']; ?></td>
                <td><?php echo $row['Description_Fre']; ?></td>
                <td><?php echo $row['Description_Spa']; ?></td>
                <!--<td><a href="<?php //echo UPLOAD_FOLDER.$row['hashedNameDocument'];?>" target="_blank">Download</a></td>-->
                <td><a href="edit_categories.php?doc_id=<?php echo $row['Id']; ?>"><button class=" btn btn-success">Edit</button></a></td>
                <td><button class="btn btn-danger btn-sm delete" id="<?php  echo $row['Id'];?>" value="delete_category"> Delete</button>

                </td>
            </tr>

            </tbody>

            <?php
            }
            } else {
                ?>
                <tr>
                    <td colspan="8">0 Results</th>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>


    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Aggiungi categoria!</h4>
                </div>
                <div class="modal-body">
                    <form method="post" action="" enctype="multipart/form-data">

                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Descrizione Ita</i></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_ita" placeholder="Description Ita" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Descrizione Eng</i></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_eng" placeholder="Description Eng" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Descrizione Ger</i></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_ger" placeholder="Description Ger" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Descrizione Fra</i></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_fra" placeholder="Description Fra" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Descrizione Spa</i></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_spa" placeholder="Description Spa" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Anno</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" name="anno_ord" placeholder="2022..." required>
                            </div>
                        </div>
                        <div class="form-group row">

                            <div class="col-sm-10">
                            </div>
                        </div>

                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-primary" name="submit" value="submit" id="submit">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function(){

                //delete document
                $('.delete').on('click',function () {
                    if (!confirm("Do you want to delete")){
                        return false;
                    }
                    var button = $('.delete').val();
                    var category_id = $(this).attr('id');
                    var myData = {"delete_category":button,"category_id":category_id};
                    $.ajax({
                        url: 'document_functions.php',
                        type: 'POST',
                        data: myData,
                        success: function(data){
                            $('#remove'+category_id).fadeOut();

                        },

                    });
                });

            });
        </script>

        </body>
        </html>
