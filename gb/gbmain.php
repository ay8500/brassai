<?PHP
include 'config.php';
include 'olddatabase.php';
$ErrorText="";

//Are we administrator or not?
if (!isset($admin)) $admin=false;
if ($admin) $pagefile='gbadmin.php'; else  $pagefile='gb.php';
  
//Action i=insert, k=comment, d=delete ,o=entry is ok
if (isset($_POST["action"])) $action=$_POST["action"]; else $action=""; 

//The page number
$page=1;if (isset($_POST["page"])) $page=$_POST["page"];
		if (isset($_GET["page"])) $page=$_GET["page"];

//Handle insert new entry
$paramId=""; 	
$paramName="";
$paramCity="";
$paramMail="";
$paramWWW="http://"; 
$paramText="";
$paramCode="";
if ($action == 'i') {
  if (isset($_POST["id"]))    $paramId	=$_POST["id"];
  if (isset($_POST["dname"])) $paramName=$_POST["dname"];
  if (isset($_POST["dcity"])) $paramCity=$_POST["dcity"];
  if (isset($_POST["dmail"])) $paramMail=$_POST["dmail"];
  if (isset($_POST["dwww"]))  $paramWWW	=$_POST["dwww"]; 
  if (isset($_POST["dtext"])) $paramText=$_POST["dtext"];
  if (isset($_POST["dcode"])) $paramCode=$_POST["dcode"];
  if (($paramCode!='') && ($paramCode  == $_SESSION['SECURITY_CODE']))  {
	insertEntry($paramId,$paramName,$paramCity,$paramMail,$paramWWW,$paramText);
	include 'sendmail.php';
	$paramId="";$paramName="";$paramCity="";$paramMail="";$paramWWW="";$paramText="";
	$ErrorText=$TXT["SubmitOk"];
  }  else  {
     $ErrorText= $TXT["ErrorSCode"];
  }
}

// Handle insert comment
if ($action == 'k')
{
	$paramId=""; 		if (isset($_POST["id"])) 	$paramId=$_POST["id"];
	$paramcomment="";	if (isset($_POST["comment"])) 	$paramcomment=$_POST["comment"];
	if ( $paramId>0 )  {
		setEntrycommentById($paramId, $paramcomment);
	}
}

