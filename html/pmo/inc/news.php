<section class="box" id="News" style="padding-top: 0.025%; display: block;">
    <div id="container_ev" class="container" style="margin-top:10%">
        <div class="row-fluid">
            <div class="span12 header">
                <hgroup>
                    <h2>NEWS</h2>
                </hgroup>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12 content">
                <div class="row-fluid">
                <?php
                $thismonth = date("m");
                $thisyear = date("Y");
                $sql = "SELECT * FROM News JOIN Tipologia on News.Tipologia_Id = Tipologia.Id 
                JOIN sottoTipologia on News.Sotto_Tipologia_Id = sottoTipologia.Id 
                JOIN tipoAtto on News.TippoAtto_Id = tipoAtto.Id JOIN enteEmittente on News.EnteEmitente_Id = enteEmittente.Id 
                JOIN clasificazioneAtto ON News.ClassificazioneAtto_Id = clasificazioneAtto.Id WHERE MONTH(News_Date) = '$thismonth' and YEAR(News_Date) = '$thisyear'";
                $result = $conn->query($sql);
                
                while($row = $result -> fetch_assoc() ){
                    ?>
                    <h3><b><?php echo $row['Description_TipoAtto'].':&nbsp;'.$row['Description_SottoTipologia'].'&nbsp;-&nbsp;'.$row['Tipologia_Descrizione'];?></b></h3>
                    <p><b><?php echo $row['Description_Clasificazione'];?>
                    </b>- <i><?php echo $row['Description_EnteEmittente'].'&nbsp;-&nbsp;'.'sentenza '.$row['News_Date'].' n.'.$row['News_Number'];?></i></p><br>
                    <p><?php echo $row['Description_News'];?></p>
                    <hr style="border-top: 2px solid #8c8b8b;">
                    <?php
                }
                ?>
                </div>
                <div class="H_Row"></div>
                
            </div>
        </div>
    </div>
</section>



