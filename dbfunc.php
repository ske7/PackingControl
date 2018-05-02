<?php
require_once("config.php");

function SetConnection($adbserver, $adbbase, $adblogin, $adbpass)
{
    $connectionInfo = array("Database" => $adbbase, "UID" => $adblogin, "PWD" => $adbpass, "CharacterSet" => 'UTF-8');
    $conn = sqlsrv_connect($adbserver, $connectionInfo);

    if ($conn === false) {
        echo "Connection could not be established.<br>";
        die(FormatErrors(sqlsrv_errors())); 
    } else {
        return $conn;
    }
}

function PrepareSQL($conn, $sql, $params)
{
    $stmt = sqlsrv_prepare($conn, $sql, $params); 
    if ($stmt === false) {
        echo "Error in prepare statement.<br>";
        die(FormatErrors(sqlsrv_errors()));
        return false;
    }
    return $stmt;    
}

function ExecureStatement($stmt)
{
    $ret = sqlsrv_execute($stmt);
    if ($ret === false) {
        echo "Error in statement execution.<br>";
        die(FormatErrors(sqlsrv_errors()));
        return false;
    }
    return $ret;
}

function QuerySQL($conn, $sql, $params)
{
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false ) {
        echo "Error in statement execution.<br>";  
        die(FormatErrors(sqlsrv_errors())); 
        return false;
    }
    return $stmt;
}

function FetchSQL($stmt)
{
    if (sqlsrv_fetch($stmt) === false) {
        echo "Error in retrieving row.\n";
        die(FormatErrors(sqlsrv_errors())); 
    }    
}

function ShowDBServerInfo($conn) 
{
    $server_info = sqlsrv_server_info($conn);  
    if ($server_info) {  
        foreach($server_info as $key => $value) {  
            echo $key.": ".$value."<br>";  
        }  
    }  
    else {  
        echo "Error in retrieving server info.<br>";  
        die(FormatErrors(sqlsrv_errors()));  
    }
}

function FormatErrors($errors) 
{  
    echo "Error information: <br>";  

    foreach ($errors as $error) {  
        echo "SQLSTATE: ".$error['SQLSTATE']."<br>";  
        echo "Code: ".$error['code']."<br>";  
        echo "Message: ".$error['message']."<br>";  
    }  
}

if (isset($conn) === false) {
    $conn = SetConnection($dbserver, $dbbase, $dblogin, $dbpass);
}
?>