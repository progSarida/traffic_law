<?php
require_once("Importo.php");

class Importi extends ArrayObject {
    public function offsetSet($key, $val) {
        if ($val instanceof Importi) {
            return parent::offsetSet($key, $val);
        }
        throw new InvalidArgumentException('Il valore deve essere un oggetto Importo.');
    }
}