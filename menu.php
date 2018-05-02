<?php
require_once("config.php");
?>

<div class="menu">
  <ul>
      <li <?php if ($pm == 1) {echo 'id = "current-menu-item"';}?>><a href="start.php">Start</a></li>
      <li <?php if ($pm == 2) {echo 'id = "current-menu-item"';}?>><a href="pack.php">Pack</a></li>
      <li <?php if ($pm == 3) {echo 'id = "current-menu-item"';}?>><a href="waitlist.php">Waiting list</a></li>
  </ul>
</div>