<?php
$serverName = "CS3-DEV.ICT.RU.AC.ZA" ;
$user = "4A2";
$password = "W3bD3vCs3!" ;
$database = "4A2" ; //check the schema's name in workbench

//connection string / statement for oop
$conn = new mysqli($serverName,$user,$password,$database);

if($conn->connect_error){
    die("Connection to server and database failed".$conn->connect_error);
} 

?>