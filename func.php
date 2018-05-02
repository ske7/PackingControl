<?php 
require_once("config.php");
require_once("dbfunc.php");

function sanitizeString($var)
{
  $var = strip_tags($var);
  $var = htmlentities($var);
  return stripslashes($var);
}

function checkalldone($conn, $acurrdocno)
{
  $sql = "select top 1 QtyDiff
          from dbo._APC_PackedInvoices WHERE acKey = ?
          and QtyDiff <> 0";
  $stmt = QuerySQL($conn, $sql, array($acurrdocno));              
  FetchSQL($stmt);
  $ret = (int)sqlsrv_get_field($stmt, 0) === 0;
  sqlsrv_free_stmt($stmt); 
  return $ret;
}

function checkdiffretimg($adiff)
{
  $imgyellow = dirname($_SERVER['PHP_SELF'])."/resource/st_Yellow.jpg";
  $imgred    = dirname($_SERVER['PHP_SELF'])."/resource/st_Red.gif";
  $imggreen  = dirname($_SERVER['PHP_SELF'])."/resource/st_Green.jpg";

  switch(true)
  {
    case $adiff == 0: return $imggreen; break;
    case $adiff > 0: return $imgred; break;
    case $adiff < 0: return $imgyellow; break;                
    default: return $imgyellow; break;        
  }
}

function checkdiffandplaysound($ret1, $ret2, $ret3, $ret4, $conn, $currdocno)
{
  $wrongproductsound = dirname($_SERVER['PHP_SELF'])."/resource/PC_WrongBarCode.mp3";
  $reddotsound       = dirname($_SERVER['PHP_SELF'])."/resource/PC_Surplus.mp3";
  $greendotsound     = dirname($_SERVER['PHP_SELF'])."/resource/PC_GreenDot.mp3";
  $allgreendotsound  = dirname($_SERVER['PHP_SELF'])."/resource/PC_AllGreenDots.mp3";
  $yellowdotsound    = dirname($_SERVER['PHP_SELF'])."/resource/PC_Surplus.mp3";
  $yelltoyellotsound = dirname($_SERVER['PHP_SELF'])."/resource/YellowToYellow.mp3";

  if ($ret1 === "NoProduct") {
    echo "<audio src=$wrongproductsound autoplay></audio>";
    echo "<script type='text/javascript'>alert('No such product on packing list');</script>";  
  } else {
    switch(true)
    {
      case checkalldone($conn, $currdocno): echo "<audio src=$allgreendotsound autoplay></audio>"; break;  
      case $ret3 == 0: echo "<audio src=$greendotsound autoplay></audio>"; break;
      case $ret3 > 0: echo "<audio src=$reddotsound autoplay></audio>"; break;
      case $ret3 == -1 && $ret4 == 0: echo "<audio src=$yellowdotsound autoplay></audio>"; break; 
      case $ret3 < 0 && $ret4 < 0: echo "<audio src=$yelltoyellotsound autoplay></audio>"; break;       
      default: break;        
    }
  }   
}
?>