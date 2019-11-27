<?php
require_once("../classes/GHT.php");
header('Content-Type: application/json');

$bht = new GHT($_POST['m'], 2, $_POST['historySize'], $_POST['initialValue'], $_FILES['file']['tmp_name']);
echo $bht->simulator();
?>