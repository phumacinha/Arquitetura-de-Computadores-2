<?php
require_once("../classes/GeneralPredictor.php");
header('Content-Type: application/json');

$bht = new GeneralPredictor($_POST['m'], 0, $_POST['historySize'], $_POST['initialValue'], $_FILES['file']['tmp_name']);
echo $bht->simulator();

?>