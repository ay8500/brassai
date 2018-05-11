	var loggedInUser=<?php echo getLoggedInUserId()?>;
	var loginShowed=false;

	var text0="<br/>";
	var text1="Kedves osztálytársak, barátok,<br/><br/>....<br/><br/>";
	    text1+="<br>Mindenkit üdvözlök <?php echo userIsLoggedOn()?getLoggedInUserName():''?>";
	var text2="Kedves osztálytársak, barátok,<br/><br>szeretettel köszöntelek a brassaista véndiákok honlapján keresztül.";
	    text2+="<br/><br/>...<br/><br/>Remélem jól vagytok és várjátok újra találkozzunk.<br/><br/>";
	    text2+="Barátsággal <?php echo userIsLoggedOn()?getLoggedInUserName():''?>";
	var text3="Kedves osztálytársak, barátok,<br/><br>sok szeretettel köszöntelek a brassaista véndiákok honlapján keresztül.";
	    text3+="<br/><br/>...<br/><br/>Remélem boldogak, egészségesek vagytok és nagyon várjátok újra találkozzunk.<br/><br/>";
	    text3+="Mindenkit ölelek és puszilok <?php echo userIsLoggedOn()?getLoggedInUserName():''?>";

	
	function sendMessage() {
		if (loggedInUser<0 && loginShowed==false) {
		    $("#login-fields").slideDown("slow");
		    loginShowed=true;
		} else {
			if (confirm("Szeretnéd a beadott üzenetet az osztálytársaidnak elküldeni?")) {
				$("#chatform").submit();
			}
		} 
	}

	
	function setText(type) {
		if( $("#story").trumbowyg('html')==text0 ||
			$("#story").trumbowyg('html')==text1 ||
			$("#story").trumbowyg('html')==text2 ||
			$("#story").trumbowyg('html')==text3 || 
			confirm("A körlevél szövege már módosítva van. Szeretnéd ennek ellenére tartalmát kicserélni?") ){
				if (type==0) {
				    $("#story").trumbowyg('html', text0);
				}else if (type==1) {
				    $("#story").trumbowyg('html', text1);
				}else if (type==2) {
					    $("#story").trumbowyg('html', text2);
				}else if (type==3) {
			    	$("#story").trumbowyg('html', text3);
			}
		}
	}
	
	function showMessage() {
		$("#new-btn").hide();
		$("#message-btn").hide();
		$("#story").trumbowyg('html', text2);
    	$("#message-fields").slideDown("slow");
	}

	function hideMessage() {
		$("#new-btn").show();
		$("#message-btn").show();
		$("#message-fields").slideUp("slow");
	}
