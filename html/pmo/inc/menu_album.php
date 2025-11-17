<section  style="padding-top: 0.025%; display: block;">
    <div class="album py-5 bg-light">
        <div class="container" style="margin-top:10%">
            <div class="content">
                <div class="row-fluid" >
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/Verbali.jpg" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/Minutes"><h2><?= $aMenu[$_SESSION['lan']][0] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Consultazione</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/DichiarazioneTras.jpg" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/Offenders"><h2><?= $aMenu[$_SESSION['lan']][9] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Comunicazione dati patente, detrazione punti</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/Legislazione.jpg" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/Legislation"><h2><?= $aMenu[$_SESSION['lan']][1] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Prima informazione sulla normativa vigente</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/Collaudo.png" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/Approval"><h2><?= $aMenu[$_SESSION['lan']][2] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Modalità di ricerca certificati collaudi/taratura</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-fluid" style="margin-top: 2%;">

                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/Omologazione.png" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/Omologation"><h2><?= $aMenu[$_SESSION['lan']][3] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Modalità di ricerca decreti</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/Documenti.jfif" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/Documentation"><h2><?= $aMenu[$_SESSION['lan']][4] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Principali modelli utili e scaricabili</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/FAQ.jfif" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/FAQ"><h2><?= $aMenu[$_SESSION['lan']][5] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Informazioni generali</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    if($lang == 'ita' || $lang == ''){
                    ?>
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/Notizie.jpg" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/News"><h2><?= $aMenu[$_SESSION['lan']][6] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Aggiornamenti normativa</p>
                                </div>
                            </div>
                        </div>
                    </div>

                        <?php
                    }
                    ?>
                </div>
                <div class="row-fluid" style="margin-top: 2%;">
                    <?php
                    if($lang == 'ita' || $lang == ''){
                        ?>
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/Sentenza.jfif" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/History"><h2><?= $aMenu[$_SESSION['lan']][7] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Giurisprudenza in materia</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                    <div class="span3">
                        <div class="card shadow-sm">
                            <img width="100%" style="height: 180px;" src="img/menu/Contatti.jfif" />

                            <div class="card-body" style="text-align: center;">
                                <p class="card-text"><a href="#/Contact"><h2><?= $aMenu[$_SESSION['lan']][8] ?></h2></a></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p>Contatti Sarida S.r.l.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>