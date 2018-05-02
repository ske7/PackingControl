<?php
require_once("config.php");
require_once("dbfunc.php");
require_once("func.php");
session_start(); 
?>
<!DOCTYPE html>
<html>
  <head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
     <meta name="viewport" content="width=device-width">
     <title>Packing Control - Packing</title>
     <link rel="stylesheet" type="text/css" media="screen" href="styles.css">
     <script type="text/javascript">
         "use strict";

         window.onload = addListeners;
         function addListeners() {
           document.getElementById('addremdiv').addEventListener('mousedown', mouseDown, false);
           window.addEventListener('mouseup', mouseUp, false);
         }

         function mouseUp()  {
           window.removeEventListener('mousemove', divMove, true);
         }

         function mouseDown(e) {
           window.addEventListener('mousemove', divMove, true);
         }

         function divMove(e){
           var div = document.getElementById('addremdiv');
           div.style.top = e.clientY + 'px';
         }

         function checkfix() {
           var fixch = document.getElementById('fixch')
           if (fixch.checked == true) {
             document.getElementById('addremdiv').classList.add('fixed');
           } else {
             document.getElementById('addremdiv').classList.remove('fixed');
           }
         }  
 
         function ajaxRequest() {
             try // Non IE Browser?
             { // Yes
                 var request = new XMLHttpRequest()
             } catch (e1) {
                 try // IE 6+?
                 { // Yes
                     request = new ActiveXObject("Msxml2.XMLHTTP")
                 } catch (e2) {
                     try // IE 5?
                     { // Yes
                         request = new ActiveXObject("Microsoft.XMLHTTP")
                     } catch (e3) // There is no AJAX Support
                     {
                         request = false
                     }
                 }
             }
             return request
        } 

        function myFormSubmit(event) {
          if (event.keyCode != 13) {
            return
          }
          event.preventDefault()
          var id = event.target.id
          var docno = document.getElementById('input1').value;
          var productcode = document.getElementById(id).value;
          if (id == 'input3') {
            var opparam = 'add'
            var a3 = document.forms[1].a3.value
          } else {
            var opparam = 'remove'
            var a3 = document.forms[2].a3.value
          }
          if (productcode == "") {return}       
          var params = "pdocno="+docno+"&productcode="+productcode+"&operation="+opparam+"&a3="+a3;
          var request = new ajaxRequest();
    
          request.open("POST", "getlivetable.php", true)
          request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

          request.onreadystatechange = function() {
            if (this.readyState == 4)
            {
            if (this.status == 200)
            {
            if (this.responseText != null)
            { 
              if (this.responseText.substr(-9) == 'NoProduct') {
                document.getElementById('containeraudio').innerHTML = this.responseText.substr(0, this.responseText.length-9)                
                alert('No such product on packing list!');
              } else { 
                document.getElementById('maintablebody').innerHTML = this.responseText
              }
            }
            else alert("ERROR AJAX: Data not received")
            }
            else alert( "ERROR AJAX: " + this.statusText)
            }
          }
          request.send(params)  
          document.getElementById(id).value = "" 
        }
    </script>
  </head>

  <body>
    <?php 
    $pm = 2; 
    require_once("menu.php");
    $currdocno = "";
    $currworker = "";

    if (isset($_GET['ackey']) && !empty($_GET['ackey'])) {
      $currdocno = sanitizeString($_GET['ackey']);
      $_SESSION['docno'] = $currdocno;
    }   

    if (isset($_GET['worker']) && !empty($_GET['worker'])) {
      $currworker = sanitizeString($_GET['worker']);
      $_SESSION['worker'] = $currworker;
    }       

    if (isset($_POST['retinf']) && 
        isset($_POST['docno']) &&
        isset($_POST['worker']) &&
        !empty($_POST['docno']) &&
        !empty($_POST['worker'])) 
    {
      $currdocno  = sanitizeString($_POST['docno']);
      $currworker = sanitizeString($_POST['worker']);
    } else {
      if (isset($_SESSION['docno']) && !empty($_SESSION['docno'])) {
        $currdocno = $_SESSION['docno'];
      }    
      if (isset($_SESSION['worker']) && !empty($_SESSION['worker'])) {
        $currworker = $_SESSION['worker'];
      }
    }  
    $a1 = "";
    $a2 = "";
    $a3 = 0;
    if (!empty($currdocno) && !empty($currworker)) 
    { 
      $_SESSION['worker'] = $currworker; 
      $_SESSION['docno'] = $currdocno; 

      $sql = "exec dbo._APC_spCheckNewDoc ?, ?";
      $params = array(array($currdocno, SQLSRV_PARAM_IN), array($currworker, SQLSRV_PARAM_IN));
      $stmt = PrepareSQL($conn, $sql, $params); 
      ExecureStatement($stmt);
       
      $sql = "select m.acReceiver as Buyer, convert(varchar(16), m.adDate, 104) as dDate, cast(count(*) as int) as Qty
            	from tHE_Move m 
            	inner join tHE_MoveItem mi on m.acKey = mi.acKey
	            where m.acKey = ?
	            group by m.acKey, m.acReceiver, m.adDate";
      $stmt = QuerySQL($conn, $sql, array($currdocno));
      FetchSQL($stmt);
      $a1 = sqlsrv_get_field($stmt, 0);
      $a2 = sqlsrv_get_field($stmt, 1);
      $a3 = sqlsrv_get_field($stmt, 2);

      sqlsrv_free_stmt($stmt);
    }

    if (isset($_POST['confirmbtn']) && !empty($currdocno) && !empty($currworker) && !empty($a3)) {
      $sql = "exec dbo._APC_spInvoiceStatusUpdate ?, ?";
      $params = array(array($currdocno, SQLSRV_PARAM_IN), "Confirmed");
      $stmt = PrepareSQL($conn, $sql, $params); 
      ExecureStatement($stmt);
      sqlsrv_free_stmt($stmt);
      
      if (!checkalldone($conn, $currdocno)) {
        echo "<script type='text/javascript'>alert('Packing confirmed!');</script>"; 
      }

      // Clear page
      $currdocno = "";
      $_SESSION['docno'] = "";
      $currworker = "";
      $_SESSION['worker'] = "";      
      $a1 = "";
      $a2 = "";
      $a3 = 0;      
    }  

    if (isset($_POST['towaitlistbtn']) && !empty($currdocno) && !empty($currworker) && !empty($a3)) {
      $sql = "exec dbo._APC_spInvoiceStatusUpdate ?, ?";
      $params = array(array($currdocno, SQLSRV_PARAM_IN), "Pending");
      $stmt = PrepareSQL($conn, $sql, $params); 
      ExecureStatement($stmt);
      sqlsrv_free_stmt($stmt);

      // Clear page
      $currdocno = "";
      $_SESSION['docno'] = "";
      $currworker = "";
      $_SESSION['worker'] = "";      
      $a1 = "";
      $a2 = "";
      $a3 = 0;        
    }      
    ?>

    <div class="headinfo">Packing contol - Packing</div>
    <br>
    <form method="post" id="form1" action="<?=$_SERVER['SCRIPT_NAME']?>">
      <table class="tableform"> 
        <tr> 
          <td>
            <pre><span>              </span></pre>
          </td>        
          <td class="labeltext1"> 
            <label for="input1">Document Number:</label>
          </td>
          <td style="text-align: left;"> 
            <input id="input1" type="text" name="docno" form="form1" size=25 <?php if (empty($currdocno)) {echo "autofocus";} ?> required value='<?php echo "$currdocno"; ?>'> 
          </td>
          <td>
            <pre><span>            </span></pre>
          </td>
          <td class="labeltext1"> 
            <label for="input2">Worker:</label> 
          </td>
          <td style="text-align: left;"> 
            <input id="input2" type="text" name="worker" form="form1" size=25 <?php if (empty($currworker) && !empty($currdocno)) {echo "autofocus";} ?> required value='<?php echo "$currworker"; ?>'> 
          </td>
          <td>
            <pre><span>              </span></pre>
          </td>          
        </tr> 
        <tr> 
          <td>
            <pre><span>              </span></pre>
          </td>        
          <td class="labeltext2"> 
            <label>Buyer:</label>
          </td>
          <td class="infotext"> 
            <label><?php echo $a1; ?></label> 
          </td>
          <td>
            <pre><span>            </span></pre>
          </td>
          <td class="labeltext2"> 
            <label>Date:</label> 
          </td>
          <td class="infotext"> 
            <label><?php echo $a2; ?></label>  
          </td>
          <td>
            <pre><span>              </span></pre>
          </td>          
        </tr> 
        <tr> 
          <td>
            <pre><span>              </span></pre>
          </td>        
          <td> 
          </td>
          <td> 
              <input class="subbtn" type="submit" name="retinf" value="Retrieve" style="display:none;">
          </td>
          <td>
            <pre><span>            </span></pre>
          </td>
          <td class="labeltext2"> 
            <label>Total Pcs:</label> 
          </td>
          <td class="infotext"> 
            <label><?php echo $a3 !==0 ? $a3 : ""; ?></label>  
          </td>
          <td>
            <pre><span>              </span></pre>
          </td>          
        </tr>       
      </table>
    </form>
    <div align="center" class="fixed" id="addremdiv">
      <table class="tableform" style="width: 60%; margin-top: 10px; background: rgba(221,221,204, 0.7);">
        <tr>
          <td style="text-align: center;">
            <form method="post" id="form2"> 
              <input type="hidden" name="a3" id="a3" value='<?php echo "$a3"; ?>'>
              <label style="color: darkblue; font-weight: bold;">Add (+): </label><input onkeypress="return myFormSubmit(event)" id="input3" type="text" name="productadd" 
                <?php if (((!empty($currworker) && !empty($currdocno))) || (isset($_POST['addbtn']))) {echo "autofocus";} ?> required value="">            
            </form>
          </td>
          <td><input type="checkbox" id="fixch" value="fixch" onchange="checkfix();">Fix</td>
          <td style="text-align: center;">
            <form method="post" id="form3"> 
              <input type="hidden" name="a3" id="a3" value='<?php echo "$a3"; ?>'>            
              <label style="color: darkblue; font-weight: bold;">Remove (-): </label><input onkeypress="return myFormSubmit(event)" id="input4" type="text" name="productrem" value="">
            </form>
          </td>
        </tr>
      </table>
    </div>
    <div class="container" id="container" align="center">
    <div id="containeraudio" align="center" style="width: 0;"></div>
    <table id="maintable" class="bordered" align="center">
      <thead>
      <tr>
        <th>Product code</th>
        <th>Barcode</th>          
        <th>Name</th>
        <th>Ordered (Qty)</th>
        <th>Counted</th>
        <th>Difference</th>
        <th>Status</th>                       
      </tr>
      </thead>
      <tbody id="maintablebody" name="maintablebody">
      <?php 
      if (!empty($currdocno) && !empty($currworker)) { 
        $sql = "select * from dbo._APC_fGetInvoiceItems(?)";
        $stmt = QuerySQL($conn, $sql, array($currdocno));
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))  
        {  
          echo "<tr>";
          echo "<td>".$row['acIdent']."</td>";
          echo "<td>".$row['acCode']."</td>";         
          echo "<td>".$row['acName']."</td>";
          echo "<td style='text-align: center;'>".$row['anQty']."</td>";
          echo "<td style='text-align: center;'>".$row['QtyCounted']."</td>";
          echo "<td style='text-align: center;'>".$row['QtyDiff']."</td>";   
          echo "<td style='text-align: center;'><img src='".checkdiffretimg($row['QtyDiff'])."'></td>";               
          echo "</tr>";                                            
        } 
        sqlsrv_free_stmt($stmt);
      }
      ?>
      </tbody>
    </table>
    </div>
    <div align="center">
      <table class="table1" style="border: 0px; box-shadow: 0 0px 0px #ccc; width: 50%; margin-top: 10px; background: #f8f8f8;">
        <tr>
          <td style="text-align: center;">
            <form method="post" id="form4" action="<?=$_SERVER['SCRIPT_NAME']?>"> 
              <input class="subbtn" type="submit" name="confirmbtn" value="OK - Confirm">
            </form>
          </td>
          <td style="text-align: center;">
            <form method="post" id="form5" action="<?=$_SERVER['SCRIPT_NAME']?>"> 
              <input class="subbtn" type="submit" name="towaitlistbtn" value="Send to Waiting List">
            </form>
          </td>
        </tr>
      </table>
    </div>  
    <script>document.getElementById('addremdiv').classList.remove('fixed');</script>
  </body>
</html>