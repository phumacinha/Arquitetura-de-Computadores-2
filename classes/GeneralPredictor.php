<?php
class GeneralPredictor {
    protected const TAKE = "T";
    protected const NOT_TAKE = "N";
    
    private $m;
    private $n;
    private $historySize;
    private $initialValue;
    private $file;
    private $table;
    private $iter;
    private $counter;
    private $globalHistory;

    public function __construct ($m, $n, $historySize, $initialValue, $file) {
        $this->m = log($m, 2) - $n;
        $this->n = $n;
        $this->historySize = $historySize;
        $this->initialValue = $initialValue;
        $this->table = [];
        $this->iter = [];
        $this->counter = [];
        $this->globalHistory = [];
        $this->file = $file;

        //initialize table
        $this->initializeTable();

        //initialize counters
        $this->initializeCounters();
    }

    protected function getM () {
        return $this->m;
    }

    protected function getN () {
        return $this->n;
    }

    protected function getFile () {
        return $this->file;
    }

    protected function getInitialValue () {
        return $this->initialValue;
    }

    protected function getTableLineByIndex ($index) {
        return $this->table[$index];
    }

    protected function getIteractions () {
        return $this->iter;
    }

    protected function insertIter ($line) {
        array_push($this->iter, $line);
    }

    private function initializeTable () {
        $history = $this->historySize == 2 ? [$this->initialValue, $this->initialValue] : [$this->initialValue];

        for ($i = 0; $i < pow(2, $this->getM() + $this->getN()); $i++) {
            array_push($this->table, ["history"=>$history, "prediction"=>$this->initialValue, "correct"=>0, "incorrect"=>0, "precision"=>0]);
        }

        return $this->table;
    }

    private function initializeCounters ($strong=true) {
        for ($i = 0; $i < pow(2, $this->getM() + $this->getN()); $i++) {
            array_push($this->counter, $this->returnCounter($this->initialValue, $strong));
        }
    }

    private function initializeGlobalHistory () {
        $this->globalHistory = [];
        for ($i = 0; $i < $this->getN(); $i++) {
            array_push($this->globalHistory, $this->getInitialValue());
        }
    }

    private function returnCounter ($value, $strong=true) {
        switch ($value) {
            case self::TAKE:
                switch ($this->historySize) {
                    case 1:
                        return 1;
                    break;

                    case 2:
                        return ($strong ? 3 : 2);
                    break;
                }
            break;

            case self::NOT_TAKE:
                switch ($this->historySize) {
                    case 1:
                        return 0;
                    break;

                    case 2:
                        return ($strong ? 0 : 1);
                    break;
                }
            break;
        }
    }

    protected function baseIndexCalculator ($address, $base=10) {
        $shift = substr(base_convert($address, 16, 2), 0, -2);
        $index = substr($shift, -$this->getM());
        return base_convert($index, 2, $base);
    }

    protected function updateCorrect ($index, $branch) {
        $line = &$this->table[$index];

        $correct = true;

        if ($line["prediction"] == $branch) {
            $line["correct"]++;
        }
        else {
            $correct = false;
            $line["incorrect"]++;
        }

        return $correct;
    }

    protected function updateHistory ($index, $branch) {
        $line = &$this->table[$index];

        switch ($this->historySize) {
            case 1:
                $line["history"][0] = $branch;
                break;
            case 2:
                $line["history"][0] = $line["history"][1];
                $line["history"][1] = $branch;
                break;
        }
    }

    protected function updateCounter ($index, $branch) {
        switch ($branch) {
            case self::TAKE:
                switch ($this->historySize) {
                    case 1:
                        //1-bit predictor
                        //counter saturates at 1
                        $this->counter[$index] = 1;
                    break;

                    case 2:
                        //2-bit predictor
                        //counter saturates at 3
                        if (++$this->counter[$index] > 3)
                            $this->counter[$index] = 3;
                    break;
                }
            break;

            case self::NOT_TAKE:
                if (--$this->counter[$index] < 0)
                    $this->counter[$index] = 0;
            break;
        }
    }

    protected function updatePrediction ($index) {
        $line = &$this->table[$index];
        $counter = $this->counter[$index];

        switch ($this->historySize) {
            case 1:
                switch ($counter) {
                    case 0:
                        $line["prediction"] = self::NOT_TAKE;
                    break;
                    
                    case 1:
                        $line["prediction"] = self::TAKE;
                    break;
                }
            break;
            
            case 2:
                if ($counter < 2) 
                    $line["prediction"] = self::NOT_TAKE;
                else
                    $line["prediction"] = self::TAKE;
            break;
        }
    }

    protected function updatePrecision ($index) {
        $line = &$this->table[$index];
        $line["precision"] =  $line["correct"]/($line["correct"] + $line["incorrect"]);
    }

    private function updateGlobalHistory ($branch) {
        array_unshift($this->globalHistory, $branch);
        array_pop($this->globalHistory);
    }

    private function convertGlobalHistoryToBin () {
        $bin = "";
        foreach ($this->globalHistory as $branch) {
            switch ($branch) {
                case self::TAKE:
                    $bin .= "1";
                break;

                case self::NOT_TAKE:
                    $bin .= "0";
                break;
            }
        }

        return $bin;
    }

    public function indexCalculator ($address, $base=10) {
        $binIndex = $this->baseIndexCalculator($address, 2) . $this->convertGlobalHistoryToBin();

        return base_convert($binIndex, 2, $base);
    }

    public function simulator () {
        flush();
        $data = fopen($this->getFile(), "r");
        
        while (!feof($data)) {
            $trace = fgets($data);
            $trace = explode(" ", $trace);

            $address = trim($trace[0]);
            $branch = trim($trace[1]);

            $decimalIndex = $this->indexCalculator($address);

            $historic = [$this->getTableLineByIndex($decimalIndex)];

            $correct = $this->updateCorrect($decimalIndex, $branch);
            $this->updateHistory($decimalIndex, $branch);
            $this->updateCounter($decimalIndex, $branch);
            $this->updatePrediction($decimalIndex);
            $this->updatePrecision($decimalIndex);
            $this->updateGlobalHistory($branch);

            array_push($historic, $this->getTableLineByIndex($decimalIndex));
            
            $this->insertIter([$correct, $decimalIndex, $historic, $address]);

            flush();
        }
        fclose($data);

        return json_encode($this->getIteractions());
    }
    
}
?>