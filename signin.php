<?php 
// This code is loaded from start.php wenn a new facebook or internet user wnat to sign in

$resultDBoperation="";

if (!userIsLoggedOn() && getParam("action")=="newUser" && getParam("classtext", "")!="") {
	if ($db->getCountOfRequest(changeType::newuser)>2) {
		$resultDBoperation='<div class="alert alert-warning" >Túl sok bejelenkezést szeretnél létrehozni, kérünk probálkozz késöbb még egyszer!</div>';
	} else {
		if ($db->getPersonByEmail(getParam("email", ""))!=null) {
			$resultDBoperation='<div class="alert alert-warning" >A megadott email cím már létezik, kérünk probálkozz még egyszer egy másik email címmel!</div>';
		} else {
			$classtext=getParam("classtext","");
			$class=$db->getClassByText($classtext);
			if ($class==null) {
				//create a new class
				$classid=$db->saveClass(array(
						"id"=>-1,
						"schoolID"=>getAktSchoolId(),
						"name"=>substr($classtext, 5,3),
						"graduationYear"=>substr($classtext, 0,4),
						"text"=>$classtext));
			} else {
				$classid=getRealId($class);
			}
			if ($classid<0) {
				$resultDBoperation='<div class="alert alert-warning" >Bejelenkezést nem sikerült, kérünk probálkozz késöbb még egyszer!<br/>Hibacód:65132</div>';
			} else {
				if (getIntParam("id", -1)==-1) {
					//create a new person
					$person=getPersonDummy();
					$person["id"]=-1;
					$person["lastname"]=html_entity_decode(getParam("lastname"),ENT_QUOTES,"UTF-8");
					$person["firstname"]=html_entity_decode(getParam("firstname"),ENT_QUOTES,"UTF-8");
					$person["email"]=html_entity_decode(getParam("email"),ENT_QUOTES,"UTF-8");
					$person["role"]=getParam("role","");
					$person["classID"]=$classid;
					$_SESSION["uId"]=0;
					$ret = $db->savePerson($person);
					unset($_SESSION["uId"]);
				} else {
					//update a person
					$person=$db->getPersonByID(getIntParam("id"));
					if ($person!=null)
						$person=$db->getPersonByID(getRealId($person));
					if ($person==null) {
						$ret=-12;
					} else {
						$person["lastname"]=html_entity_decode(getParam("lastname"),ENT_QUOTES,"UTF-8");
						$person["firstname"]=html_entity_decode(getParam("firstname"),ENT_QUOTES,"UTF-8");
						$person["email"]=html_entity_decode(getParam("email"),ENT_QUOTES,"UTF-8");
						$person["facebookid"]=getIntParam("fid",0);
						$_SESSION["uId"]=0;
						$ret = $db->savePerson($person);
						unset($_SESSION["uId"]);
					}
				}
				if ($ret>=0) {
					$db->saveRequest(changeType::newuser);
					$resultDBoperation='<div class="alert alert-info" >Köszünjük szépen!<br/>Bejelenkezési adatok sikeresen kimentve. Hamarosam e-mailtben visszajelezzük a bejelenkezési adatokat.<br/>Jó szorakozást és sikeres kapcsolatfelvételt kivánunk a véndiákok oldalán.</div>';
					setUserInSession($person["role"],$person["user"],$ret);
					sendNewUserMail($person["firstname"], $person["lastname"], $person["email"], $person["passw"], "", $class["graduationYear"], $class["name"],$person["id"]);
				} else {
					$resultDBoperation='<div class="alert alert-warning" >Bejelenkezést nem sikerült, kérünk probálkozz késöbb még egyszer!<br/>Hibacód:64432</div>';
				}
			}
		}
	}
}

?>

<style>
<!--
.fb-radio{width: 25px;height: 25px;padding:20px}
-->
</style>

