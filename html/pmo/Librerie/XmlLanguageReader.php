<?php
class xmlLanguageReader
{
    private $xml;

    public function __construct($path){
        $this->xml = simplexml_load_file($path);
    }

    public function getWord($nameWord, $language){
        foreach($this->xml->children() as $child)
        {
            if($child->attributes() == $nameWord)
                return $child->$language;
        }
    }
}
?>