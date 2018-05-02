<?php
require_once("config.php");
require_once("dbfunc.php");
session_start();
?>
<!DOCTYPE html>
<html>
  <head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
     <meta name="viewport" content="width=device-width">
     <title>Packing Control - Waiting list</title>
     <link rel="stylesheet" type="text/css" media="screen" href="styles.css">
  </head>

  <body>
    <?php $pm = 3; require_once("menu.php"); ?>
    <div class="headinfo">Packing contol - Waiting list</div>
    <br>
    <div align="center">
    <table class="bordered" style="margin-top: 0px;">
      <thead>
      <tr>
          <th style="width: 150px;">Document [acKey]</th>        
          <th style="width: 250px;">Buyer</th>
          <th style="width: 130px;">Worker</th>
      </tr>
      </thead>
      <tbody>
      <?php 
      $sql = "select * from dbo._APC_vGetWaitingList";
      $stmt = QuerySQL($conn, $sql, array());
      while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))  
      {  
          echo "<tr>";
          echo "<td style='text-align: center;'><a href=".dirname($_SERVER['PHP_SELF'])."/pack.php?ackey=".$row['acKey']."&worker=".$row['Worker'].">".$row['acKey']."</a></td>";      
          echo "<td>".$row['acReceiver']."</td>";
          echo "<td style='text-align: center;'>".$row['Worker']."</td>";
          echo "</tr>";                                            
      } 
      sqlsrv_free_stmt($stmt);
      ?>
      </tbody>
    </table>
    </div>
  </body>
</html>