<div class="sub_title">Bejelentkezés</div>
<div class="container-fluid">
<div class="well">
	<div class="panel panel-default">
		<?php if (!isset($ret)) {?>
		<div class="panel-heading">
			<label id="dbDetails">
				<?php if (isset($_SESSION["FacebookId"])) {?> 
					<div class="left margin-hor"><img src="https://graph.facebook.com/<?php echo $_SESSION['FacebookId']; ?>/picture" /></div>
					<div class="inline">Kedves <?php echo $_SESSION["FacebookName"]?> szeretettel köszöntünk a véndiákok honoldalán.</div>
				<?php  } else {?>
				<div class="inline">Kedves látogató szeretettel köszöntünk a véndiákok honoldalán.</div>
				<?php  } ?>
			</label> 
		</div>
		<div class="resultDBoperation" ><?php echo $resultDBoperation;?></div>
		<div id="page1">
			<h4 class="margin-hor">Kapcsolatom a <?php  echo getAktSchoolName() ?> diákjaival:</h4> 
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(true,1);"/> 
				<div class="inline margin-hor"> Végzös diák vagyok, ebben az iskolában éretségiztem és ballagtam.</div></div>
			<div style="clear:both;"></div>
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(true,2);"/> 
				<div class="inline margin-hor"> Nem éretségiztem ebben az iskolabán, de egy ideig oda jártam.</div></div>
			<div style="clear:both;"></div>
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(false,3);"/> 
				<div class="inline margin-hor"> Nem jártam ebben az iskolában, sok kedves ismerösöm és barátommal szeretnék kapcsolatba maradni.</div></div>
			<div style="clear:both;"></div>
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(false,4);"/> 
				<div class="inline margin-hor">Ennek az iskolának tanárnöje, tanárja voltam vagy vagyok </div></div>
			<div style="clear:both;"></div>
			<div id="idclass" style="margin:10px;display:none">
				<div class="input-group" id="grpyear"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">Ballagási év</span>
					<select id="year" name="year" size="1" class="form-control" onchange="showPersons()">
						<option value="0">...válassz!...</option>
						<?php for ($i=2010;$i>1965;$i--) {
							if (getParam("year", "")==$i) $selected="selected"; else $selected="";
							echo('<option '.$selected.' value="'.$i.'">'.$i.'</option>');
						} ?>
					</select>
				</div>
				<div class="input-group" id="grpclass"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">Ballagási osztály</span>
					<select id="class" name="class" size="1" class="form-control" onchange="showPersons()">
						<option value="0">...válassz!...</option>
						<?php for($cl=11;$cl<14;$cl++) { ?>
							<?php for($cs="A";$cs<"G";$cs++) { ?>
								<option value="<?php echo $cl.$cs ?>"><?php echo $cl.$cs ?></option>
						<?php } } ?>
					</select>
				</div>
			</div>
		</div>
		<div id="page2" style="margin:10px;display:none">
			<h4 class="margin-hor">Válaszd ki neved a meglevő névsórból:</h4>
			<div class="input-group" id="grpclass"> 
				<span style="min-width:150px; text-align:right" class="input-group-addon">Személyek:</span>
				<select id="personlist" name="class" size="1" class="form-control" onchange="showPerson()">
					<option value="-2">...válassz!...</option>
					<option value="-1">...nem találom nevem a listán...</option>
				</select>
			</div>
		</div> 
		<div id="page3" style="margin:10px;display:none">
			<h4 class="margin-hor">Személyes adataim:</h4> 
			<div class="input-group" > 
				<span style="min-width:150px; text-align:right" class="input-group-addon">Családnevem</span>
				<input type="text" class="form-control" id="lastname"/>
			</div>
			<div class="input-group" > 
				<span style="min-width:150px; text-align:right" class="input-group-addon">Keresztnevem</span>
				<input type="text" class="form-control" id="firstname" />
			</div>
			<div class="input-group" > 
				<span style="min-width:150px; text-align:right" class="input-group-addon">E-Mail címem</span>
				<input type="text" class="form-control" id="email" onchange="validateEmailInput(this);" onkeyup="validateEmailInput(this);"/>
			</div>
		</div>		
		<button class="margin-def btn btn-default disabled"  onclick="signin()" id="signin">Bejelentkezem</button>
		<?php } else {?>
			<button class="btn btn-default" onclick="javascript:document.location.href='editDiak.php?uid=<?php echo $ret?>'">Mutasd személyes adataim</button> Bejelentkezés sikerült
		<?php } ?>
	</div>
