<section class="box" id="Contact" style="display: block;"  >
    <div class="container">
        <div style="margin-top: 110px;" class="panel">
            <div class="row-fluid">
                <div class="span12 header">
                    <hgroup>
                        <h2>Contact</h2>

                        <h3>Demandez et obtenir des informations</h3>
                    </hgroup>
                </div>
            </div>

            <div class="row-fluid">
                <div class="span12 content">
                    <div class="row-fluid">
                        <div class="span6" id="response_mex">

                            <form id="f_email">
                                <fieldset>
                                    <div class="controls">
                                        <div class="control-group">
                                            <div class="controls">
                                                <input class="span12" id="s_obj" name="s_obj" type="text" value="Informazioni generiche PMO" readonly>
                                            </div>
                                            <div class="controls">
                                                <input data-validation="required" data-validation-error-msg="Champs requis"
                                                       class="span12" placeholder="... Votre nom ..." name="s_name"
                                                       type="text">
                                            </div>
                                            <div class="controls">
                                                <input data-validation="email"
                                                   data-validation-error-msg="format d'email incorrect"
                                                   class="span12" placeholder="... Votre email ... " name="s_mail"
                                                   type="text">
                                            </div>

                                            <div class="controls">
                                                <textarea data-validation="required" data-validation-error-msg="Champs requis"
                                                          class="span12" id="textarea" rows="6"
                                                          placeholder="... Votre message ..."
                                                          name="s_message"></textarea>
                                            </div>
                                            <button class="btn btn-default" id="invia_mail_home">Envoyer!</button>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>