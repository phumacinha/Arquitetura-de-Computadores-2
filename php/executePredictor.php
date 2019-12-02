<?php
require_once("classes/GeneralPredictor.php");
$type = $_POST['type'];
$m = $_POST['m'];
$n = isset($_POST['n']) && !empty($_POST['n']) ? $_POST['n'] : 0;
$historySize = $_POST['historySize'];
$initialValue = $_POST['initialValue'];
$file = $_FILES['file']['tmp_name'];


$predictor = new GeneralPredictor($m, $n, $historySize, $initialValue, $file);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../modules/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../styles/response.css">
    <title>Branch Predictor Simulator (<?php echo $type; ?>)</title>
</head>
<body>

    <div id="response"></div>


    <script src="../modules/popper.js/popper.min.js"></script>
    <script src="../modules/jquery/jquery-3.4.1.min.js"></script>
    <script src="../modules/bootstrap/bootstrap.min.js"></script>
    <script src="../js/Predictor.js"></script>
    

    <script>
        $(document).ready(function(){
            var response = '#response'
            var predictor = new Predictor(<?php echo "{$m}, {$n}, {$historySize}, '{$initialValue}', {$predictor->simulator()}"; ?>, response);
            predictor.createTable()
        })
        
    </script>
</body>
</html>