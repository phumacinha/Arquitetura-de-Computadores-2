<?php
require_once('Predictor.php');

class GHT extends Predictor {
    private $globalHistory;

    public function __construct ($m, $n, $historySize, $initialValue, $file) {
        parent::__construct($m, $n, $historySize, $initialValue, $file);

        $this->initializeglobalHistory();
    }

    private function initializeglobalHistory () {
        $this->globalHistory = [];
        for ($i = 0; $i < parent::getN(); $i++) {
            array_push($this->globalHistory, parent::getInitialValue());
        }
    }

    private function updateglobalHistory ($branch) {
        array_push($this->globalHistory, $branch);
        array_shift($this->globalHistory);
    }

    private function convertglobalHistoryToBin () {
        $bin = "";
        foreach ($this->globalHistory as $branch) {
            switch ($branch) {
                case parent::TAKE:
                    $bin .= "1";
                break;

                case parent::NOT_TAKE:
                    $bin .= "0";
                break;
            }
        }

        return $bin;
    }

    // $historicoGlobal + endereco
    private function indexCalculator ($address) {
        $binIndex = parent::baseIndexCalculator($address, 'bin') + $this->convertglobalHistoryToBin();
        return base_convert($binIndex, 2, 10);
    }

    public function simulator () {
        flush();
        $data = fopen(parent::getFile(), "r");
        
        while (!feof($data)) {
            $trace = fgets($data);
            $trace = explode(" ", $trace);

            $address = trim($trace[0]);
            $branch = trim($trace[1]);

            $decimalIndex = $this->indexCalculator($address);

            $historic = [parent::getTableLineByIndex($decimalIndex)];

            $correct = parent::updateCorrect($decimalIndex, $branch);
            parent::updateHistory($decimalIndex, $branch);
            parent::updateCounter($decimalIndex, $branch);
            parent::updatePrediction($decimalIndex);
            parent::updatePrecision($decimalIndex);
            $this->updateglobalHistory($branch);

            array_push($historic, parent::getTableLineByIndex($decimalIndex));
            
            parent::insertIter([$correct, $decimalIndex, $historic, $address]);

            flush();
        }
        fclose($data);

        return json_encode(parent::getIteractions());
    }
}
?>