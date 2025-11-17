<?php
include (INC . "/views.php");

/**
 * Classe che simula una vista
 */
class CLS_VIEW{

    public $aliasToColumns;

    public $from;

    public $where;
    
    public $having;

    public $groupBy;
  
    public $union;
    
    public $unionWheres;

    public $viewSelect;

    /**
    * viewdefinition è un array associativo e può avere come valori
    * aliases, un array associativo alias-> colonna
    * from
    * where
    * group by
    */
    public function __construct(array $viewDefinition){
        $this->aliasToColumns = array();
        // TODO considerare un eventuale metodo migliore per gestire il caso
        // Aggiunge uno spazio prima dell'alias per evitare di sostituire alias precedute da "tabella."
        foreach ($viewDefinition['aliases'] as $chiave => $valore){
            $this->aliasToColumns[" " . $chiave] = " $valore";
        }
        
        $this->from = $viewDefinition['from'];
        $this->where = $viewDefinition['where'] ?? null;
        $this->having = $viewDefinition['having'] ?? null;
        $this->groupBy = $viewDefinition['groupBy'] ?? null;
        $this->union = $viewDefinition['union'] ?? null;
        $this->viewSelect = ($viewDefinition['distinct'] ?? false) ? " SELECT DISTINCT " : " SELECT ";
        
        foreach ($this->aliasToColumns as $key => $value){
            $this->viewSelect .= $value . " as " . "$key, ";
        }
        
        $this->viewSelect = substr($this->viewSelect, 0, - 2);
        $this->viewSelect .= " FROM " . $this->from;
    }

    /**
    * genera una select sulla vista aggiungendo delle nuove condizioni, un ordinamento e un limite sulle righe
    */
    public function generateSelect($where2 = null, $having2 = null, $order = null, $limit = null){
        $queryString = $this->viewSelect;
        
        if (! empty($this->where) || ! empty($where2)){
            $queryString .= " WHERE ";
            
            if (! empty($this->where)){
                $queryString .= $this->where . ' ';
            }
        
            if ($where2 != null && ! empty($where2)){
                //Aggancia uno spazio all'inizio della where aggiuntiva dato che non è detto che le venga passato e in tal caso
                //fallirebbe la sostituzione qui sotto perchè nel costruttore ogni alias chiave/valore è costruita con spazio
                if (strpos(substr($where2, 0, 1), ' ') !== false){
                    $where2 = ' '.$where2;
                }
            
                $where2decoded = str_replace(array_keys($this->aliasToColumns), array_values($this->aliasToColumns), $where2);
                $queryString .= ($this->where != null && $this->where != '' ? ' AND ' : '') . $where2decoded . ' ';
            }
        }

        if (! empty($this->groupBy)){
            $queryString .= " GROUP BY " . $this->groupBy;
        }
      
        if (! empty($this->having) || ! empty($having2)){
            $queryString .= " HAVING ";
            
            if (! empty($this->having)){
                $queryString .= $this->having . ' ';
            } else $queryString .= "1=1";
            
            if ($having2 != null && ! empty($having2)){
                //Aggancia uno spazio all'inizio della where aggiuntiva dato che non è detto che le venga passato e in tal caso
                //fallirebbe la sostituzione qui sotto perchè nel costruttore ogni alias chiave/valore è costruita con spazio
                if (strpos(substr($having2, 0, 1), ' ') !== false){
                    $having2 = ' '.$having2;
                }
                
                $havingDecoded = str_replace(array_keys($this->aliasToColumns), array_values($this->aliasToColumns), $having2);
                $queryString .= ($this->having != null && $this->having != '' ? ' AND ' : '') . $havingDecoded;
            }
        }
        
        //Le union devono essere definite come '<identificativoUnion>' => array(Parametri vista....)
        //è possibile agganciare where esterne come array, specificando come indice l'identificativo della union
        //attraverso $this->unionWheres = array('<identificativoUnion>' => <where>....). Se non definita, per ogni union
        //verrà usata la where esterna della vista principale
        if (! empty($this->union)){
            foreach ($this->union as $unionId => $subquery){
                $newCls = new CLS_VIEW($subquery);
                
                $queryString .= " UNION ".$newCls->generateSelect($this->unionWheres[$unionId] ?? $where2, $having);
            }
        }
      
        if ($order != null && ! empty($order)){
            $queryString .= " ORDER BY " . $order;
        }
        
        if ($limit != null && ! empty($limit)){
            $queryString .= " LIMIT " . $limit;
        }
      
        return $queryString;
    }

    /**
     * @deprecated
     */
    public function generateDistinctSelect($where2 = null, $having = null, $order = null, $limit = null){
        $queryString=$this->generateSelect($where2,$having,$order,$limit);
        return str_replace("SELECT ","SELECT DISTINCT", $queryString);
    }

    public function executeSelect($rs, $where, $order, $limit){
        return $rs->SelectQuery($this->generateSelect($where, $order, $limit));
    }
}
?>