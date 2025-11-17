<?php  $lang = $_GET['l'];?>
<section class="box" id="Approval" style="padding-top: 0.025%; display: block;">
    <div id="container_ev" class="container" style="margin-top:10%">
        <div class="row-fluid">
            <div class="span12 header">
                <hgroup>
                    <h2>
                    <?php 
                        if($lang == ''){
                            echo $section['ita'];
                        }else{
                            foreach($section as $key => $value){
                                if($lang == $key){
                                    echo $value;
                                }
                                
                            }
                        }
                    ?>
                    </h2>
                </hgroup>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12 content">
                <!--<div class="row-fluid">
                    <div class="menu">
                        <?php 
                            if($lang == ''){
                                echo $category_o['ita'];
                            }else{
                                foreach($category_o as $key => $value){
                                    if($lang == $key){
                                        echo $value;
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <h3>
                                <?php 
                                    if($lang == ''){
                                        echo $description['ita'];
                                        }else{
                                        foreach($description as $key => $name){
                                            if($lang == $key){
                                                echo $name;
                                            }
                                        }
                                    }
                                ?>
                            </h3>
                        </th>
                        <th>
                            <h3>
                                <?php 
                                    if($lang == ''){
                                        echo $document['ita'];
                                        }else{
                                        foreach($document as $key => $name){
                                            if($lang == $key){
                                                echo $name;
                                            }
                                        }
                                    }
                                ?>
                            </h3>
                        </th>
                        <th><h3>FILE</h3></th>
                    </tr>
                    </thead>
                    <?php 
                        $query_omologazioni = "SELECT * FROM `Documents` WHERE mainCategory = 'OMOLOGAZIONI' ORDER BY ID DESC";
                        
                        $result = $conn->query($query_omologazioni);
                        while($row = $result -> fetch_assoc() ){
                            ?>
                                <tbody class="tbody">
                                    <tr>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                
                                                <?php
                                                    switch($lang){
                                                        case "ita":
                                                        echo $row['DescriptionIta'];
                                                        break;
                                                        case "eng":
                                                        echo $row['DescriptionEng'];
                                                        break;
                                                        case "ger":
                                                        echo $row['DescriptionGer'];
                                                        break;
                                                        case "fre":
                                                        echo $row['DescriptionFre'];
                                                        break;
                                                        case "spa":
                                                        echo $row['DescriptionSpa'];
                                                        break;
                                                        default:
                                                        echo $row['DescriptionIta'];
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                <?php
                                                    switch($lang){
                                                        case "ita":
                                                        echo $row['DocumentNameIta'];
                                                        break;
                                                        case "eng":
                                                        echo $row['DocumentNameEng'];
                                                        break;
                                                        case "ger":
                                                        echo $row['DocumentNameGer'];
                                                        break;
                                                        case "fre":
                                                        echo $row['DocumentNameFre'];
                                                        break;
                                                        case "spa":
                                                        echo $row['DocumentNameSpa'];
                                                        break;
                                                        default:
                                                        echo $row['DescriptionIta'];
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                <a href="<?php echo TARGET_FOLDER.$row['hashedNameDocument'];?>" target="_blank"><img src="img/download.png" alt="download" style="width:20px;text-align:center" /></a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            <?php

                        }
                    ?>
                </table>-->
                    <?php
                    ?>
                
                <div class="row-fluid">
                    <div class="menu">
                        <?php 
                            if($lang == ''){
                                echo $category_c['ita'];
                                }else{
                                foreach($category_c as $key => $value){
                                    if($lang == $key){
                                        echo $value;
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
                <?php 
                   
                    $query_collaudi = "SELECT * FROM `Categories` WHERE Id != 24 AND Id != 27 ORDER BY Starting_Validity DESC, Reg_Date DESC ";
                    $result_co = mysqli_query($conn, $query_collaudi);
                    if(!$result_co){
                		echo $conn->error;
                    	exit();
                    }else{
                    	while($row = mysqli_fetch_array($result_co)){
						    $cat_Id = $row['Id'];
						    if($cat_Id != 14){
						    	switch($lang){
		                            case "ita":
		                            $description_sub = $row['Description_Ita'];
		                            break;
		                            case "eng":
		                            $description_sub = $row['Description_Eng'];
		                            break;
		                            case "ger":
		                            $description_sub = $row['Description_Ger'];
		                            break;
		                            case "fre":
		                            $description_sub = $row['Description_Fre'];
		                            break;
		                            case "spa":
		                            $description_sub = $row['Description_Spa'];
		                            default:
		                            $description_sub = $row['Description_Ita'];
		                        }
							   	?>
		                        <div class="row-fluid">
		                            <div class="submenu">
		                            	<?php echo $description_sub;?></div>
		                        </div>
		                        <table class="table table-hover">
	                            <thead>
	                                <tr>
	                                    <th>
	                                        <h3>
	                                            <?php 
	                                            if($lang == ''){
	                                                echo $description['ita'];
	                                                }else{
	                                                foreach($description as $key => $name){
	                                                    if($lang == $key){
	                                                        echo $name;
	                                                    }
	                                                }
	                                            }
	                                            ?>
	                                        </h3>
	                                    </th>
	                                    <th>
	                                        <h3>
	                                            <?php 
	                                            if($lang == ''){
	                                                echo $document['ita'];
	                                                }else{
	                                                foreach($document as $key => $name){
	                                                    if($lang == $key){
	                                                        echo $name;
	                                                    }
	                                                }
	                                            }
	                                            ?>
	                                        </h3>
	                                    </th>
	                                    <th><h3>FILE</h3></th>
	                                </tr>
	                            </thead>
	                            
		                        <?php
                                          
		                        $query = "SELECT * FROM Documents JOIN Categories on Documents.Category_ID = Categories.Id WHERE Categories.Id = $cat_Id ORDER BY Documents.ID DESC";
                              $result = $conn->query($query);
                              
		                        if(!$result){
		                        	echo $conn->error;
		                        	exit();
			                        }
                              else{
                                      						
			                        while($row = mysqli_fetch_array($result)){                                   
                                     
			                        	?>
			                            <tbody class="tbody">
			                                <tr>
			                                    <td>
			                                        <div class="row-fluid" style="min-height: 30px;">
			                                            
			                                            <?php
			                                                switch($lang){
			                                                    case "ita":
			                                                    echo $row['DescriptionIta'];
			                                                    break;
			                                                    case "eng":
			                                                    echo $row['DescriptionEng'];
			                                                    break;
			                                                    case "ger":
			                                                    echo $row['DescriptionGer'];
			                                                    break;
			                                                    case "fre":
			                                                    echo $row['DescriptionFre'];
			                                                    break;
			                                                    case "spa":
			                                                    echo $row['DescriptionSpa'];
			                                                    break;
			                                                    default:
			                                                    echo $row['DescriptionIta'];
			                                                }

			                                            ?>
			                                        </div>
			                                    </td>
			                                    <td>
			                                        <div class="row-fluid" style="min-height: 30px;">
			                                        <?php
                                                          
			                                                switch($lang){
			                                                    case "ita":
			                                                    echo $row['DocumentNameIta'];
			                                                    break;
			                                                    case "eng":
			                                                    echo $row['DocumentNameEng'];
			                                                    break;
			                                                    case "ger":
			                                                    echo $row['DocumentNameGer'];
			                                                    break;
			                                                    case "fre":
			                                                    echo $row['DocumentNameFre'];
			                                                    break;
			                                                    case "spa":
			                                                    echo $row['DocumentNameSpa'];
			                                                    break;
			                                                    default:
			                                                    echo $row['DocumentNameIta'];
			                                                }
			                                            ?>
			                                        </div>
			                                    </td>
			                                    <td>
			                                        <div class="row-fluid" style="min-height: 30px;">
			                                            <a href="<?php echo TARGET_FOLDER.$row['hashedNameDocument'];?>" target="_blank"><img src="img/download.png" alt="download" style="width:20px;text-align:center" /></a>
			                                        </div>
			                                    </td>
			                                </tr>
			                            </tbody>
			                        	<?php
			                        }
			                        echo '</table>';
			                        echo '<div class="H_Row"></div>';
	                    		}
						    }
						    
	                    }
							
					}
                    
                ?>
            </div>
        </div>
    </div>
</section>

<section class="box" id="Omologation" style="padding-top: 0.025%; display: block;">
    <div id="container_ev" class="container" style="margin-top:10%">
        <div class="row-fluid">
            <div class="span12 header">
                <hgroup>
                    <h2>
                    <?php 
                        if($lang == ''){
                            echo $omologazioni['ita'];
                        }else{
                            foreach($omologazioni as $key => $value){
                                if($lang == $key){
                                    echo $value;
                                }
                                
                            }
                        }
                    ?>
                    </h2>
                </hgroup>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12 content">
                
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <h3>
                                <?php 
                                    if($lang == ''){
                                        echo $description['ita'];
                                        }else{
                                        foreach($description as $key => $name){
                                            if($lang == $key){
                                                echo $name;
                                            }
                                        }
                                    }
                                ?>
                            </h3>
                        </th>
                        <th>
                            <h3>
                                <?php 
                                    if($lang == ''){
                                        echo $document['ita'];
                                        }else{
                                        foreach($document as $key => $name){
                                            if($lang == $key){
                                                echo $name;
                                            }
                                        }
                                    }
                                ?>
                            </h3>
                        </th>
                        <th><h3>FILE</h3></th>
                    </tr>
                    </thead>
                    <?php 
                        $query_omo_sec = "SELECT * FROM `Documents` WHERE mainCategory = 'OMOLOGAZIONI' ORDER BY ID DESC";
                        
                        $result_omo_sec = $conn->query($query_omo_sec);
                        while($row = $result_omo_sec -> fetch_assoc() ){
                            ?>
                                 <tbody class="tbody">
                                    <tr>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                
                                                <?php
                                                    switch($lang){
                                                        case "ita":
                                                        echo $row['DescriptionIta'];
                                                        break;
                                                        case "eng":
                                                        echo $row['DescriptionEng'];
                                                        break;
                                                        case "ger":
                                                        echo $row['DescriptionGer'];
                                                        break;
                                                        case "fre":
                                                        echo $row['DescriptionFre'];
                                                        break;
                                                        case "spa":
                                                        echo $row['DescriptionSpa'];
                                                        default:
                                                        echo $row['DescriptionIta'];
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                <?php
                                                    switch($lang){
                                                        case "ita":
                                                        echo $row['DocumentNameIta'];
                                                        break;
                                                        case "eng":
                                                        echo $row['DocumentNameEng'];
                                                        break;
                                                        case "ger":
                                                        echo $row['DocumentNameGer'];
                                                        break;
                                                        case "fre":
                                                        echo $row['DocumentNameFre'];
                                                        break;
                                                        case "spa":
                                                        echo $row['DocumentNameSpa'];
                                                        default:
                                                        echo $row['DocumentNameIta'];
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                <a href="<?php echo TARGET_FOLDER.$row['hashedNameDocument'];?>" target="_blank"><img src="img/download.png" alt="download" style="width:20px;text-align:center" /></a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            <?php

                        }
                    ?>
                </table>
                    <?php
                    ?>
                
            </div>
        </div>
    </div>
</section>

<section class="box" id="Documentation" style="padding-top: 0.025%; display: block;">
    <div id="container_ev" class="container" style="margin-top:10%">
        <div class="row-fluid">
            <div class="span12 header">
                <hgroup>
                    <h2>
                    <?php 
                        if($lang == ''){
                            echo $documentazione['ita'];
                        }else{
                            foreach($documentazione as $key => $value){
                                if($lang == $key){
                                    echo $value;
                                }
                                
                            }
                        }
                    ?>
                    </h2>
                </hgroup>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12 content">
                
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <h3>
                                <?php 
                                    if($lang == ''){
                                        echo $description['ita'];
                                        }else{
                                        foreach($description as $key => $name){
                                            if($lang == $key){
                                                echo $name;
                                            }
                                        }
                                    }
                                ?>
                            </h3>
                        </th>
                        <th>
                            <h3>
                                <?php 
                                    if($lang == ''){
                                        echo $document['ita'];
                                        }else{
                                        foreach($document as $key => $name){
                                            if($lang == $key){
                                                echo $name;
                                            }
                                        }
                                    }
                                ?>
                            </h3>
                        </th>
                        <th><h3>FILE</h3></th>
                    </tr>
                    </thead>
                    <?php 
                        $query_docs = "SELECT * FROM `Documents` WHERE mainCategory =  'DOCUMENTAZIONE' order by ID DESC ";
                        $result_docs = $conn->query($query_docs);
                        while($row = $result_docs -> fetch_assoc() ){
                            ?>
                                 <tbody class="tbody">
                                    <tr>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                
                                                <?php
                                                    switch($lang){
                                                        case "ita":
                                                        echo $row['DescriptionIta'];
                                                        break;
                                                        case "eng":
                                                        echo $row['DescriptionEng'];
                                                        break;
                                                        case "ger":
                                                        echo $row['DescriptionGer'];
                                                        break;
                                                        case "fre":
                                                        echo $row['DescriptionFre'];
                                                        break;
                                                        case "spa":
                                                        echo $row['DescriptionSpa'];
                                                        default:
                                                        echo $row['DescriptionIta'];
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                <?php
                                                    switch($lang){
                                                        case "ita":
                                                        echo $row['DocumentNameIta'];
                                                        break;
                                                        case "eng":
                                                        echo $row['DocumentNameEng'];
                                                        break;
                                                        case "ger":
                                                        echo $row['DocumentNameGer'];
                                                        break;
                                                        case "fre":
                                                        echo $row['DocumentNameFre'];
                                                        break;
                                                        case "spa":
                                                        echo $row['DocumentNameSpa'];
                                                        default:
                                                        echo $row['DocumentNameIta'];
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row-fluid" style="min-height: 30px;">
                                                <a href="<?php echo TARGET_FOLDER.$row['hashedNameDocument'];?>" target="_blank"><img src="img/download.png" alt="download" style="width:20px;text-align:center" /></a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            <?php

                        }
                    ?>
                </table>
                    <?php
                    ?>
                <div class="row-fluid">
                    <div class="menu">
                    <?php 
                        if($lang == ''){
                            echo $info['ita'];
                        }else{
                            foreach($info as $key => $value){
                                if($lang == $key){
                                    echo $value;
                                }
                                
                            }
                        }
                    ?>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="submenu">www.ilportaledellautomobilista.it</div>
                </div>
            </div>
            
        </div>
    </div>
</section>
