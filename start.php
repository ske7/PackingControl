<?php
require_once("config.php");
session_start();
?>
<!DOCTYPE html>
<html>
  <head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
     <meta name="viewport" content="width=device-width">
     <title>Packing Control - Start/Intructions</title>
     <link rel="stylesheet" type="text/css" media="screen" href="styles.css">
  </head>

  <body>
    <?php $pm = 1; require_once("menu.php"); ?>
    <div class="headinfo">Packing contol - Start/Intructions</div>
    <br>
    <div class="info"><pre><?php include_once(SITE_ROOT."/resource/help.txt"); ?></pre></div>
  </body>
</html>