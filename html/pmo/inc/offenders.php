<!--<link rel="stylesheet" href="chosen/docsupport/style.css">
<link rel="stylesheet" href="chosen/docsupport/prism.css">
<link rel="stylesheet" href="chosen/chosen.css">

<script src="chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
<script src="chosen/chosen.jquery.js" type="text/javascript"></script>
<script src="chosen/docsupport/prism.js" type="text/javascript" charset="utf-8"></script>
<script src="chosen/docsupport/init.js" type="text/javascript" charset="utf-8"></script>-->

<style>
    select option{
        width:100%;
        height: auto;
        word-wrap: break-word;
        white-space: normal;
    }

    .multiSelect option{
        background-color: white;
        color: rgba(0,98,255,1.0);
    }

    .multiSelect{
        background-color: white;
    }

    select{
        width:100%;
    }
</style>

<section class="box" id="Offenders" style="padding-top: 0.025%; display: block;">
    <div id="container_ev" class="container" style="margin-top:10%">
        <div class="row-fluid">
            <div class="span12 header">
                <hgroup>
                    <h2><?= $xml->getWord("TitoloTrasgressori_1",$_SESSION['lan']); ?></h2>

                    <h3><?= $xml->getWord("TitoloTrasgressori_2",$_SESSION['lan']); ?></h3>
                </hgroup>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12 content" id="content_offenders">
                <div class="span5">
                    <div class="row-fluid">
                        <div class="row-fluid" style="min-height: 80px;">
                            <?= $xml->getWord("Frase_1_Offenders",$_SESSION['lan']); ?>
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <?= $xml->getWord("Frase_2_Offenders",$_SESSION['lan']); ?>
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <?= $xml->getWord("Frase_3_Offenders",$_SESSION['lan']); ?>
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <?= $xml->getWord("Frase_4_Offenders",$_SESSION['lan']); ?>
                        </div>
                        <div class="row-fluid" style="min-height: 80px;">
                            <?= $xml->getWord("Frase_5_Offenders",$_SESSION['lan']); ?>
                        </div>
                    </div>
                </div>
                <div class="span7">
                    <form id="f_query_offenders">
                        <fieldset>
                            <div class="control-group">
                                <div class="row-fluid" style="min-height: 80px;">
                                    <select data-validation="required" data-validation-error-msg="Campo obbligatorio" style="width: 100%;" id="ComuneVerbale" name="ComuneVerbale">
                                        <?= $strCity_2 ?>
                                    </select>
                                </div>
                                <div class="row-fluid" style="min-height: 80px;">
                                    <input data-validation="number" data-validation-error-msg="Campo obbligatorio" type="text" id="CronoNumeroVerbale" name="CronoNumeroVerbale" style="width: 60px;">/
                                    <select data-validation="required" data-validation-error-msg="Campo obbligatorio" style="width: 20%;" id="CronoAnnoVerbale" name="CronoAnnoVerbale">
                                        <?= $strAnno ?>
                                    </select>/
                                    <select style="width: 20%;" id="CronotVerbale" name="CronotVerbale">
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
                                    <select data-validation="required" data-validation-error-msg="Campo obbligatorio" style="width: 20%;" id="GiornoVerbale" name="GiornoVerbale">
                                    <?= $strGiornoV ?>
                                    </select>/
                                    <select data-validation="required" data-validation-error-msg="Campo obbligatorio" style="width: 20%;" id="MeseVerbale" name="MeseVerbale">
                                        <?= $strMeseV ?>
                                    </select>/
                                    <select data-validation="required" data-validation-error-msg="Campo obbligatorio" style="width: 20%;" id="AnnoVerbale" name="AnnoVerbale">
                                        <?= $strAnnoV ?>
                                    </select>
                                </div>
                                <div class="row-fluid" style="min-height: 80px;">
                                    <select data-validation="required" data-validation-error-msg="Campo obbligatorio" style="width: 20%;" id="OreVerbale" name="OreVerbale">
                                        <?= $strOre ?>
                                    </select>
                                    <select data-validation="required" data-validation-error-msg="Campo obbligatorio" style="width: 20%;" id="MinutiVerbale" name="MinutiVerbale">
                                        <?= $strMin ?>
                                    </select>
                                </div>
                                <div class="row-fluid" style="min-height: 80px;">
                                    <input data-validation="alphanumeric" data-validation-error-msg="Campo obbligatorio" type="text" id="TargaVeicolo" name="TargaVeicolo" style="text-transform:uppercase">
                                </div>
                                <div class="row-fluid" style="min-height: 80px;">
                                    <input type="button" value="<?= $xml->getWord("BtnInformativa",$_SESSION['lan']); ?>" name="infoOffen" onClick="Abilita(false);">
                                    <input data-validation="required" data-validation-error-msg="Campo obbligatorio" type="checkbox" name="informativaOffen" id="informativaOffen" ><?= $xml->getWord("Accettazione",$_SESSION['lan']); ?>
                                </div>
                                <button class="btn btn-default" id="invia_query_home_offenders"><?= $xml->getWord("Submit",$_SESSION['lan']); ?></button>

                    </form>
                </div>
                </fieldset>
            </div>
            <div id="message_offenders"></div>
        </div>
    </div>
