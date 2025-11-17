<section class="box" id="Minutes" style="padding-top: 0.025%; display: block;">
    <div id="container_ev" class="container" style="margin-top:10%">
        <div class="row-fluid">
            <div class="span12 header">
                <hgroup>
                    <h2>Consulta multa</h2>
                    <h3>Para compilar la siguiente información, ver nuestro<br />
                        <a href="http://www.poliziamunicipale-online.it/help/Verbale_Esempio.pdf" style="text-decoration:underline;" target="_new">EJEMPLO</a></h3>
                </hgroup>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12 content" id="content_query">
                <div class="span5">
                    <div class="row-fluid">
                        <div class="row-fluid" style="min-height: 80px;">
                            Introduzca la Entidad de Referencia
                            (Ejemplo: Como)
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            Insertar número cronológico
                            (Ejemplo: 123/2012/V or 123/2012/ES)
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            Fecha fina (dd/mm/yyyy)
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            Hora fina
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            Placa
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
                            <input data-validation="number" data-validation-error-msg="Campo requerido" type="text" id="nv" name="nv" style="width: 60px;">/
                            <?= $strYear ?>/
                            <select data-validation="required" data-validation-error-msg="Campo requerido" style="width: 55px;" id="tv" name="tv">
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
                            <input data-validation="alphanumeric" data-validation-error-msg="Campo requerido" type="text" id="pv" name="pv" style="text-transform:uppercase">
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <input type="button" value="Disclosures" name="info" onClick="Abilita();">
                            <input data-validation="required" data-validation-error-msg="Campo requerido" type="checkbox" name="informativa" id="informativa" >De acuerdo
                        </div>
                        <button class="btn btn-default" id="invia_query_home">Enviar</button>
                    </form>
                </div>
                </fieldset>
                </div>
                <div id="message_query"></div>

            </div>
        </div>

    </div>
</section>
