
<?php
    
    include("controller_login.php");
    include("document_functions.php");
    checkLogin();
    $get_category = $_GET['id'];
    if(isset($_POST['submit'])){

        $targetfolder = UPLOAD_FOLDER . basename( $_FILES['file']['name']) ;

        $name_ita = htmlentities($_POST['name_ita'], ENT_QUOTES, "UTF-8");
        $name_eng = htmlentities($_POST['name_eng'], ENT_QUOTES, "UTF-8");
        $name_ger = htmlentities($_POST['name_ger'], ENT_QUOTES, "UTF-8");
        $name_fra = htmlentities($_POST['name_fra'], ENT_QUOTES, "UTF-8");
        $name_spa = htmlentities($_POST['name_spa'], ENT_QUOTES, "UTF-8");
        $desc_ita = htmlentities($_POST['desc_ita'], ENT_QUOTES, "UTF-8");
        $desc_eng = htmlentities($_POST['desc_eng'], ENT_QUOTES, "UTF-8");
        $desc_ger = htmlentities($_POST['desc_ger'], ENT_QUOTES, "UTF-8");
        $desc_fra = htmlentities($_POST['desc_fra'], ENT_QUOTES, "UTF-8");
        $desc_spa = htmlentities($_POST['desc_spa'], ENT_QUOTES, "UTF-8");
        $category = $_POST['select'];
        $main_category = $_POST['main_category'];
        $filename = basename( $_FILES['file']['name']);
        $sql = "INSERT INTO `Documents` VALUES 
        (NULL, '$name_ita', '$name_eng', '$name_ger', '$name_fra', '$name_spa', '$desc_ita', '$desc_eng', '$desc_ger', '$desc_fra', '$desc_spa', '$filename', '$category', '$main_category');";
        if(move_uploaded_file($_FILES['file']['tmp_name'], $targetfolder) && $conn->query($sql) === TRUE)

        {
        
            $answer =  "The file ". basename( $_FILES['file']['name']). " is uploaded";

        }
        else {

            $answer = "Problem uploading file";

        }
        
    
    }

    ?>
    <?php include('header.php');?>
    <div class="container">
        <div class="row tabledocuments">
            
            <div class="pull-left">
                
                
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
            </div>
            <button type="button" class="btn btn-info btn-sm pull-right" data-toggle="modal" data-target="#myModal">Add New File</button><br><hr>
            <?php echo $answer;?>
            <table class="table table-striped">
                <thead>
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Main Category</th>
                    <th scope="col">Category</th>
                    <th scope="col">Name Ita</th>
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
                if($get_category == ''){
                    $sql = "select Documents.*,Categories.* from Documents LEFT JOIN Categories on Documents.Category_ID = Categories.Id order by Documents.ID desc
                ";
                }else{
                    $sql = "select Documents.*,Categories.* from Documents LEFT JOIN Categories on Documents.Category_ID = Categories.Id WHERE Documents.mainCategory='$get_category' order by Documents.ID desc";

                }
                
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    // output data of each row
                    while($row = $result->fetch_assoc()) {
                        ?>
                            <tr id="remove<?php echo $row['ID'];?>">
                            
                            <td scope="row"><?php echo $row['ID']; ?></td>
                            <td scope="row"><?php echo $row['mainCategory']; ?></th>
                            <td scope="row">
                                <?php
                                if($row['Description_Ita'] == 'Null'){
                                    echo "No Category";
                                }else{
                                    echo $row['Description_Ita'];
                                }
                                ?>
                            </th>
                            <td><?php echo $row['DocumentNameIta']; ?></td>
                            <td><?php echo $row['DocumentNameEng']; ?></td>
                            <td><?php echo $row['DocumentNameGer']; ?></td>
                            <td><?php echo $row['DocumentNameFre']; ?></td>
                            <td><?php echo $row['DocumentNameSpa']; ?></td>
                            <td><a href="<?php echo UPLOAD_FOLDER.$row['hashedNameDocument'];?>" target="_blank">Download</a></td>
                            <td><a href="edit_document.php?doc_id=<?php echo $row['ID']; ?>"><button class=" btn btn-success">Edit</button></a></td>
                            <td><button class="btn btn-danger btn-sm delete" id="<?php  echo $row['ID'];?>" value="delete"> Delete</button>

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
                        <h4 class="modal-title">Add new document!</h4>
                    </div>
                <div class="modal-body">
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="form-group row">
                            
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Choose Main Category</label>
                            <div class="col-sm-8" >
                                <select class="custom-select form-control" name="main_category" id="main_category">
                                <?php 
                                    foreach($MAIN_CATEGORIES as $key => $value){
                                        ?>
                                            <option value="<?php echo $value;?>"><?php echo $value;?></option>
                                        <?php
                                    }
                                ?>
                                </select>
                            </div>
                            <div id="ans_main"></div>
                        </div>
                        <div class="form-group row">
                        
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Choose Category</label>
                            <div class="col-sm-8">
                            <select class="custom-select form-control" name="select" id="sub_category">
                            <?php 
                                    $sql = "SELECT * from Categories";
                                    $result = $conn->query($sql);
                                    while($row = $result->fetch_assoc()) {
                                        ?>
                                            <option value="<?php echo $row['Id'];?>"><?php echo $row['Description_Ita'];?></option>
                                        <?php
                                
                                    }
                                ?>
                                
                            </select>
                            </div>
                            <div id="ans_sub"></div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Document Name Ita</label>
                            <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" name="name_ita" placeholder="Document Name Ita" required>
                            </div>
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Description Ita</i></label>
                            <div class="col-sm-8">
                            <textarea class="form-control" rows="2" name="desc_ita" placeholder="Description Ita" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Document Name Eng</label>
                            <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" name="name_eng" placeholder="Document Name Eng" required>                            
                            </div>
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Description Eng</i></label>
                            <div class="col-sm-8">
                            <textarea class="form-control" rows="2" name="desc_eng" placeholder="Description Eng" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Document Name Ger</label>
                            <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" name="name_ger" placeholder="Document Name Ger" required>
                            </div>
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Description Ger</i></label>
                            <div class="col-sm-8">
                            <textarea class="form-control" rows="2" name="desc_ger" placeholder="Description Ger" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Document Name Fra</label>
                            <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" name="name_fra" placeholder="Document Name Fra" required>
                            </div>
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Description Fra</i></label>
                            <div class="col-sm-8">
                            <textarea class="form-control" rows="2" name="desc_fra" placeholder="Description Fra" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Document Name Spa</label>
                            <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" name="name_spa" placeholder="Document Name Spa" required>
                            </div>
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm"><i>Description Spa</i></label>
                            <div class="col-sm-8">
                            <textarea class="form-control" rows="2" name="desc_spa" placeholder="Description Spa" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Choose File</label>
                            <div class="col-sm-8">
                            <input type="file" name="file" size="50" required/>

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
    </div>    
   <script>
     $(document).ready(function(){
           
            //delete document
            $('.delete').on('click',function () {
                if (!confirm("Do you want to delete")){
                return false;
                }
                var button = $('.delete').val();
                var document_id = $(this).attr('id');
                var myData = {"delete":button,"document_id":document_id};
                $.ajax({
                    url: 'document_functions.php',
                    type: 'POST',
                    data: myData,
                    success: function(data){
                        $('#remove'+document_id).fadeOut();

                    },

                });
            });
            
        });
   </script>
   
</body>
</html>
