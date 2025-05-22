<?php
$servername = "localhost";
$username ="root";
$password = "";
$dbname = "sales_spy";

try{
  $pdo = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, Pdo::ERRMODE_EXCEPTION);
  
}catch(PDOException $e){
  echo "connection failed: ".$e->getMessage();
}

