<?php
require_once("Bollettino.php");

class Bollettini extends ArrayObject {
    public function offsetSet($key, $val) {
        if ($val instanceof Importi) {
            return parent::offsetSet($key, $val);
        }
        throw new InvalidArgumentException('Il valore deve essere un oggetto Bollettino.');
    }
}