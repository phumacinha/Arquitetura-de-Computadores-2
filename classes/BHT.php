<?php
require_once('Predictor.php');

class BHT extends Predictor {
    private $historySize;
    private $m;
    private $table;
    private $iter;
    private $counter;
    private $file;


    public function __construct ($m, $historySize, $initialValue, $file) {
        $this->historySize = $historySize;
        $this->m = log($m, 2);
        $this->table = [];
        $this->iter = [];
        $this->counter = [];
        $this->file = $file;

        $history = $historySize == 2 ? [$initialValue, $initialValue] : [$initialValue];

        for ($i = 0; $i < $m; $i++) {
            //initialize bht table
            array_push($this->table, ["history"=>$history, "prediction"=>$initialValue, "correct"=>0, "incorrect"=>0, "precision"=>0]);
            
            //initialize counters
            array_push($this->counter, $this->initialCounter($initialValue));
        }
    }

    private function initialCounter ($initialValue, $strong=true) {
        switch ($initialValue) {
            case self::TAKE:
                switch ($this->historySize) {
                    case 1:
                        return 1;
                    break;

                    case 2:
                        if ($strong)
                            return 3;
                        else
                            return 2;
                    break;
                }
            break;

            case self::NOT_TAKE:
                switch ($this->historySize) {
                    case 1:
                        return 0;
                    break;

                    case 2:
                        if ($strong)
                            return 0;
                        else
                            return 1;
                    break;
                }
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

    private function updateCorrect ($index, $branch) {
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

    private function updatePrecision ($index) {
        $line = &$this->table[$index];
        $line["precision"] =  $line["correct"]/($line["correct"] + $line["incorrect"]);
    }

    private function indexCalculator ($address) {
        $shift = substr(base_convert($address, 16, 2), 0, -2);
        $index = substr($shift, -$this->m);
        return base_convert($index, 2, 10);
    }

    public function simulator () {
        flush();
        $data = fopen($this->file, "r");
        
        while (!feof($data)) {
            $trace = fgets($data);
            $trace = explode(" ", $trace);

            $address = trim($trace[0]);
            $branch = trim($trace[1]);

            $decimalIndex = $this->indexCalculator($address);

            $line = &$this->table[$decimalIndex];

            $historic = [$line];

            $correct = $this->updateCorrect($decimalIndex, $branch);
            $this->updateHistory($decimalIndex, $branch);
            $this->updateCounter($decimalIndex, $branch);
            $this->updatePrediction($decimalIndex);
            $this->updatePrecision($decimalIndex);

            array_push($historic, $line);
            array_push($this->iter, [$correct, $decimalIndex, $historic, $address]);

            flush();
        }
        fclose($data);

        return json_encode($this->iter);
    }
}
?>