</div></div>

<script>
	var role=0;
	var personList;

	function showPersons() {
	    $("#page3").hide("slow");
		if ((role==1 || role==2) && ($("#year").val()=="0" || $("#class").val()=="0")) {
		    $("#page2").hide("slow");
		    return;
		}
		var url="getPersonList.php?class=";
		if (role==4 || role==3)
			url+="0 staf";
		else
			url+=$("#year").val()+" "+$("#class").val();
		if (role==2 || role==3) {
			url+="&guest=true"
		}
		<?php if(isset($_SESSION["FacebookId"])) {?>
			url+="&fid=<?php echo $_SESSION["FacebookId"]?>"
		<?php } ?> 
		$.ajax({
			  url: url
			}).success(function(data) {
				personList=data;
				$("#page2").show("slow");
				$("#personlist").find("option:gt(1)").remove();
				data.forEach(function(d,i) {
					$("#personlist").append($("<option />").val(i).text(d.lastname+" "+d.firstname));
				});
			});
	}

	function showPerson() {
		var id=parseInt($("#personlist").val());
		if (id<-1) {
		    $("#page3").hide("slow");
		    $("#signin").addClass("disabled");
		    return;
		}
		<?php if (isset($_SESSION["FacebookId"])) {?>
			if(id!=-1) {
			    $("#lastname").val(personList[id].lastname);
			    $("#firstname").val(personList[id].firstname);
				$("#email").val("<?php echo getFieldValueNull($_SESSION, "FacebookEmail")?>");
			} else {
				$("#lastname").val("<?php echo getFieldValueNull($_SESSION, "FacebookLastName")?>");
				$("#firstname").val("<?php echo getFieldValueNull($_SESSION, "FacebookFirstName")?>");
				$("#email").val("<?php echo getFieldValueNull($_SESSION, "FacebookEmail")?>");
			}
		<?php } else {?>
			if(id!=-1) {
			    $("#lastname").val(personList[id].lastname);
			    $("#firstname").val(personList[id].firstname);
			    $("#email").val(personList[id].email);
			} else  {
			    $("#lastname").val("");
			    $("#firstname").val("");
			    $("#email").val("");
			}
		<?php } ?>
		$("#page3").show("slow");
		$("#signin").removeClass("disabled");
	}

	function setRole(visibleClass,r) {
		role=r;
		if (visibleClass) {
			$("#idclass").show("slow");
		} else {
		    $("#idclass").hide("slow");
		}
		showPersons();
	}

	function signin() {
		var msg="";
		if ($("#lastname").val().length<3) {
			msg+="A családnév nincs kitöltve, vagy túl rövid.\n";
		}
		if ($("#firstname").val().length<3) {
			msg+="A keresztnév nincs kitöltve, vagy túl rövid.\n";
		}
		if( !validateEmail($("#email").val())) {
		    msg+="Email cím nem helyes.\n";
		}
		if (msg!="") {
			msg="Bejelentkezés nem lehetséges mert:\n\n"+msg;
			alert(msg);
		} else {
			var url="start.php?action=newUser";
			if (role==1 || role==4) url+="&role=";
			if (role==2 || role==3) url+="&role=guest";
			if (role==3 || role==4) url+="&classtext=0 staf";	
			if (role==1 || role==2) url+="&classtext="+$("#year").val()+" "+$("#class").val();
			url+="&firstname="+$("#firstname").val();
			url+="&lastname="+$("#lastname").val();
			url+="&email="+$("#email").val();
			var id=parseInt($("#personlist").val());
			if(id>=0) url+="&id="+personList[id].id;
			<?php if(isset($_SESSION["FacebookId"])) {?>
				url+="&fid=<?php echo $_SESSION["FacebookId"]?>"
			<?php } ?> 
			document.location.href=url;
		}
	}

	function validateEmailInput(sender) { 
	    	if (validateEmail(sender.value)) {
	    		sender.style.color="green";
	    	} else {
	    		sender.style.color="red";
	    	}
	} 

	function validateEmail(mail) {
	   	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	   	return re.test(mail);
	}
	
</script>
