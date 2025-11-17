function strrpos(haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0));
    return i === -1 ? false : i;
}

function compute_CF(cognome, nome, tipo, data, cod_comune) {
    alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    vocali = "AEIOU";
    numeri = "0123456789";
    mesi = "ABCDEHLMPRST";
    alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
    numeri_disp = "10   2 3 4   5 6 7 8 9";

    CF = "";
    code = 0;
    if (tipo == "D") return "";

    // Determina
    for (var i = 0; i <= 1; i++) {
        if (i == 0) {
            word = cognome;
        }
        else {
            word = nome;
        }

        word = word.replace(" ", "");
        word = word.replace("\'", "");
        word = word.replace("à", "a");
        word = word.replace("è", "e");
        word = word.replace("é", "e");
        word = word.replace("ì", "i");
        word = word.replace("ò", "o");
        word = word.replace("ù", "u");
        word = word.toUpperCase();

        extracted_cons = "";
        extracted_vocs = "";

        for (var j = 0; j < word.length; j++) {
            char = word.substr(j, 1);
            isthere = strrpos(vocali, char);
            if (isthere === false) // NOTA: I tre "=" sono voluti.
                extracted_cons = extracted_cons + char;
            else
                extracted_vocs = extracted_vocs + char;
        }

        num_cons = extracted_cons.length;
        num_vocs = extracted_vocs.length;

        if (num_cons > 3 && i == 1) {
            CF = CF + extracted_cons.substr(0, 1) + extracted_cons.substr(2, 2);
        }
        else if (num_cons > 2) {
            CF = CF + extracted_cons.substr(0, 3);
        }
        else if (num_cons == 2 && num_vocs > 0) {
            CF = CF + extracted_cons + extracted_vocs.substr(0, 1);
        }
        else if (num_cons == 1 && num_vocs == 1) {
            CF = CF + extracted_cons + extracted_vocs + "X";
        }
        else if (num_cons == 1 && num_vocs > 1) {
            CF = CF + extracted_cons + extracted_vocs.substr(0, 2);
        }
        else if (num_cons == 0 && num_vocs > 2) {
            CF = CF + extracted_vocs.substr(0, 3);
        }
        else if (num_cons == 0 && num_vocs == 2) {
            CF = CF + extracted_vocs + "X";
        }
        else return "";
    }

    array_data = new Array();
    array_data = data.split("/");
    anno = array_data[2];

    CF = CF + anno.substr(2, 2);

    CF = CF + mesi.substr(array_data[1] - 1, 1);

    giorno = parseInt(array_data[0]);

    if (tipo == 'M') {
        giorno += 100;
        giorno = giorno.toString();
        gg = giorno.substr(1, 2);
    }
    else {
        giorno += 140;
        giorno = giorno.toString();
        gg = giorno.substr(1, 2);
    }

    CF = CF + gg;

    CF = CF + cod_comune;

    for (var i = 0; i < CF.length; i++) {
        char = CF.substr(i, 1);
        if ((i % 2) == 0) // NOTA: se i   pari, cio  se la lettera   dispari.
            code = code + strrpos(numeri_disp, char) + strrpos(alfabeto_disp, char);
        else
            code = code + strrpos(numeri, char) + strrpos(alfabeto, char);
    }

    CF = CF + alfabeto.substr((code % 26), 1);

    if (CF.length != 16) return "";

    return CF;
}