</section>

<script>

    var arrayComuni = <?php echo json_encode($arrayComuni); ?>;

    function ControllaComune(el,tipo,idInput){
        switch (tipo)
        {
            case 1 : if(el.value == "")$("#ComuneNascita1").prop('disabled',false);
            else {
                $("#ComuneNascita1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;
            case 2 : if(el.value == "")$("#ComuneResidenza1").prop('disabled',false);
            else {
                $("#ComuneResidenza1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;
            case 3 : if(el.value == "")$("#ComuneDitta1").prop('disabled',false);
            else {
                $("#ComuneDitta1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;
            case 4 : if(el.value == "")$("#ComuneNascitaTrasgressore1").prop('disabled',false);
            else {
                $("#ComuneNascitaTrasgressore1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;
            case 5 : if(el.value == "")$("#ComuneResidenzaTrasgressore1").prop('disabled',false);
            else {
                $("#ComuneResidenzaTrasgressore1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;

        }

    }
    $( document ).ready(function() {
        $('.date').datepicker({
            format: 'dd/mm/yyyy'
        });

    });

    function checkComune(el,idSelect){
        for(var i = 0; i < arrayComuni.length; i++)
        {
            if(arrayComuni[i]["Nome_Comune"] == el.value){
                alert("L'elemento era già presente nella dropdown");
                //console.log(document.getElementById(idSelect).getElementsByTagName('option').attributes[0].value);
                //document.getElementById(idSelect).getElementsByTagName('option')[arrayComuni[i]["CC"]].selected = 'selected';
                document.getElementById(idSelect).value = arrayComuni[i]["CC"];

                if(document.getElementById(idSelect).value!="") {
                    el.disabled = true;
                    el.value = "";
                }
                //$("#"+idSelect+" select").val("'"+arrayComuni[i]["CC"]+"'");
                break;
            }
        }
    }

    function VisualizzaNascondi(el){
        //alert(el.value);
        switch(el.value){
            case "1": $(".Utente").css("display","block"); $(".Trasgressore").css("display","none"); $(".firmaTR").css("display","block"); break;
            case "2": $(".Utente").css("display","block"); $(".Trasgressore").css("display","block"); $(".firmaTR").css("display","block"); break;
            case "3": $(".Utente").css("display","block"); $(".Trasgressore").css("display","block"); $(".firmaTR").css("display","none"); break;
            case "4": $(".Utente").css("display","none"); $(".Trasgressore").css("display","block"); $(".firmaTR").css("display","block"); break;
            default: $(".Utente").css("display","none"); $(".Trasgressore").css("display","none"); break;
        }
    }

    function AbilitaDisabilitaTr(classe,nome){
        //alert(classe+" --- "+nome+" --- "+$("input[name='"+nome+"']:checked").val());

        if ($("input[name='"+nome+"']:checked").val() == "NL" || $("input[name='"+nome+"']:checked").val() == "LR") {
            $("."+classe).css("display","block");
        }
        else {
            $("."+classe).css("display","none");
        }
    }

</script>













