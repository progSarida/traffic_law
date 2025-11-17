<section class="box" id="Minutes" style="padding-top: 0.025%; display: block;">
    <div id="container_ev" class="container" style="margin-top:10%">
        <div class="row-fluid">
            <div class="span12 header">
                <hgroup>
                    <h2>Consultazione verbale</h2>
                    <h3>Per compilare i dati seguenti, guarda il nostro<br />
                        <a href="https://www.poliziamunicipale-online.it/help/Verbale_Esempio.pdf" style="text-decoration:underline;" target="_new">ESEMPIO</a></h3>
                </hgroup>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12 content" id="content_query">
                <div class="span5">
                    <div class="row-fluid">
                        <div class="row-fluid" style="min-height: 80px;">
                        Inserire Ente di Riferimento
                        (Esempio: Como)
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            Inserire Numero Cronologico
                            (Esempio: 123/2012/V o per esteri 123/2012/ES)
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            Data violazione (dd/mm/yyyy)
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            Ora Violazione
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            Targa Veicolo
                        </div>



                    </div>
                </div>
                <div class="span7">
                    <form id="f_query">
                        <fieldset>
                            <div class="control-group">
                        <div class="row-fluid" style="min-height: 80px;">
                            <?= $strCity ?>
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <input data-validation="number" data-validation-error-msg="Campo obbligatorio" type="text" id="nv" name="nv" style="width: 60px;">/
                            <?= $strYear ?>/
                            <select data-validation="required" data-validation-error-msg="Campo obbligatorio" style="width: 55px;" id="tv" name="tv">
                                <option></option>
                                <option value="V">V</option>
                                <option value="U">U</option>
                                <option value="T">T</option>
                                <option value="A">A</option>
                                <option value="C">C</option>
                                <option value="ES">ES</option>
                                <option value="VM">VM</option>
                            </select>
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <?= $strD ?>/<?= $strM ?>/<?= $strY ?>
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <?= $strHH.$strMM ?>
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <input data-validation="alphanumeric" data-validation-error-msg="Campo obbligatorio" type="text" id="pv" name="pv" style="text-transform:uppercase">
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <input type="button" value="Informativa" name="info" onClick="Abilita();">
                            <input data-validation="required" data-validation-error-msg="Campo obbligatorio" type="checkbox" name="informativa" id="informativa" >Accetto
                        </div>
                        <button class="btn btn-default" id="invia_query_home">Invia</button>
                    </form>
                </div>
                </fieldset>
                </div>
                <div id="message_query"></div>

            </div>
        </div>

    </div>
</section>