// Handle delete entry
if ($action == 'd') 
{
	$paramId=""; 	
	if (isset($_POST["id"])) $paramId=$_POST["id"];
	if  ($paramId>0 ) {
		setEntryStatusById($paramId,'d');
		$ErrorText=$TXT["DeleteOk"];
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
  <title><?PHP echo($TXT["Title"]); ?></title>
  <link rel="stylesheet" type="text/css" href="gb.css" />
   <meta name="robots" content="index,follow" />
   <meta name="author" content="Levente Maier" />
   <meta name="description" content="Vendegkönyv Gästebuch" />
</head>
<body>
	<table border="0" width="100%" >
 	<tr>
 	<td width="100%">
		<table align="center" border="0">
			<tr><td class="title"><?PHP echo($TXT["Title"]); ?> <?PHP if ($admin) { ?>&nbsp;<a href="gb.php">Admin</a><?PHP } ?></td></tr>
			<? if ($ErrorText!="") {?>
				<tr><td class="navi" ><?=$ErrorText?></td></tr>
			<?}?>
			<tr><td class="navi" ><?PHP echo($TXT["EntryCountBegin"].getNumberOfEntrys().$TXT["EntryCountEnd"]);?></td></tr>
		</table>
		<form name='Formular' method='POST' action='<?=$pagefile?>'>
			<input type='hidden' name='action' value='i'>
			<input type='hidden' name='page' value='1'>
			<input type='hidden' name='id' value='<?=getNextId();?>'>
		<div align='center'>
		<table border="0" cellspacing="0" cellpadding="2" width='<?=$ciGbWidth?>' >
		<tr>
			<td class='einrand' height='7' width='120' colspan='2' rowspan='2'><div align='center'><?PHP echo($TXT["EnterEntry"]);?></div></td>
			<td height="6" colspan='2'></td>
		</tr><tr>
			<td class="einrandrechtsoben" >&nbsp;</td>
			<td width="5">&nbsp;</td>
		</tr><tr>
			<td rowspan="6" width="5">&nbsp;</td>
			<td class='einrandlinks'><div class ="texta"><?=$TXT["Name"]?></div></td>
			<td class='einrandrechts' ><input type='text' name='dname' size='44' value='<?=$paramName?>' maxlength='100' class='feld' onFocus="this.style.backgroundColor='#f3f3f3'" onblur="this.style.backgroundColor='#AFAFAF'" ></td>
			<td rowspan="6" width="5">&nbsp;</td>
		</tr><tr>
			<td class='einrandlinks'><div  class ="texta" ><?=$TXT["Place"]?></div></td>
			<td class='einrandrechts' ><input type='text' name='dcity' maxlength='30' size='44' value='<?=$paramCity?>' maxlength='30' class='feld' onFocus="this.style.backgroundColor='#f3f3f3'" onblur="this.style.backgroundColor='#AFAFAF'"  </td>
		</tr><tr>
			<td class='einrandlinks'><div  class ="texta" ><?=$TXT["Mail"]?></div></td>
			<td class='einrandrechts' ><input type='text' name='dmail' size='44' value='<?=$paramMail?>' maxlength='100' class='feld' onFocus="this.style.backgroundColor='#f3f3f3'" onblur="this.style.backgroundColor='#AFAFAF'"  </td>
		</tr><tr>
			<td class='einrandlinks'><div  class ="texta" ><?=$TXT["Homepage"]?></div></td>
			<td class='einrandrechts' ><input type='text' name='dwww' value='<?=$paramWWW?>' maxlength='100' size='44'  class='feld' onFocus="this.style.backgroundColor='#f3f3f3'" onblur="this.style.backgroundColor='#AFAFAF'" ></td>
		</tr><tr>
			<td class='einrandlinks'><div  class ="texta" ><?=$TXT["EntryText"]?></div></td>
			<td class='einrandrechts' ><textarea id='dtext' name='dtext' COLS='41' ROWS='6' class='feld' onFocus="this.style.backgroundColor='#f3f3f3'" onblur="this.style.backgroundColor='#AFAFAF'"><?=$paramText?></textarea><p/></td>
		</tr><tr>
			<td class='einrandlinks'><div  class ="texta" ><?=$TXT["SCode"]?></div></td>
			<td class='einrandrechts'  >
				<table>
				<tr> 
					<td><input type='text' name='dcode' size='10' value='' maxlength='10' class='feld' onFocus="this.style.backgroundColor='#f3f3f3'" onblur="this.style.backgroundColor='#AFAFAF'" ></td>
					<td style="background-color: #FFFFFF"><img name="captchaimg" alt="" src="SecurityImage.php" /></td>
					<td><div style="font-size:10px"><?=$TXT["SText"]?></div></td>
				</tr></table>
			</td>
		</tr>
		<tr>
			<td class='einrand' width='<?=$ciGbWidth?>'  colspan="4" >
			<table width="100%" border="0"><tr><td style="text-align:center">
				<input type="submit" value="<?=$TXT["Submit"]?>" class="submit" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="reset"  value="<?=$TXT["Delete"]?>" class="submit" />
			</td></tr></table></td>
		</tr>
		<tr>
			<td colspan="4" class="navi" >
			<table border="0" width="100%">
			<tr><td class="navi" width="100" style="text-align:right" >
				<?php if ($page>1) {?>
					<a href="<?=$pagefile?>?page=<?=$page-1?>"><?=$TXT["MenuPrevPageTT"]?></a>
				<?php } else {?>
					&nbsp;
				<?php }?>
				</td>
				<td class="navi" width="100" style="text-align:center">
					<?=$TXT["Page"]?> <?=$page?> / <?=round((getNumberOfEntrys() / $ciEntrysPerPage) +0.4999) ?>
				</td>
				<td class="navi" width="100" style="text-align:left">
				<?php if ($page*$ciEntrysPerPage<getNumberOfEntrys()) {?>
					<a href="<?=$pagefile?>?page=<?=$page+1?>"><?=$TXT["MenuNextPageTT"]?></a>
				<?php } else {?>
					&nbsp;
				<?php }?>	
				</td>
			</tr>
			</table>
		</tr>
	</td>
    </table>
	</div></form>
	</td></tr>
	<tr><td><div align='center'>
	<table width="<?=$ciGbWidth?>" border="0" cellspacing="0" cellpadding="2">
	<?php 
	$maxEntry = getNumberOfEntrys() - ($page-1)*$ciEntrysPerPage -1;
	$minEntry = $maxEntry - $ciEntrysPerPage +1;
	if ($minEntry<0) { $minEntry=0;}
	for ( $i=$maxEntry;$i>=$minEntry;$i--)
	{
		$entry=getEntryByIndex($i);
	?>
	<tr><td class='einrand' rowspan="2" colspan="3" ><?=$TXT["EntryFrom"]?> <b><?=$entry['name'];?></b></td><td height='5' colspan='4'></tr>	
	<tr><td class="einrandrechtsoben" colspan="3">&nbsp;</td></tr>
	<tr><td width='5'>&nbsp;</td>
	    <td width="10%" class="einrandlinks" ><div class="texta"><?=$TXT["Place"]?></div></td><td width="40%" class="back3" colspan="1"><?=$entry['city'];?></td>
	    <td width="10%" class="back3" 	 ><div class="texta"><?=$TXT["Mail"]?></div></td><td width="40%" class="back3" colspan="1"><?=$entry['mail'];?></td><td class="einrandrechts">&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td width='5'>&nbsp;</td>
	    <td width="10%" class="einrandlinks" ><div class="texta"><?=$TXT["Date"]?></div></td><td width="40%" class="back3" colspan="1"><?=$entry['date'];?></td>
	    <td width="10%" class="back3" 	 ><div class="texta"><?=$TXT["Homepage"]?></div></td><td width="40%" class="back3" colspan="1"><?=$entry['www'];?></td><td class="einrandrechts">&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td width='5'>&nbsp;</td>
	    <td width="10%" class="einrandlinks" ><div class="texta"><?=$TXT["EntryText"]?></div></td><td width="70%" class="back2" colspan="3"><?=$entry['text'];?></td><td class="einrandrechts">&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td width='5'></td><td colspan="4" class="einrandlinks"><img src="t.gif" height="2" width="100%" border="0" alt=""></td><td colspan="1" class="einrandrechts"><img src="t.gif" height="2" width="100%" border="0" alt=""><td></td></td></tr>
	<?php if ($entry['comment']!='') {?>	
		<tr><td width='5'>&nbsp;</td><td width="10%" class="einrandlinks" ><div class="texta"><?=$TXT["Comment"]?></div></td><td width="70%" class="back2" colspan="3"><?=$entry['comment'];?></td><td class="einrandrechts">&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td width='5'></td><td colspan="4" class="einrandlinks"><img src="t.gif" height="2" width="100%" border="0" alt=""></td><td colspan="1" class="einrandrechts"><img src="t.gif" height="2" width="100%" border="0" alt=""><td></td></td></tr>
	<?php }?>
	<tr><td  class='einrand' colspan="7">		
	  <table border="0" width="100%"><tr>
	  <form action="<?=$pagefile?>" method="post">
	  <td  class="back1" colspan="1" style="text-align:center" height="20px">
	  <?php if ($admin || ($_SERVER['REMOTE_ADDR']==$entry['ip'])) {?> 	
		<input class="submit" type="submit" value="<?=$TXT["DeleteEntry"]?>" />
		<input type="hidden" name="page" value="1"/><input type="hidden" name="action" value="d"/><input type="hidden" name="id" value="<?=$entry['id']?>"/>
	  <?php } else {?> &nbsp <?php } ?>
  	  </td></form>
	  <form action="<?=$pagefile?>" method="post"><td class="back1" colspan="2"  style="text-align:center" height="20px">
	  <?php if ($admin) {?>	
		   <input class="submit" type="submit" value="<?=$TXT["SetComment"]?>" />&nbsp;
		   <input class="button" type="text" name="comment" value="" size="40"/>
		   <input type="hidden" name="page" value="<?=$page?>"/>
		   <input type="hidden" name="action" value="k"/>
		   <input type="hidden" name="id" value="<?=$entry['id']?>"/>
	  <?php } else {?>&nbsp<?php }?>	
 	  </td></form><td><div align="right"><?=$i+1?></td>
	  </tr></table>
	  </td></tr>
	  <tr><td colspan="7">&nbsp;</td></tr>
	<?php }?>
	</table></div></td></tr>
</table>
<?php if ($TXT["Footer"][0]!="~") echo($TXT["Footer"]); ?>  
</body>
</html>