<?PHP
	if (isset($_GET["paramName"])) $paramName=$_GET["paramName"]; 
		else if (isset($_SESSION["USER"])) 
				$paramName=$_SESSION["USER"];
			else
				$paramName="";
	if (isset($_GET["paramMail"])) $paramMail=$_GET["paramMail"]; 
		else if (isset($_SESSION["MAIL"])) 
			$paramMail=$_SESSION["MAIL"];
			else 
				$paramMail="";
	if (isset($_GET["paramComment"])) $paramComment=$_GET["paramComment"]; else $paramComment="";
	if (isset($_GET["paramQ"])) $paramQ=$_GET["paramQ"]; else $paramQ="";
	if (isset($_GET["paramC"])) $paramC=$_GET["paramC"]; else $paramC="";
	if (isset($_GET["paramSI"])) $paramSI=$_GET["paramSI"]; else $paramSI="";
	if (isset($_GET["paramImage"])) $paramImage=$_GET["paramImage"]; else $paramImage="";
	if (isset($_GET["paramDeleteKomment"])) $paramDeleteKomment=$_GET["paramDeleteKomment"]; else $paramDeleteKomment="";
	$ErrorText="";
	if ($paramImage!="") {
		if (($paramSI!="") && ($paramSI  == $_SESSION['SECURITY_CODE']) ) 
		{
			if (($paramName!="") && ($paramMail!="") && ($paramComment!="") && (check_email($paramMail)))
			{
				insertComment($paramImage,$paramName,$paramMail,$paramComment,$paramQ,$paramC);
				$paramName="";$paramMail="";$paramComment="";$paramC="1";$paramQ="1";
			}
			else $ErrorText= $TXT["CommentErrorFields"];
		}
		else $ErrorText= $TXT["CommentErrorSCode"];
	}
	if ($paramDeleteKomment!="") deleteComment($paramImage,$paramDeleteKomment);
	$image=$images_array[$slideshow_index];
?>
	<div id="statusbar">
		<table border="0" style="width:100%"><tr><td>
			<b style="font-size:20px"><?PHP echo(getGalleryDescription());?></b>&nbsp;&nbsp; <?PHP echo( $images_count.$TXT["PictCount"]);?>
			</td><td style="text-align:right">
			<?PHP echo "<a href=\"$SCRIPT_NAME?view=slideshow&gallery=$gallery&slideshow_index=$slideshow_index\" title=\"".$TXT["BackTT"]."\">".$TXT["MenuBack"]."</a>"; ?> 
			</td></tr>
		</table>
	</div>
	
<div id="img_area">
<table  id="commentArea">
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td style="text-align:center;">
	<?php echo("<img src=\"$gallery/.thumbs/$thumbnail_size"."_thumb_".$image.".".$thumbnail_filetype."\" title=\"thumbnail\" alt=\"thumbnail\" />");?>
	<br/><?php writeMetaData($image);?></td>
	<td>
		<form action="<?echo($SCRIPT_NAME);?>" method="get">
		<input type="hidden" name="view" value="comment" />
		<input type="hidden" name="slideshow_index" value="<?=$slideshow_index?>" />
		<input type="hidden" name="gallery" value="<?=$gallery?>" />
		<input type="hidden" name="paramImage" value="<?=$image?>" />
		<table id="comment">
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2" style="text-align:center; font-size:20px"><b><?PHP echo($TXT["CommentTitle"]); ?></b></td></tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td><?PHP echo($TXT["CommentName"]); ?></td><td><input type="text" name="paramName" size="40" value="<?php echo($paramName)?>" /></td></tr>
			<tr><td><?PHP echo($TXT["CommentMail"]); ?></td><td><input type="text" name="paramMail" size="40"  value="<?=$paramMail?>" /></td></tr>
			<tr><td><?PHP echo($TXT["CommentText"]); ?></td><td><input type="text" name="paramComment" size="70"value="<?=$paramComment?>" /></td></tr>
			<tr><td><?PHP echo($TXT["CommentQuali"]); ?></td><td>
				<select name="paramQ">
					<option value="0" <?php if ($paramQ=="0") echo("selected") ?>><?php echo $TXT["Ranking"][0]?></option>
					<option value="1" <?php if ($paramQ=="1") echo("selected") ?>><?php echo $TXT["Ranking"][1]?></option>
					<option value="2" <?php if ($paramQ=="2") echo("selected") ?>><?php echo $TXT["Ranking"][2]?></option>
					<option value="3" <?php if ($paramQ=="3") echo("selected") ?>><?php echo $TXT["Ranking"][3]?></option>
					<option value="4" <?php if ($paramQ=="4") echo("selected") ?>><?php echo $TXT["Ranking"][4]?></option>
					<option value="5" <?php if ($paramQ=="5") echo("selected") ?>><?php echo $TXT["Ranking"][5]?></option>
				</select>
			</td></tr>
			<tr><td><?PHP echo($TXT["CommentCont"]); ?></td><td>
				<select name="paramC">
					<option value="0" <?php if ($paramC=="0") echo("selected") ?>><?php echo $TXT["Ranking"][0]?></option>
					<option value="1" <?php if ($paramC=="1") echo("selected") ?>><?php echo $TXT["Ranking"][1]?></option>
					<option value="2" <?php if ($paramC=="2") echo("selected") ?>><?php echo $TXT["Ranking"][2]?></option>
					<option value="3" <?php if ($paramC=="3") echo("selected") ?>><?php echo $TXT["Ranking"][3]?></option>
					<option value="4" <?php if ($paramC=="4") echo("selected") ?>><?php echo $TXT["Ranking"][4]?></option>
					<option value="5" <?php if ($paramC=="5") echo("selected") ?>><?php echo $TXT["Ranking"][5]?></option>
				</select>
			</td></tr>
			<tr>
				<?PHP 
					$secutiryImagePHP ="SecurityImage/SecurityImage";
					$path=getenv("SCRIPT_FILENAME");
					$path=strtr($path,"\\","/");
					$path=substr($path,0,strrpos($path,"/"))."/";
					if (!file_exists($path.$secutiryImagePHP) )  
				        $secutiryImagePHP="../".$secutiryImagePHP; 
				?> 
				<td><?PHP echo($TXT["CommentSCode"]); ?></td>
				<td><img name="paramSI" alt="" src="<?PHP echo($secutiryImagePHP);?>" />&nbsp;<input name="paramSI"  type="text" size="6"/><div style="font-size:10px"><?PHP echo($TXT["CommentSText"]); ?></div></td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2" style="text-align:center"><input type="submit" value="<?PHP echo($TXT["CommentSubmit"]); ?>" /></td></tr>
			<tr><td colspan="2">&nbsp;<?=$ErrorText?></td></tr>
		</table>
		</form>
	</td></tr>
	<tr><td colspan="2"><?php writeComment($image); ?></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
</table>

<?
	function check_email($mail_address) {
		$pattern = "/^[\w-]+(\.[\w-]+)*@";
		$pattern .= "([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4})$/i";
	    if (preg_match($pattern, $mail_address)) {
			$parts = explode("@", $mail_address);
			if (isset($parts[0]) && isset($parts[1])){
				//echo "The e-mail address is valid.";
				return true;
			} else {
				//echo "The e-mail host is not valid.";
				return false;
			}
		} else {
			//echo "The e-mail address contains invalid charcters.";
			return false;
		}
	}
?>