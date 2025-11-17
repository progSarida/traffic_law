
<?php

include("controller_login.php");
include("document_functions.php");
checkLogin();

?>
<?php include('header.php');?>
<style>
    .active, .link:hover {
        background-color: lightblue;
        color: lightslategray;
    }
    .clear{
        clear: both;
        height: 20px;
    }
</style>
<div class="container">
    <div class="row">
        <div id="nav">
            <ul class="nav nav-pills nav-justified">
                <li><a href="#documents" class="active link"><b>Documents</b></a></li>
                <li><a href="#news" class="link"><b>News</b></a></li>
                <li><a href="#news_categories" class="link"><b>News Categories</b></a></li>
            </ul>
        </div>
        <div class="clear"></div>
        <div id="documents" class="table table-responsive toggle col-md-12 col-sm-12">
            <ul class="list-group">
                <li class="list-group-item"><h3>Aggiungi un nuovo documento</h3>
                    <ol>
                        <li>
                            <p> Nella pagina Documenti fai clic sul pulsante <b> Add New File</b> a destra della pagina </p>
                        </li>
                        <li>
                            <p>Compila tutti i campi e seleziona il documento che desideri caricare</p>
                        </li>
                        <li>
                            <p>Fai clic sul pulsante <b> Submit </b> se vuoi aggiungere un nuovo documentot</p>
                        </li>
                        <li>
                            <p>Fai clic sul pulsante <b> Close </b> se desideri cancellarlo</p>
                        </li>
                    </ol>
                </li>
                <li class="list-group-item"><h3>Modifica documento</h3>
                    <ol>
                        <li>
                            <p>Nella pagina Documenti fai clic sul pulsante <b> Edit </b> per il documento che desideri modificare</p>
                        </li>
                        <li>
                            <p>Apporta tutte le modifiche che desideri</p>
                        </li>
                        <li>
                            <p>Fai clic sul pulsante <b> Submit </b> se desideri salvare le modifiche</p>
                        </li>
                    </ol>
                </li>
                <li class="list-group-item"><h3>Elimina documento</h3>
                    <ol>
                        <li>
                            <p>Nella pagina Documenti fai clic sul pulsante <b> Delete </b> per il documento che desideri eliminare</p>
                        </li>
                        <li>
                            <p>Dopo aver fatto clic sul pulsante <b> Delete </b>, riceverai una finestra di conferma</p>
                        </li>
                        <li>
                            <p>Fai clic su <b> Ok </b> se desideri eliminare o <b> Cancel </b> se cambi idea</p>
                        </li>
                    </ol>
                </li>
            </ul>
        </div>
        <div id="news" class="toggle" style="display:none">
            <ul class="list-group">
                <li class="list-group-item"><h3>Aggiungi notizie</h3>
                    <ol>
                        <li>
                            <p> Nella pagina News fai clic sul pulsante <b>Add News</b> a destra della pagina </p>
                        </li>
                        <li>
                            <p>Compila tutti i campi</p>
                        </li>
                        <li>
                            <p>Fare clic sul pulsante <b>Submit</b> se si desidera aggiungere le notizie</p>
                        </li>
                        <li>
                            <p>Fai clic sul pulsante <b>Close</b> se desideri cancellarlo</p>
                        </li>
                    </ol>
                </li>
                <li class="list-group-item"><h3>Modifica notizie</h3>
                    <ol>
                        <li>
                            <p>Nella pagina News fai clic sul pulsante <b> Edit </b> per le notizie che desideri modificare</p>
                        </li>
                        <li>
                            <p>Apporta tutte le modifiche che desideri</p>
                        </li>
                        <li>
                            <p>Fai clic sul pulsante <b> Submit </b> se desideri salvare le modifiche</p>
                        </li>
                    </ol>
                </li>
                <li class="list-group-item"><h3>Elimina notizie</h3>
                    <ol>
                        <li>
                            <p>Nella pagina News fai clic sul pulsante <b> Delete </b> per le notizie che desideri eliminare</p>
                        </li>
                        <li>
                            <p>Dopo aver fatto clic sul pulsante <b> Delete </b>, riceverai una finestra di conferma</p>
                        </li>
                        <li>
                            <p>Fai clic su <b> Ok </b> se desideri eliminare o <b> Cancel </b> se cambi idea</p>
                        </li>
                    </ol>
                </li>
            </ul>
        </div>
        <div id="news_categories" class="toggle" style="display:none">
            <ul class="list-group">
                <li class="list-group-item"><h3>Vai a una categoria specifica</h3>
                    <ol>
                        <li>
                            <p>Per cambiare le categorie di notizie, fai clic sul menu <b>News Categories</b></p>

                        </li>
                        <li>
                            <p>Dopo aver fatto clic, seleziona la categoria desiderata</p>
                        </li>
                    </ol>
                </li>
                <li class="list-group-item"><h3>Aggiungi valori per ogni categoria</h3>
                    <ol>
                        <li>
                            <p>Nella pagina delle categorie che hai scelto fai clic sul pulsante <b> Add New </b> a destra della pagina</p>
                        </li>
                        <li>
                            <p>Compila il campo della descrizione. (Se sei nella categoria Sottotipologia seleziona e Tiplogia dopo la descrizione)</p>
                        </li>
                        <li>
                            <p>Fai clic sul pulsante <b> Add New </b> se desideri aggiungere la descrizione per quella categoria</p>
                        </li>
                        <li>
                            <p>Fai clic sul pulsante <b> Close </b> se desideri cancellarlo</p>
                        </li>
                    </ol>
                </li>
                <li class="list-group-item"><h3>Modifica categoria</h3>
                    <ol>
                        <li>
                            <p>Nella pagina delle categorie che hai scelto fai clic sulla colonna <b> Description </b> sul testo che desideri modificare (è colorata in blu)</p>
                        </li>
                        <li>
                            <p>Modifica il testo e fai clic su <button type="submit" class="btn btn-primary btn-sm"><i class="glyphicon glyphicon-ok"></i></button> per salvare le modifiche</p>
                        </li>
                        <li>
                            <p>Clicca su <button type="button" class="btn btn-default btn-sm editable-cancel"><i class="glyphicon glyphicon-remove"></i></button> se vuoi cancellare</p>
                        </li>
                    </ol>
                </li>
                <li class="list-group-item"><h3>Elimina descrizione per ogni categoria</h3>
                    <ol>
                        <li>
                            <p>Nella pagina News fai clic sul pulsante <b> Delete </b> per le notizie che desideri eliminare</p>
                        </li>
                        <li>
                            <p>Dopo aver fatto clic sul pulsante <b> Delete </b>, riceverai una finestra di conferma</p>
                        </li>
                        <li>
                            <p>Fai clic su <b> Ok </b> se desideri eliminare o </b> Cancel </b> se cambi idea</p>
                        </li>
                    </ol>
                </li>
            </ul>
        </div>
    </div>
</div>
<script>
    $("#nav a").click(function(e){
        e.preventDefault();
        $(".toggle").hide();
        var toShow = $(this).attr('href');
        $(toShow).show();

    });
</script>

</body>
</html>
