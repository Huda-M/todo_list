<?php
$host = 'localhost';
$userName = "root";
$password = "";
$database = "todolist";
$conn = mysqli_connect($host,$userName,$password,$database);

if(!$conn){
    die("connection failed : " .  mysqli_connect_error());
}


