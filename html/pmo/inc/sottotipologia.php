
<?php
    
    include("controller_login.php");
    include("document_functions.php");
    checkLogin();
    if(isset($_POST['add_sototipologia'])){
        $t_description = htmlentities($_POST['t_description'], ENT_QUOTES, "UTF-8");
        $tipologia = $_POST['tipologia'];

        $sql = "INSERT INTO `sottoTipologia` VALUES (NULL, '$tipologia','$t_description');";
        if($conn->query($sql) === TRUE){
            $answer =  "Description added succesfully!";

        }else {
            $answer = $conn->error;

        }
        
    
        }else{
        $answer = "";
    }
    
    ?>
    <?php include('header.php');?>
    <div class="container">
       
        <div class="col-lg-10">
            <h5 style="color:green;"><?php echo $answer;?></h5>
            <table class="table table-striped">
            <caption><h3>Sotto Tipologia</h3></caption>
                <tr>
                    <th>#</th>
                    <th>Tipologia</th>
                    <th>Description</th>
                    <th>Delete</th>
                </tr>
                <tbody id="categories_data">
            <?php
            $query = "SELECT sottoTipologia.*,Tipologia.Id idTipoloGia, Tipologia.Description_Tipologia from sottoTipologia join Tipologia on sottoTipologia.tipologia_id = Tipologia.Id ORDER BY sottoTipologia.Id DESC";
            $rs= mysqli_query($conn, $query);
            if(mysqli_num_rows($rs) != 0){
                while($row = mysqli_fetch_array($rs)){
                    ?>
                    <tr id="remove<?php echo $row['Id'];?>">
                        <td><?php echo $row['Id'];?></td>
                        <td ><a data-name="select_tipologia" data-value="<?php echo $row['idTipoloGia'];?>" class="select" data-source="get_tipologia.php" data-type="select" data-pk="<?php echo $row['Id'];?>" style="color:blue;"><?php echo $row['Description_Tipologia'];?></a></td>
                        <td data-name="name_sototipologia" class="name" data-type="text" data-pk="<?php echo $row['Id'];?>" style="color:blue;"><?php echo $row['Description_SottoTipologia'];?></td>
                        <td><button class="btn btn-danger btn-sm delete_sototipologia" id="<?php  echo $row['Id'];?>" value="delete_sototipologia"> Delete</button>
                        
                    </tr>
                    <?php
                }
                ?>
                </tbody>
                </table>
                <?php
            }else{
                echo '<tr><td colspan="5">0 Results!</td></tr>';
                echo '</tbody></table>';
            }
            ?>
                
        </div>
        <div class="col-lg-2">
            <br>
            <button type="button" class="btn btn-info btn-sm pull-right form-control" data-toggle="modal" data-target="#myModal">Add New</button><br>

        </div>
        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add New</h4>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="sottotipologia.php">
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Description:</label>
                                <div class="col-sm-8">
                                <input type="text" class="form-control" name="t_description" required>                            
                                </div>
                            </div>
                            <div class="form-group row">
                                
                                <label for="colFormLabelSm" class="col-sm-4 col-form-label col-form-label-sm">Tipologia</label>
                                <div class="col-sm-8" >
                                <select class="custom-select form-control" name="tipologia">
                                    <?php 
                                            $sql = "SELECT * from Tipologia";
                                            $result = $conn->query($sql);
                                            while($row = $result->fetch_assoc()) {
                                                ?>
                                                    <option value="<?php echo $row['Id'];?>"><?php echo $row['Description_Tipologia'];?></option>
                                                <?php
                                        
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div id="ans_main"></div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" value="Add New" name="add_sototipologia" class="btn btn-primary">
                        </form>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>
                
    </div>    
   <script>
        $(document).ready(function(){
            
            $('.delete_sototipologia').on('click',function () {
                if (!confirm("Do you want to delete")){
                return false;
                }
                var button = $('.delete_sototipologia').val();
                var sototipologia_id = $(this).attr('id');
                var myData = {"delete_sototipologia":button,"sototipologia_id":sototipologia_id};
                $.ajax({
                    url: 'document_functions.php',
                    type: 'POST',
                    data: myData,
                    success: function(data){
                        $('#remove'+sototipologia_id).fadeOut();
                    },

                });
            });
            $('#categories_data').editable({
                container: 'body',
                selector: 'td.name',
                url: "editnewscategories.php",
                title: 'Description',
                type: "POST",
                validate: function(value){
                    if($.trim(value) == ''){
                        return 'This field is required';
                    }
                }
            });

            $('#categories_data').editable({
                container: 'body',
                type: 'select',
                selector: 'a.select',
                url: "editnewscategories.php",
                title: 'Tipologia',
                type: "POST",
                validate: function(value){
                    if($.trim(value) == ''){
                        return 'This field is required';
                    }
                }
            });   
        });

   </script>
</body>
</html>
