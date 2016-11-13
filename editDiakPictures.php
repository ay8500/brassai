<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
<table class="editpagetable">
	<?php if (isAktUserTheLoggedInUser() ) :?>
		<tr><td colspan="3"><p style="text-align:left" ><h2>Képes album:</h2>A feltöltött képeket csak az osztálytársaid látják. Ha akkarod eggyeseket megjelölhetsz és akkor mindenki látni fogja.</p></td></tr>
	<?php endif ?>
	<tr><td colspan="3">
	<form id="formRadio">
	<?php
		$notDeletedPictures=0;
		$pictures = $db->getListofPictures(getPersonId($diak),"person",2,2) ;
		foreach ($pictures as $pict) {
			if (  $pict["isDeleted"]==0  || userIsAdmin() ) {
				$file=$pict["file"];
				$checked="";
				if ($pict["isVisibleForAll"]==1) $checked="checked";
				$notDeletedPictures++;
		?>
		<div style="padding: 10px;margin: 10px; display: inline-block;border-radius: 10px;border-style: outset; vertical-align: top;border-width: 1px; background-color: white">
			<div style="display: inline-block;border-radius:10px;" >
				<a title="<?php echo $pict["title"] ?>" onclick="showPicture('<?php echo $file?>',<?php echo $pict["isVisibleForAll"]==1 || userIsAdmin()?1:0 ?>);" href="#">
					<img style="width:200px; height:200px;" src="convertImg.php?color=ffffff&thumb=true&file=<?php echo $file?>" />
				</a>
			</div>
			<?php if ( userIsAdmin() || userIsEditor() || isAktUserTheLoggedInUser()) : ?>
				<div style="display: inline-block;">
				<div class="borderbutton borderbuttonedit" ><input  type="radio"  onclick="clickRadio(<?php echo $pict["id"] ?>);" value="<?php echo ($pict["id"]); ?>" name="pictureid" title="Kép kiválasztás név vagy tartalom módósításhoz" /></div>
				<br />
				<div class="borderbutton borderbuttonworld" ><input <?php echo $checked ?> type="checkbox"  onchange="changeVisibility(<?php echo $pict["id"] ?>);" id="visibility<?php echo $pict["id"]?>" title="ezt a képet mindenki láthatja, nem csak az osztálytársaim" /></div>
				<br />
				<?php if ($pict["isDeleted"]==0): ?>
					<div class="borderbutton" ><a onclick="deletePicture('<?PHP echo($personid) ?>',<?php echo $pict["id"] ?>);" title="Képet töröl"><img src="images/delete.gif" /></a></div>
				<?php endif ?> 
				</div>
			<?php endif ?>
			<div style="width: 220px;height: 35px; ">
				<b><span id="pTitle<?php echo $pict["id"] ?>"><?php echo $pict["title"] ?></span></b><br/>
				<span id="pComment<?php echo $pict["id"] ?>"><?php echo $pict["comment"] ?></span>
			</div>
		</div>
	<?php 
		}
	} if ($notDeletedPictures==0) {
	?>
		<h3>Nincsenek képek feltöltve.</h3>
	<?php }?>
	</form>
	</td></tr>
	<tr><td colspan="3"><hr/> </td></tr>
	
	<?php if (userIsAdmin() || isAktUserTheLoggedInUser()) : ?>
	<tr><td colspan="3">
		<table>
			<form>
				<tr><td colspan="3">Megjelölt kép</td><td></td>
				<tr><td>A kép címe:</td><td><input type="text" id="pTitle" size="40" class="form-control" /></td></tr>
				<tr>
					<td>A kép tartalma:</td>
					<td><input type="text" id="pComment" size="40" class="form-control" /></td>
					<td>
						<input type="button" value="kiment"  style="width:70px;" class="btn btn-default"  onclick="changeTitle(<?PHP echo($personid) ?>)"/>
						&nbsp; <span style="padding:3px; background-color: lightgreen; border-radius:4px; display: none;" id="ajaxStatus"></span>
					</td>
				</tr>
			</form>
			<?php if ($notDeletedPictures<100 || userIsAdmin()) :?>
			<tr><td colspan="3"><hr>Kép feltöltése</td></td>
			<tr>
				<form enctype="multipart/form-data" action="editDiak.php" method="post">
					<td>Válassz egy képet max. 2MByte</td><td><input class="btn btn-default" name="userfile" type="file" size="44" accept=".jpg" /></td>	
					<td><input class="btn btn-default"  style="width:70px" type="submit" value="feltölt"/></td>
					<input type="hidden" value="upload" name="action" />
					<input type="hidden" value="<?PHP echo($personid) ?>" name="uid" />
					<input type="hidden" value="<?PHP echo($tabOpen) ?>" name="tabOpen" />
				</form>
			</tr>
			<?php endif ?>
	</td></tr>
	<?php endif ?>
	
</table>
<div id="pictureViewer" class="pictureView">
	<button class="btn  btn-default glyphicon glyphicon-remove-circle" onclick="$('#pictureViewer').hide('fast');"> Bezár</button> 
	<br/>
	<img id="pictureToView" class="img-responsive" src="" />
	<br/>
	<button class="btn  btn-default glyphicon glyphicon-remove-circle" onclick="$('#pictureViewer').hide('fast');"> Bezár</button> 
</div>

</div>
</td>
</tr>
</table>
	</div>
</div>
<?php include 'homefooter.php';?>

<script type="text/javascript">

	$(function() {
		$(document).keyup(function(e) {
		    if (e.keyCode == 27)  
	    		$('#pictureViewer').hide('slow');
		});
	});

	function deletePicture(uid,id) {
    	if (confirm("Szeretnéd törölni a képet? Ha akarod akkor beáliíthatod, hogy egyes képeket csak az osztálytársaid lássák."))
    		window.location.href="editDiak.php?tabOpen=1&action=deletePicture&id="+id+"&uid="+uid;    
	}

	function changeVisibility(id) {
		var c = $('#visibility'+id).prop('checked')?1:0;
		$.ajax({
			url:"editDiakPictureVisibility.php?id="+id+"&attr="+c,
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

	function showPicture(picture,visibleforall) {
		if(visibleforall==1) {
	    var url = "convertImg.php?width=1024&file="+picture;
	    	$('#pictureToView').attr('src',url);
	    	$('#pictureViewer').show('slow');
		} else {
			alert("Ezt a képet csak bejelentkezett osztály vagy iskolatársak tekinthetik meg.");
		}
	    
	}
   
</script>

