<?php
require_once('Predictor.php');

class GHT extends Predictor {



    
    private function indexCalculator ($address) {
        $shift = substr(base_convert($address, 16, 2), 0, -2);
        $index = substr($shift, -$this->m);
        return base_convert($index, 2, 10);
    }
}
?>