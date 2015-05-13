<script type="text/javascript">
	function deletePicture(uid,id) {
    	if (confirm("Szeretnéd törölni a képet? Ha akarod akkor beáliíthatod, hogy egyes képeket csak az osztálytársaid lássák."))
    		window.location.href="editDiak.php?tabOpen=1&action=deletePicture&id="+id+"&uid="+uid;    
	}

	function changeVisibility(uid,id) {
		var c = $('#visibility'+id).attr('checked');
		$.ajax({
			url:"editDiakPictureVisibility.php?id="+id+"&attr="+c+"&uid="+uid,
			type:"GET",
			success:function(data){
				$('#ajaxStatus').html(' Kimetés sikerült. ');
				$('#ajaxStatus').show();
				setTimeout(function(){
				    $('#ajaxStatus').html('');
				    $('#ajaxStatus').hide();
				}, 2000);
			},
	    });
	       
   }

	function clickRadio(id) {
	    $('#pTitle').val($('#pTitle'+id).html());
	    $('#pComment').val($('#pComment'+id).html());
	}
	
	function changeTitle(uid) {
		var t = $('#pTitle').val();
		var c = $('#pComment').val();
		var id = $('input[name=pictureid]:checked', '#formRadio').val();
		if (id>0) {
			$.ajax({
				url:"editDiakPictureTitle.php?id="+(id-1)+"&title="+t+"&comment="+c+"&uid="+uid,
				type:"GET",
				dataType: 'json',
				success:function(data){
				    $('#pTitle'+data.id).html(data.title);
				    $('#pComment'+data.id).html(data.comment);
				    $('#pTitle').val( $('#pTitle'+data.id).html());
					$('#pComment').val($('#pComment'+data.id).html());
					$('#ajaxStatus').html(' Kimetés sikerült. ');
					$('#ajaxStatus').show();
					setTimeout(function(){
					    $('#ajaxStatus').html('');
					    $('#ajaxStatus').hide();
					}, 2000);
}
			});
		}
	       
	}

	function showPicture(picture) {
	    $('#pictureViewer').show('slow');
	    var url = "convertImg.php?width=1024&file="+picture;
	    $('#pictureToView').attr('src',url);
	}
   
</script>


<table class="editpagetable">
	<tr><td colspan="3" style="text-align:center"><?PHP echo( $resultDBoperation ) ?></td></tr>
	<tr><td colspan="3"><p style="text-align:left" ><h2>Képes album:</h2>A feltöltött képeket csak az osztálytársaid látják. Ha akkarod egyeseket megjelölhetsz és akkor mindenki látni fogja.</p></td></tr>
	<tr><td colspan="3">
	<form id="formRadio">
	<?php
		$pictures = getListofPictures($_SESSION['scoolClass'].$_SESSION['scoolYear'],$uid, false) ;
		$notDeletedPictures=1;
		foreach ($pictures as $pict) {
			if (   (   (userIsLoggedOn() || $pict["visibleforall"]=="true") && $pict["deleted"]!="true")  || userIsAdmin() ) {
				$file=$uid."-".$pict["id"];
	?>
			<div style="padding: 10px; display: inline-block;border-radius: 5px;border-style: outset; vertical-align: top;border-width: 1px">
			<a title="<?php echo $pict["title"] ?>" onclick="showPicture('<?php  echo $file ?>');" >
				<img style="width: 200px; height: 200px;" src="convertImg.php?color=eeeeee&thumb=true&file=<?php  echo $file ?>" />
			</a>
			<?php if (userIsAdmin() || (userIsLoggedOn() && $uid==$_SESSION['UID'])) : ?>
				<div style="display: inline-block;">
				<div class="borderbutton borderbuttonedit" ><input  type="radio"  onclick="clickRadio(<?php echo $pict["id"] ?>);" value="<?php echo $pict["id"] +1 ?>" name="pictureid" title="Kép kiválasztás név vagy tartalom módósításhoz" /></div>
				<br />
				<br />
				<br />
				<br />
				<?php if ($pict["visibleforall"]=="true" ):?>
					<div class="borderbutton borderbuttonworld" ><input checked type="checkbox"  onchange="changeVisibility('<?PHP echo($uid) ?>',<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></div>
				<?php else: ?>
					<div class="borderbutton borderbuttonworld" ><input  type="checkbox"  onchange="changeVisibility('<?PHP echo($uid) ?>',<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></div>
				<?php endif ?>
				<br />
				<br />
				<?php if ($pict["deleted"]!="true"): ?>
					<div class="borderbutton" ><a onclick="deletePicture('<?PHP echo($uid) ?>',<?php echo $pict["id"] ?>);" title="Képet töröl"><img src="images/delete.gif" /></a></div>
				<?php endif ?> 
				</div>
			<?php endif ?>
			<div style="width: 220px;height: 35px; ">
			<b><span id="pTitle<?php echo $pict["id"] ?>"><?php echo $pict["title"] ?></span></b><br/>
			<span id="pComment<?php echo $pict["id"] ?>"><?php echo $pict["comment"] ?></span>
			</div>
			</div>
	<?php 
			if ($notDeletedPictures++ % 3 ==0) echo("<br />");
		}
	}
	?>
	</form>
	</td></tr>
	<tr><td colspan="3"><hr/> </td></tr>
	
	<?php if (userIsAdmin() || (userIsLoggedOn() && $uid==$_SESSION['UID'])) : ?>
	<tr><td colspan="3">
		<table>
			<form>
				<tr><td colspan="3">Megjelölt kép</td><td></td>
				<tr><td>A kép címe:</td><td><input type="text" id="pTitle" size="40"/></td></tr>
				<tr>
					<td>A kép tartalma:</td><td><input type="text" id="pComment" size="40"/></td>
					<td>
						<input type="button" value="kiment"  style="width:70px" class="submit2"  onclick="changeTitle(<?PHP echo($uid) ?>)"/>
						&nbsp; <span style="padding:3px; background-color: lightgreen; border-radius:4px; display: none;" id="ajaxStatus"></span>
					</td>
				</tr>
			</form>
			<?php if ($notDeletedPictures<24 || userIsAdmin()) :?>
			<tr><td colspan="3"><hr>Kép feltöltése</td></td>
			<tr>
				<form enctype="multipart/form-data" action="editDiak.php" method="post">
					<td>Válassz egy képet max. 2MByte</td><td><input class="submit2" name="userfile" type="file" size="44" accept=".jpg" /></td>	
					<td><input class="submit2"  style="width:70px" type="submit" value="feltölt"/></td>
					<input type="hidden" value="upload" name="action" />
					<input type="hidden" value="<?PHP echo($uid) ?>" name="uid" />
					<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
				</form>
			</tr>
			<?php endif ?>
	</td></tr>
	<?php endif ?>
	
</table>

