<?php
include_once 'tools/sessionManager.php';
include_once("tools/userManager.php");//login logoff
include_once 'tools/ltools.php';
include_once 'tools/appl.class.php';
include_once("dbBL.class.php");		//the database
include_once("sendMail.php");	//send mail

use \maierlabs\lpfw\Appl as Appl;

//this is the facebook callback page
if (getParam("FacebookId")) {
	$_SESSION['FacebookId']=getParam("FacebookId");
	$_SESSION["FacebookName"]=getParam("last_name").' '.getParam("first_name");
	$_SESSION["FacebookFirstName"]=getParam("first_name");
	$_SESSION["FacebookLastName"]=getParam("last_name");
	$_SESSION["FacebookEmail"]=getParam("email");
	$_SESSION["FacebookLink"]="https://www.facebook.com/";
} else {
	unset($_SESSION['FacebookId']);
	unset($_SESSION["FacebookName"]);
	unset($_SESSION["FacebookFirstName"]);
	unset($_SESSION["FacebookLastName"]);
	unset($_SESSION["FacebookEmail"]);
}

include_once 'logon.php';
if (userIsLoggedOn()) {
	header('Location: index.php?loginok=true');
}
//New User
if (!userIsLoggedOn() && isActionParam("newUser") && getParam("classtext", "")!="") {
	if ($db->getCountOfRequest(changeType::newuser)>5) {
		logoutUser();
		Appl::$resultDbOperation='<div class="alert alert-warning" >Túl sok bejelenkezést szeretnél létrehozni, kérünk probálkozz késöbb még egyszer!</div>';
	} else {
		if (checkUserEmailExists(getParam('id'),html_entity_decode(getParam("email"),ENT_QUOTES,"UTF-8"))) {
			logoutUser();
			Appl::$resultDbOperation='<div class="alert alert-warning" >A megadott email cím már létezik, kérünk probálkozz még egyszer egy másik email címmel!</div>';
		} else {
			setUserInSession("", "dummy", 0); //simulate user is loged on to not create duplicate users
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
				logoutUser();
				Appl::$resultDbOperation='<div class="alert alert-warning" >Bejelenkezést nem sikerült, kérünk probálkozz késöbb még egyszer!<br/>Hibacód:65132</div>';
			} else {
				if (getIntParam("id", -1)==-1) {
					//create a new person
					$person=$db->getPersonDummy();
					$person["id"]=-1;
					$person["lastname"]=html_entity_decode(getParam("lastname"),ENT_QUOTES,"UTF-8");
					$person["firstname"]=html_entity_decode(getParam("firstname"),ENT_QUOTES,"UTF-8");
					$person["email"]=html_entity_decode(getParam("email"),ENT_QUOTES,"UTF-8");
					$person["role"]=getParam("role","");
                    $person["facebookid"]=getParam("fid");
					$person["classID"]=$classid;
					$newUserReturnValue = $db->savePerson($person);
				} else {
					//update a person
					$person=$db->getPersonByID(getIntParam("id"));
					if ($person!=null)
						$person=$db->getPersonByID(getRealId($person));
					if ($person==null) {
						$newUserReturnValue=-12;
					} else {
						$person["lastname"]=html_entity_decode(getParam("lastname"),ENT_QUOTES,"UTF-8");
						$person["firstname"]=html_entity_decode(getParam("firstname"),ENT_QUOTES,"UTF-8");
						$person["email"]=html_entity_decode(getParam("email"),ENT_QUOTES,"UTF-8");
						$person["facebookid"]=getParam("fid");
						$newUserReturnValue = $db->savePerson($person);
					}
				}
				if ($newUserReturnValue>=0) {
					$person["id"]=$newUserReturnValue;
					$db->saveRequest(changeType::newuser);
					Appl::setMessage('Köszünjük szépen!<br/>Bejelenkezési adatok sikeresen kimentve. Hamarosam e-mailtben visszajelezzük a bejelenkezési adatokat.<br/>Jó szorakozást és sikeres kapcsolatfelvételt kivánunk a véndiákok oldalán.', 'info');
					setUserInSession($person["role"],$person["user"],$newUserReturnValue);
					sendNewUserMail($person["firstname"], $person["lastname"], $person["email"], $person["passw"],$person["user"], "", $class["graduationYear"], $class["name"],$person["id"]);
				} else {
					logoutUser();
					Appl::setMessage('Bejelenkezést nem sikerült, kérünk probálkozz késöbb még egyszer!','warning');
				}
			}
		}
	}
}
Appl::setSiteSubTitle('Bejelentkezés');
Appl::addCssStyle('
	.fb-radio{width: 25px;height: 25px;position: relative;top: -6px;}
');
include 'homemenu.inc.php';
?>

<div class="container-fluid">
<div class="well">
	<div class="panel panel-default">
		<?php if (!isset($newUserReturnValue)) {
		    Appl::addJsScript("
		        $( document ).ready(function() {
		            $('.navbar-nav').css('opacity','0.2');
                    $('.navbar-nav').css('pointer-events','none');
                });  
    		");
		    ?>
		<div class="panel-heading">
			<label id="dbDetails">
				<?php if (isset($_SESSION["FacebookId"])) {?> 
					<div class="left margin-hor"><img src="https://graph.facebook.com/<?php echo $_SESSION['FacebookId']; ?>/picture" /></div>
					<div class="inline">Kedves <?php echo $_SESSION["FacebookName"]?> szeretettel köszöntünk a véndiákok honoldalán.</div>
				<?php  } else {?>
				<div class="inline">Kedves látogató szeretettel köszöntünk a véndiákok honoldalán.</div>
				<?php  } ?>
                <div>Légyszíves és állítsd be milyen viszonyban állsz a véndiákokkal.</div>
			</label> 
		</div>
		<div id="page1">
			<h4 class="margin-hor">Kapcsolatom a <?php  echo getAktSchoolName() ?> véndiákjaival:</h4>
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(true,1);"/> 
				<div class="inline margin-hor"> Véndiák vagyok, ebben az iskolában éretségiztem és ballagtam.</div></div>
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
				<div class="inline margin-hor">Ennek az iskolának tanárnője, tanárja voltam vagy vagyok </div></div>
			<div style="clear:both;"></div>
			<div id="idclass" style="margin:10px;display:none">
				<div class="input-group" id="grpyear"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">Ballagási év</span>
					<select id="year" name="year" size="1" class="form-control" onchange="showPersons()">
						<option value="0">...válassz!...</option>
						<?php for ($i=date("Y");$i>date("Y")-80;$i--) {
							if (getParam("year", "")==$i) $selected="selected"; else $selected="";
							echo('<option '.$selected.' value="'.$i.'">'.$i.'</option>');
						} ?>
					</select>
				</div>
				<div class="input-group" id="grpclass"> 
  					<span style="min-width:150px; text-align:right" class="input-group-addon">Ballagási osztály</span>
					<select id="class" name="class" size="1" class="form-control" onchange="showPersons()">
						<option value="0">...válassz!...</option>
						<?php for($cl=10;$cl<14;$cl++) { ?>
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
		<button class="margin-def btn btn-success disabled"  onclick="signin()" id="signin">Bejelentkezem</button>
        <button class="margin-def btn btn-warning"  onclick="document.location.href='index.php'" >Kilép</button>
		<?php } else {?>
			<button class="btn btn-default" onclick="javascript:document.location.href='editDiak.php?uid=<?php echo $newUserReturnValue?>'">Mutasd személyes adataim</button> Bejelentkezés sikerült
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
		var url="ajax/getPersonsInClass.php";
		if (role==4 || role==3)
			url+="?classid="+<?php echo $db->getStafClassIdBySchoolId(getAktSchoolId())?>;
		else
			url+="?class="+$("#year").val()+" "+$("#class").val();
		if (role==2 || role==3) {
			url+="&guest=true"
		}
		<?php if(isset($_SESSION["FacebookId"])) {?>
			url+="&fid=<?php echo $_SESSION["FacebookId"]?>"
		<?php } ?> 
		$.ajax({
			  url: url,
			  success:function(data) {
				personList=data;
				$("#page2").show("slow");
				$("#personlist").find("option:gt(1)").remove();
				data.forEach(function(d,i) {
					$("#personlist").append($("<option />").val(i).text(d.lastname+" "+d.firstname));
				}
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
			var url="signin.php?action=newUser";
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
<?php include "homefooter.inc.php"; die();?>
