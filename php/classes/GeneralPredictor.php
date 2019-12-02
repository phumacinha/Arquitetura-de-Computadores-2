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

    private $totalCorrect;

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
        $this->totalCorrect = 0;

        //initialize table
        $this->initializeTable();

        //initialize counters
        $this->initializeCounters();

        //initialize global history
        $this->initializeGlobalHistory();
    }

    private function getM () {
        return $this->m;
    }

    private function getN () {
        return $this->n;
    }

    private function getFile () {
        return $this->file;
    }

    private function getInitialValue () {
        return $this->initialValue;
    }

    private function getTableLineByIndex ($index) {
        return $this->table[$index];
    }

    private function getIterations () {
        return $this->iter;
    }

    private function getTotalIter () {
        return count($this->iter);
    }

    private function getTotalCorrect () {
        return $this->totalCorrect;
    }

    private function getGlobalPrecision () {
        return $this->getTotalCorrect()/(count($this->getIterations())+1);
    }

    private function totalCorrectIncrement () {
        $this->totalCorrect++;
    }

    private function insertIter ($line) {
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

    private function baseIndexCalculator ($address, $base=10) {
        $shift = substr(base_convert($address, 16, 2), 0, -2);
        $index = substr($shift, -$this->getM());
        return $this->getM() == 0 ? "" : base_convert($index, 2, $base);
    }

    private function updateCorrect ($index, $branch) {
        $line = &$this->table[$index];

        $correct = true;

        if ($line["prediction"] == $branch) {
            $line["correct"]++;
            $this->totalCorrectIncrement();
        }
        else {
            $correct = false;
            $line["incorrect"]++;
        }

        return $correct;
    }

    private function updateHistory ($index, $branch) {
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

    private function updateCounter ($index, $branch) {
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

    private function updatePrediction ($index) {
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

    private function updatePrecision ($index) {
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

    private function indexCalculator ($address, $base=10) {
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
            
            $this->insertIter([$correct, $decimalIndex, $historic, $address, $this->getGlobalPrecision()]);

            flush();
        }
        fclose($data);

        return json_encode($this->getIterations());
    }
    
}
?>