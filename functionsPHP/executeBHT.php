<?php
require_once("../classes/BHT.php");
header('Content-Type: application/json');

$bht = new BHT($_POST['m'], $_POST['n'], $_POST['initialValue'], $_FILES['file']['tmp_name']);
echo $bht->simulator();
?>