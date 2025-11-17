<?php
require_once('cost-sarida-gitco.php');

define("DB_NAME", 'traffic_law');

define("MAIN_DB",'sarida');
define("MENU_ID",3);



define("PAGE_NUMBER",20);
define ("IMG_LANGUAGE", serialize (array ('','f_ita.png','f_eng.png','f_ger.png','f_spa.png','f_fre.png')));
define ("LANGUAGE", serialize (array ('','Ita','Eng','Ger','Spa','Fre')));
define ("RENT", serialize (array ('','Locatario/Noleggiante','Lessee','Mieter','Arrendador','Loueur')));


define ("ADDITIONAL_NIGHT", serialize (array ("",
        ". Ai sensi dell'articolo 195 comma 2 bis la sanzione e' aumentata di un terzo quando la violazione e' commessa dopo le ore 22.00 e prima delle ore 7.00 (sanzione notturna)",
        ". According to Article 195 c. 2 bis the amount of the fine is increased of one-third when the violation has been committed after 22.00 and before 07.00  (night sanction)",
        ". Nach der Artikel 195 c. 2 bis  der Betrag wird um ein Drittel erhoeht wenn die Uebertretung nach 22.00 Uhr und bevor 07.00 begehen wird. (naechtliche Uebertretung)",
        ". De conformidad con el articulo 195, parrafo 2 bis del Codigo de circulacion, la sancion se incrementa en un tercio cuando el delito se comete despues de las 22.y antes de las 7 (penalidad nocturna)",
        ". En vertu de l'article 195, paragraphe 2 bis code la route, la peine est augmentee d'un tiers lorsque l'infraction est commise apres 22  heures et avant 7  heures (peine de nuit)"
    )
));



define ("ADDITIONAL_MASS", serialize (array ("",
        ". Se la violazione e' commessa alla guida di uno dei veicoli indicati alle voci b, e, f, g, h, i, l di cui all'art. 142 comma 3 la sanzione è raddoppiata",
        ". If the violation has been committed with one of the vehicle specified at article 142 c. 3 point b, e, f, g, h, I, l, the amount of the fine will be doubled",
        ". Wenn die Uebertretung mit einem angegebenen Fahrzeug im Artikel 142 c.3 punkt e, f, g, h, i, l, begehen wird, wird den Betrag verdoppeln",
        ". Si la infraccion se comete a la conduccion de uno de los vehiculos indicados en los articulos b, e, f, g, h, i, a que se refiere el art. 142 parrafo 3 codigo de circulacion, la pena se duplica",
        ". Si la violation est commise a la conduite d'un des vehicules indiques aux points b, e, f, g, h, i, l vises à l'art. 142 paragraphe 3 code de la route, la peine est doublee"
    )
));



define ("PAGE_GLOBAL", serialize (array ('','prn_payment.php')));



define("LAN","Ita");



define("FINE_TOLERANCE",5);
define("FINE_TOLERANCE_PERC",5);


define("FINE_PARTIAL",0.7);
define("FINE_MAX",0.5);
define("FINE_NIGHT",3);
define("FINE_MASS",2);


define("FINE_HOUR_START_DAY",7);
define("FINE_MINUTE_START_DAY",0);
define("FINE_HOUR_END_DAY",22);
define("FINE_MINUTE_END_DAY",0);


define("MASS",3.5);


