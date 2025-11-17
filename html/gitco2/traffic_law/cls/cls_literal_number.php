<?php
class CLS_LITERAL_NUMBER{

    function centinaia($num, $separa = 1){
        $num    = (int) $num;
        $num_s  = sprintf('%03d', $num);
        $string = '';

        $units  = array('', 'uno', 'due', 'tre', 'quattro', 'cinque', 'sei', 'sette', 'otto', 'nove');
        $teens  = array('dieci', 'undici', 'dodici', 'tredici', 'quattordici', 'quindici', 'sedici',
            'diciassette', 'diciotto', 'diciannove');
        $decine = array('', 'dieci', 'venti', 'trenta', 'quaranta', 'cinquanta', 'sessanta', 'settanta', 'ottanta', 'novanta');

        if( strlen($num_s) > 3 OR $num_s == 0 ){
            return;
        }
        else{
            $cifre = array((int)$num_s[0], (int)$num_s[1], (int)$num_s[2]);

            if( $cifre[0] ){
                if( $cifre[0] != 1){
                    $string .= $units[$cifre[0]];
                }
                $string .= 'cento';

                if( $separa ){
                    $string .= ' ';
                }
            }

            if( $cifre[1] ){
                if( $cifre[1] == 1 ){
                    $string .= $teens[$cifre[2]];
                }
                else{
                    if( $cifre[2] == 1 OR $cifre[2] == 8 ){
                        $string .= substr($decine[$cifre[1]], 0, -1);
                    }
                    else{
                        $string .= $decine[$cifre[1]];
                    }
                }
            }

            if( $cifre[2] AND $cifre[1] != 1 ){
                $string .= $units[$cifre[2]];
            }

            return $string;
        }
    }

    function intero_in_lettere($num, $separatore = 0){
        if( !is_string($num) ){
            $num .= "";
        }
        $num = preg_replace('/^0+/', '', $num);

        if( strlen($num) > 15 ){
            return false;
        }
        $many = array('', 'mila', 'milioni', 'miliardi', 'mila');
        $pow_dieci = array('', 'mille', ' un milione ', ' un miliardo ', 'mille');
        $string = '';

        if( (strlen($num) % 3) != 0 ){
            if( strlen($num) > 3 ){
                $num_tmp = substr($num, strlen($num) % 3);
                $terzina = explode('|', wordwrap($num_tmp, 3, '|', 1));

                array_splice($terzina, 0, 0, substr($num, 0, strlen($num) % 3));
            }
            else{
                $terzina = array($num);
            }
        }
        else{
            $terzina = explode('|', wordwrap($num, 3, '|', 1));
        }

        for( $i = 0, $count = count($terzina); $i < $count; $i++ ){
            $terzina[$i] = intval($terzina[$i]);
            $index = $count - 1 - $i;
            if( $terzina[$i] AND ($terzina[$i] != 1 OR $index == 0) ){
                $string .= $this->centinaia($terzina[$i], $separatore);
                $string .= $many[$index];
                if( $index == 4 AND !$terzina[1] ){
                    if( $separatore ){
                        $string .= ' ';
                    }
                    $string .= $many[3];
                }
            }
            elseif($terzina[$i] == 1 AND $index != 0){
                $string .= $pow_dieci[$index];
                if( $index == 4 AND !intval($terzina[1]) ){
                    if( $separatore ){
                        $string .= ' ';
                    }
                    $string .= $many[3];
                }

                if( ($i != $count - 1) AND intval($terzina[$i + 1]) AND $separatore){
                    $string = trim($string) . ' e';
                }
            }

            if( $separatore AND $terzina[$i]){
                $string .= ' ';
            }
        }
        return trim($string);
    }

    function euro2word($euro_val)
    {
        list($euro, $centesimi) = explode('.', $euro_val);
        $centesimi = intval(substr((string) $centesimi, 0, 2));

        $money = $this->intero_in_lettere($euro);
        $money .= ' euro';
        if( $centesimi > 0)
        {
            $money .= ' e ';
            $money .= $this->intero_in_lettere($centesimi);
            $money .= ' centesimi';
        }

        return($money);
    }

    function converti_numero_bollettino($numero)
    {
        $numero = number_format($numero,2,".","");
        list($intero, $decimale) = explode('.', $numero);

        $ritorno = $this->intero_in_lettere($intero);

        if( $decimale > 0)
        {
            $ritorno .= '/';
            $ritorno .= $decimale;
            if(strlen($decimale)==1) $ritorno .= '0';
        }
        else
            $ritorno .= '/00';

        return($ritorno);
    }
}
