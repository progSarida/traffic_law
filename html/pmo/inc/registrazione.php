<?php  $lang = $_GET['l'];
define("DOC_REGISTRA", "doc/registrazione/");

function leggiCartella($cartella)
 {
 $ris = array();
 $archivi = scandir($cartella);
 $N = count($archivi);
 for($i=0,$k=0; $i < $N ;$i++)
   if($archivi[$i][0] != '.')
     {
     $nome = $archivi[$i];
     $percorso = realpath($cartella . "/" . $nome);
     $titolo = str_replace("_", " ", $nome);
     $titolo = substr($titolo, 0, strrpos($titolo, '.'));
     $ris[$k]['nome'] = $nome;
     $ris[$k]['percorso'] = $percorso;
     $ris[$k++]['titolo'] = $titolo;
     }
 return $ris;
 }
?>
<section class="box" id="Registrati" style="padding-top: 0.025%; display: block;">
    <div id="container_ev" class="container" style="margin-top:10%">
        <div class="row-fluid">
            <div class="span12 header">
                <hgroup>
                    <h2>REGISTRAZIONE </h2>
                </hgroup>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12 content">
                
                <div class="row-fluid">
                    <div class="menu">Registrazione</div>
                </div>
                <?php 
                   
                    $sql_enti = "SELECT * FROM enteEmittente ORDER BY Description_EnteEmittente";
                    $ris_enti = mysqli_query($conn, $sql_enti);
                    if(!$ris_enti){
                		echo $conn->error;
                    	exit();
                    }else{
                    	while($riga = mysqli_fetch_array($ris_enti))
			  {
			  $codEnte = $riga['Id'];
		          $descEnte = $riga['Description_EnteEmittente'];
		?>
		                        <div class="row-fluid">
		                            <div class="submenu">
		                            	<?php echo $descEnte;?></div>
		                        </div>
		                        <table class="table table-hover">
	                            <thead>
	                                <tr>
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
                                          
					$doc = leggiCartella(DOC_REGISTRA.$codEnte);
					$N = count($doc);
			                for($i=0; $i < $N ;$i++)
					  {
			                ?>
			                            <tbody class="tbody">
			                                <tr>
			                                    <td>
			                                        <div class="row-fluid" style="min-height: 30px;">
			                                        <?=$doc[$i]['titolo']?>
			                                        </div>
			                                    </td>
			                                    <td>
			                                        <div class="row-fluid" style="min-height: 30px;">
			                                            <a href="<?=DOC_REGISTRA."$codEnte/".$doc[$i]['nome']?>" target="_blank"><img src="img/download.png" alt="download" style="width:20px;text-align:center" /></a>
			                                        </div>
			                                    </td>
			                                </tr>
			                            </tbody>
			                        	<?php
			                        }
						?>
			         </table>
			         <div class="H_Row"></div> <?php
	                    		}
				}
                ?>
            </div>
        </div>
    </div>
</section>

