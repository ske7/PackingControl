<?php 
require_once("config.php");
require_once("dbfunc.php");
require_once("func.php");
session_start();
ob_clean(); 

if (isset($_POST['pdocno']) && !empty($_POST['pdocno']) &&
    isset($_POST['productcode']) && !empty($_POST['productcode']) &&
    isset($_POST['a3']) && !empty($_POST['a3']) &&    
    isset($_POST['operation'])) {
  ob_clean();      
  $currdocno = sanitizeString($_POST['pdocno']);
  $productcode = sanitizeString($_POST['productcode']);
  $operation = sanitizeString($_POST['operation']);
  $sql = "exec dbo._APC_spCount ?, ?, ?";
  $params = array(array($currdocno, SQLSRV_PARAM_IN), array($productcode, SQLSRV_PARAM_IN), $operation);
  $stmt = QuerySQL($conn, $sql, $params);
  FetchSQL($stmt);
  $ret1 = sqlsrv_get_field($stmt, 0);    
  $ret2 = sqlsrv_get_field($stmt, 1);         
  $ret3 = sqlsrv_get_field($stmt, 2); 
  $ret4 = sqlsrv_get_field($stmt, 3);         
  sqlsrv_free_stmt($stmt);
  checkdiffandplaysound($ret1, $ret2, $ret3, $ret4, $conn, $currdocno);
  $sql = "select * from dbo._APC_fGetInvoiceItems(?)";
  $stmt = QuerySQL($conn, $sql, array($currdocno));
  $display_string = "";
  while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))  
  {  
    $display_string .= "<tr>";
    $display_string .= "<td >".$row['acIdent']."</td>";
    $display_string .= "<td>".$row['acCode']."</td>";         
    $display_string .= "<td>".$row['acName']."</td>";
    $display_string .= "<td style='text-align: center;'>".$row['anQty']."</td>";
    $display_string .= "<td style='text-align: center;'>".$row['QtyCounted']."</td>";
    $display_string .= "<td style='text-align: center;'>".$row['QtyDiff']."</td>";   
    $display_string .= "<td style='text-align: center;'><img src='".checkdiffretimg($row['QtyDiff'])."'></td>";               
    $display_string .= "</tr>";                                            
  } 
  sqlsrv_free_stmt($stmt); 
  if ($ret1 === "NoProduct") {  
    $display_string = $ret1;
  }
  echo trim($display_string); 
}
?>