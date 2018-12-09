$( document ).ready(
		function() {showAllCandles();
});

function showPersonCandle(id) {
	hideLightCandle(id);
	$.ajax({
		url: "ajax/getCandleLighters.php?id="+id,
		success:function(data) {
			$("#personlist"+id).html(data);
			$("#person-candle"+id).show();
		}
	});
}

function hidePersonCandle(id) {
	$("#person-candle"+id).hide();
}

function showLightCandle(id) {
	hidePersonCandle(id);
	$("#light-candle"+id).show();
}

function hideLightCandle(id) {
	$("#light-candle"+id).hide();
}
function lightCandle(id) {
	candles[id] = ++candles[id];
    showWaitMessage();
	$.ajax({
		url: "ajax/setCandleLighter.php?id="+id,
		success:function(data) {
			clearModalMessage();
			showCandles(id,candles[id]);
			$("#light-button"+id).prop("disabled",true);
		},
		error: function(data) {
			alert('Nem sikerült.');
		}
	}) ;
}

var candles = {
	<?php 
		global $personList;
		$firstValue = true;
		foreach ($personList as $d) {
			if ($firstValue)
				$firstValue=false;
			else 
				echo(',');
			echo ''.$d["id"].':"'.getActualCandles($d['id']).'"';
		}
	?>
};

function showAllCandles() {
	for (var candle in candles) { 
		showCandles(candle,candles[candle]);
	}
	
}

function showCandles(id,candles) {
	var candleSize = candles>30?30:candles;
	var html="";
		for ( var x = 1; x <= 9 && x<= candles; x++) {
		html +='<img src="images/candle'+(Math.floor(Math.random() * 6)+1)+'.gif" style="width: '+(candleSize*2+30)+'px;">';
	}
	$("#candles"+id).html(html);
	$("#candles"+id).prop('title', candles+' gyertya ég. Gyújts te is gyertyát emlékére.');
}