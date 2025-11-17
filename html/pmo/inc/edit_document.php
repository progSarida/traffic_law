    <?php include('header.php');?>
    <?php
 
include("controller_login.php");
include("document_functions.php");
checkLogin();
$user = $_SESSION['user'];
if(isset($_POST['submit'])){
    $document_id =  $_GET['doc_id'];
    $targetfolder = UPLOAD_FOLDER . basename( $_FILES['file']['name']) ;

    //echo $targetfolder."<br><br>";

    $name_ita = $_POST['name_ita'];
    $name_eng = htmlentities($_POST['name_eng'], ENT_QUOTES, "UTF-8");
    $name_ger = htmlentities($_POST['name_ger'], ENT_QUOTES, "UTF-8");
    $name_spa = htmlentities($_POST['name_spa'], ENT_QUOTES, "UTF-8");
    $name_fra = htmlentities($_POST['name_fra'], ENT_QUOTES, "UTF-8");
    $select_subcategory = $_POST['select_subcategory'];
    $desc_ita = htmlentities($_POST['desc_ita'], ENT_QUOTES, "UTF-8");
    $desc_eng = htmlentities($_POST['desc_eng'], ENT_QUOTES, "UTF-8");
    $desc_ger = htmlentities($_POST['desc_ger'], ENT_QUOTES, "UTF-8");
    $desc_fra = htmlentities($_POST['desc_fra'], ENT_QUOTES, "UTF-8");
    $desc_spa = htmlentities($_POST['desc_spa'], ENT_QUOTES, "UTF-8");
    $main_category = $_POST['main_category'];
    $filename = basename( $_FILES['file']['name']);

    // $filename."<br><br>";

    if($filename == ''){
        $sql = "UPDATE Documents SET DocumentNameIta = '$name_ita', 
        DocumentNameEng='$name_eng',
        DocumentNameGer='$name_ger', 
        DocumentNameFre='$name_fra', 
        DocumentNameSpa='$name_spa', 
        DescriptionIta='$desc_ita',
        DescriptionEng='$desc_eng',
        DescriptionGer='$desc_ger',
        DescriptionFre='$desc_fra',
        DescriptionSpa='$desc_spa',
        Category_ID='$select_subcategory',
        mainCategory='$main_category' WHERE ID = '$document_id'";
    }else{
        $sql = "UPDATE Documents SET DocumentNameIta = '$name_ita', 
        DocumentNameEng='$name_eng',
        DocumentNameGer='$name_ger', 
        DocumentNameFre='$name_fra', 
        DocumentNameSpa='$name_spa', 
        DescriptionIta='$desc_ita',
        DescriptionEng='$desc_eng',
        DescriptionGer='$desc_ger',
        DescriptionFre='$desc_fra',
        DescriptionSpa='$desc_spa', 
        hashedNameDocument='$filename',
        Category_ID='$select_subcategory',
        mainCategory='$main_category' WHERE ID = '$document_id'";
        
    $select = "SELECT * FROM Documents WHERE ID= '$document_id'";
    if ($result=mysqli_query($conn,$select))
    {
        while ($obj=mysqli_fetch_object($result))
        {
        $filename = $obj->hashedNameDocument;
        }
    
    }

    //echo $targetfolder.$_FILES['file']['tmp_name']." --- ".UPLOAD_FOLDER.$filename;

    //die;
    unlink(UPLOAD_FOLDER.$filename);
    move_uploaded_file($_FILES['file']['tmp_name'], $targetfolder);

    }
    
    if($conn->query($sql) === TRUE)

    {
        header('Location: document_administration.php');
        

    }

    else {
        $answer = "Problem uploading file";
    }
   
}

?>
    <div class="container">
        
        <h2>Edit Document</h2>
        <p class ="" style="font-size:18px;color:green;"><?php  echo $answer;?></p>
        <hr>
        

        <?php 
            $doc_id =  $_GET['doc_id'];
            $sql = "SELECT Documents.*,Categories.* from Documents join Categories on Documents.Category_ID = Categories.Id 
            WHERE Documents.ID = '$doc_id'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Main Category</label>
                                <div class="col-sm-8">
                                
                                    <select class="custom-select form-control" name="main_category">
                                        <?php 
                                            foreach($MAIN_CATEGORIES as $key => $value){
                                              if($value == $row['mainCategory'])  {
                                                  ?>
                                                    <option value="<?php echo $row['mainCategory'];?>" selected><?php echo $row['mainCategory'];?></option>
                                                  <?php
                                              }else{
                                                  ?>
                                                <option value="<?php echo $value;?>"><?php echo $value;?></option>
                                                <?php
                                              }
                                            }
                                        ?>
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Category</label>
                                <div class="col-sm-8">
                                <select class="custom-select form-control" name="select_subcategory">
                                    <?php 
                                        $sql = "SELECT * from Categories";
                                        $result = $conn->query($sql);
                                        while($row_cat = $result->fetch_assoc()) {
                                            if($row_cat['Description_Ita'] == $row['Description_Ita']){
                                                ?>
                                                <option value="<?php echo $row['Category_ID'];?>" selected><?php echo $row['Description_Ita'];?></option>
                                                <?php
                                            }else{
                                            ?>
                                                <option value="<?php echo $row_cat['Id'];?>"><?php echo $row_cat['Description_Ita'];?></option>
                                            <?php
                                            }
                                        }
                                    ?>
                                    
                                </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Document Name Ita</label>
                                <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" name="name_ita" value="<?php echo $row['DocumentNameIta'];?>" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Ita</i></label>
                                <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_ita" required ><?php echo $row['DescriptionIta'];?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Document Name Eng</label>
                                <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" name="name_eng" value="<?php echo $row['DocumentNameEng'];?>" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Eng</i></label>
                                <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_eng" required><?php echo $row['DescriptionEng'];?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Document Name Ger</label>
                                <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" name="name_ger" value="<?php echo $row['DocumentNameGer'];?>" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Ger</i></label>
                                <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_ger" required><?php echo $row['DescriptionGer'];?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Document Name Fra</label>
                                <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" name="name_fra" value="<?php echo $row['DocumentNameFre'];?>" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Fre</i></label>
                                <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_fra" required ><?php echo $row['DescriptionFre'];?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Document Name Spa</label>
                                <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm" name="name_spa" value="<?php echo $row['DocumentNameSpa'];?>" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm"><i>Description Spa</i></label>
                                <div class="col-sm-8">
                                <textarea class="form-control" rows="2" name="desc_spa" required><?php echo $row['DescriptionSpa'];?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">Choose File</label>
                                <div class="col-sm-8">
                                <a href="<?php echo UPLOAD_FOLDER.$row['hashedNameDocument'];?>" target="_blank">Download</a>
                                <input type="file" class="form-control form-control-sm" name="file">
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
