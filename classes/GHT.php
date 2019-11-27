<?php
require_once('Predictor.php');

class GHT extends Predictor {
    private $globalHistory;

    public function __construct ($m, $n, $historySize, $initialValue, $file) {
        parent::__construct($m, $n, $historySize, $initialValue, $file);

        $this->initializeGlobalHistory();
    }

    private function initializeGlobalHistory () {
        $this->globalHistory = [];
        for ($i = 0; $i < parent::getN(); $i++) {
            array_push($this->globalHistory, parent::getInitialValue());
        }
    }

    private function updateGlobalHistory ($branch) {
        array_unshift($this->globalHistory, $branch);
        array_pop($this->globalHistory);
    }

    public function convertGlobalHistoryToBin () {
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

    // $historicoGlobal . endereco
    public function indexCalculator ($address, $base='dec') {
        $binIndex = parent::baseIndexCalculator($address, 'bin') . $this->convertGlobalHistoryToBin();
        if ($base == 'bin') {
            return $binIndex;
        }
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

            $this->updateGlobalHistory($branch);
            $decimalIndex = $this->indexCalculator($address);

            $historic = [parent::getTableLineByIndex($decimalIndex)];

            $correct = parent::updateCorrect($decimalIndex, $branch);
            parent::updateHistory($decimalIndex, $branch);
            parent::updateCounter($decimalIndex, $branch);
            parent::updatePrediction($decimalIndex);
            parent::updatePrecision($decimalIndex);

            array_push($historic, parent::getTableLineByIndex($decimalIndex));
            
            parent::insertIter([$correct, $decimalIndex, $historic, $address]);

            flush();
        }
        fclose($data);

        return json_encode(parent::getIteractions());
    }
}
?>