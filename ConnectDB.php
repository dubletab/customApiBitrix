<?php


function connectToDB(){


    $hostname="localhost";
    $username="admin";
    $password="admin";
    $dbname="sitemanager";

    $link = mysqli_connect($hostname,$username, $password) or die ("html>script language='JavaScript'>alert('Не удается подключиться к базе данных. Повторите попытку позже.'),history.go(-1)/script>/html>");
    mysqli_select_db($link, $dbname);
    return $link;

}

?>