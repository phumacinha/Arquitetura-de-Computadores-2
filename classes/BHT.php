<?php
require_once('Predictor.php');

class BHT extends Predictor {
    private $file;

    public function __construct ($m, $historySize, $initialValue, $file) {
        parent::__construct($m, 0, $historySize, $initialValue, $file);
        $this->file = $file;
    }

   

    public function simulator () {
        flush();
        $data = fopen(parent::getFile(), "r");
        
        while (!feof($data)) {
            $trace = fgets($data);
            $trace = explode(" ", $trace);

            $address = trim($trace[0]);
            $branch = trim($trace[1]);

            $decimalIndex = parent::baseIndexCalculator($address);

            $history = [parent::getTableLineByIndex($decimalIndex)];

            $correct = parent::updateCorrect($decimalIndex, $branch);
            parent::updateHistory($decimalIndex, $branch);
            parent::updateCounter($decimalIndex, $branch);
            parent::updatePrediction($decimalIndex);
            parent::updatePrecision($decimalIndex);

            array_push($history, parent::getTableLineByIndex($decimalIndex));
            
            parent::insertIter([$correct, $decimalIndex, $history, $address]);

            flush();
        }
        fclose($data);

        return json_encode(parent::getIteractions());
    }
}
?>