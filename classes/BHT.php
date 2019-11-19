<?php
class BHT {
    const TAKE = "T";
    const NOT_TAKE = "N";

    private $n;
    private $m;
    private $bht;
    private $iter;
    private $file;


    public function __construct ($m, $n, $initialValue, $file) {
        $this->n = $n;
        $this->m = $m;
        $this->bht = [];
        $this->iter = [];
        $this->file = $file;

        $history = $n == 2 ? [$initialValue, $initialValue] : [$initialValue];

        for ($i = 0; $i < $m; $i++) {
            array_push($this->bht, ["history"=>$history, "prediction"=>$initialValue, "correct"=>0, "incorrect"=>0, "precision"=>0]);
        }
    }

    public function simulator () {
        flush();
        $data = fopen($this->file, "r");
        
        while(!feof($data))
        {
            $trace = fgets($data);
            $trace = explode(" ", $trace);

            $address = trim($trace[0]);
            $branch = trim($trace[1]);

            $shift = substr(base_convert($address, 16, 2), 0, -2);
            $index = substr($shift, -log($this->m, 2));
            $decimalIndex = base_convert($index, 2, 10);

            $line = &$this->bht[$decimalIndex];

            $historic = [$line];

            $correct = true;

            if ($line["prediction"] == $branch) {
                $line["correct"]++;
            }
            else {
                $correct = false;
                $line["incorrect"]++;
            }


            switch ($this->n) {
                case 1:
                    $line["history"][0] = $branch;
                    $line["prediction"] = $branch;
                    break;
                case 2:
                    $line["history"][0] = $line["history"][1];
                    $line["history"][1] = $branch;

                    if ($line["history"][0] == $line["history"][1]) {
                        $line["prediction"] = $branch;
                    }
                    break;
            }
            
            
            $line["precision"] =  $line["correct"]/($line["correct"] + $line["incorrect"]);

            array_push($historic, $line);
            array_push($this->iter, [$correct, $decimalIndex, $historic, $address]);

            

            flush();
        }
        fclose($data);

        return json_encode($this->iter);
    }
}
?>