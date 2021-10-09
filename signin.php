<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
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
} else {
	unset($_SESSION['FacebookId']);
	unset($_SESSION["FacebookName"]);
	unset($_SESSION["FacebookFirstName"]);
	unset($_SESSION["FacebookLastName"]);
	unset($_SESSION["FacebookEmail"]);
}
global $db;
global $userDB;

$actClass = $db->handleClassSchoolChange(getParam("classid"),getParam("schoolid"));
include_once Config::$lpfw.'logon.inc.php';
handleLogInOff($userDB);
if (isUserLoggedOn()) {
	header('Location: index?loginok=true');
}
$schoolList = $db->getSchoolList();

//New User
if (!isUserLoggedOn() && isActionParam("newUser") && getParam("classtext", "")!="" && getActSchool()!=null) {
	if ($db->getCountOfRequest(changeType::newuser)>5) {
		logoutUser();
		Appl::$resultDbOperation='<div class="alert alert-warning" >Túl sok bejelenkezést szeretnél létrehozni, kérünk probálkozz késöbb még egyszer!</div>';
	} else {
		if (checkUserEmailExists($userDB,getParam('id'),html_entity_decode(getParam("email"),ENT_QUOTES,"UTF-8"))) {
			logoutUser();
			Appl::$resultDbOperation='<div class="alert alert-warning" >A megadott email cím már létezik, kérünk probálkozz még egyszer egy másik email címmel!</div>';
		} else {
            if (getActSchool() != null) {
                setUserInSession($userDB, "", "dummy", 0); //simulate user is loged on to not create duplicate users
                $classtext = getParam("classtext", "");
                $class = $db->getClassByText($classtext);
                if ($class == null) {
                    //create a new class
                    $classid = $db->saveClass(array(
                        "id" => -1,
                        "schoolID" => getActSchoolId(),
                        "name" => substr($classtext, 5, 3),
                        "graduationYear" => substr($classtext, 0, 4),
                        "text" => $classtext));
                } else {
                    $classid = getRealId($class);
                }
                if ($classid < 0) {
                    logoutUser();
                    Appl::$resultDbOperation = '<div class="alert alert-warning" >Bejelenkezést nem sikerült, kérünk probálkozz késöbb még egyszer!<br/>Hibacód:65132</div>';
                } else {
                    if (getIntParam("id", -1) == -1) {
                        //create a new person
                        $person = $db->getPersonDummy();
                        $person["id"] = -1;
                        $person["lastname"] = html_entity_decode(getParam("lastname"), ENT_QUOTES, "UTF-8");
                        $person["firstname"] = html_entity_decode(getParam("firstname"), ENT_QUOTES, "UTF-8");
                        $person["email"] = html_entity_decode(getParam("email"), ENT_QUOTES, "UTF-8");
                        $person["title"] = html_entity_decode(getParam("title"), ENT_QUOTES, "UTF-8");
                        $person["gender"] = html_entity_decode(getParam("gender"), ENT_QUOTES, "UTF-8");
                        $person["role"] = getParam("role", "");
                        $person["facebookid"] = getParam("fid");
                        $person["classID"] = $classid;
                        $person["gender"] = getGender($person["firstname"]);
                        $newUserReturnValue = $db->savePerson($person);
                    } else {
                        //update a person
                        $person = $db->getPersonByID(getIntParam("id"));
                        if ($person != null)
                            $person = $db->getPersonByID(getRealId($person));
                        if ($person == null) {
                            $newUserReturnValue = -12;
                        } else {
                            $person["lastname"] = html_entity_decode(getParam("lastname"), ENT_QUOTES, "UTF-8");
                            $person["firstname"] = html_entity_decode(getParam("firstname"), ENT_QUOTES, "UTF-8");
                            $person["email"] = html_entity_decode(getParam("email"), ENT_QUOTES, "UTF-8");
                            $person["gender"] = html_entity_decode(getParam("gender"), ENT_QUOTES, "UTF-8");
                            $person["title"] = html_entity_decode(getParam("title"), ENT_QUOTES, "UTF-8");
                            $person["facebookid"] = getParam("fid");
                            $newUserReturnValue = $db->savePerson($person);
                        }
                    }
                    if ($newUserReturnValue >= 0) {
                        $person["id"] = $newUserReturnValue;
                        $db->saveRequest(changeType::newuser);
                        Appl::setMessage('Köszünjük szépen!<br/>Bejelenkezési adatok sikeresen kimentve. Hamarosam e-mailtben visszajelezzük a bejelenkezési adatokat.<br/>Jó szorakozást és sikeres kapcsolatfelvételt kivánunk a véndiákok oldalán.', 'info');
                        setUserInSession($userDB, $person["role"], $person["user"], $newUserReturnValue);
                        sendNewUserMail($person["firstname"], $person["lastname"], $person["email"], $person["passw"], $person["user"], "", $class["graduationYear"], $class["name"], $person["id"]);
                    } else {
                        logoutUser();
                        Appl::setMessage('Bejelenkezést nem sikerült, kérünk probálkozz késöbb még egyszer! Hibakód:'.$newUserReturnValue, 'warning');
                    }
                }
            } else {
                logoutUser();
                Appl::$resultDbOperation = '<div class="alert alert-warning" >Bejelenkezést nem sikerült, kérünk probálkozz késöbb még egyszer!<br/>Hibacód:39132</div>';
            }
        }
	}
}
Appl::setSiteTitle(getActSchoolName().' Bejelentkezés','Bejelentkezés regisztrálás');
Appl::addCssStyle('
	.fb-radio{width: 25px;height: 25px;position: relative;top: -6px;}
');
include 'homemenu.inc.php';
?>

<div class="container-fluid">
<div class="well">
	<div class="panel panel-default">
		<?php if (!isset($newUserReturnValue)) { ?>
		<div class="panel-heading">
			<label id="dbDetails">
				<?php if (isset($_SESSION["FacebookId"])) {?> 
					<div class="left margin-hor"><img src="https://graph.facebook.com/<?php echo $_SESSION['FacebookId']; ?>/picture" /></div>
					<div class="inline">Kedves <?php echo $_SESSION["FacebookName"]?> szeretettel köszöntünk a véndiákok honoldalán.</div>
				<?php  } else {?>
				<h3 class="inline">Kedves látogató szeretettel köszöntünk a véndiákok honoldalán.</h3>
				<?php  } ?>
                <div><hr/></div>
                <div>Motiváció:</div>
                <div>Ez az oldal ingyenes, nem tartalmaz reklámot és ami a legfontosabb, látogatásod és aktivitásaid biztonságban maradnak! Adataid, képeid és bejegyzésed csak arra a célra vannak tárólva, hogy a véndiákok oldalát gazdagítsák! Ezenkivül csak te határozod meg ki láthatja őket. Kellemes időtöltést és szorakozást kivánunk.</div>
			</label>
		</div>
		<div id="page1">
            <div class="input-group shadowbox" >
                <span style="min-width:110px; text-align:right" class="input-group-addon" id="basic-addon1">Iskola</span>
                <select class="form-control" id="selectSchool" onchange="changeSchool()">
                    <option>válassz iskolát</option>
                    <?php foreach ($schoolList as $school) {
                        $selected = $school["id"]==getActSchoolId()?"selected=selected":""?>
                        <option value="<?php echo $school["id"] ?>" <?php echo $selected ?>><?php echo $school["name"] ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php if (getActSchoolId()!=null) { ?>
			<h4 class="margin-hor">Kapcsolatom a kiválasztott iskola véndiákjaival:</h4>
            <div class="margin-def">Légyszíves állítsd be milyen viszonyban állsz a véndiákokkal.</div>
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(true,1);"/> 
				<div class="inline margin-hor"> Véndiákja vagyok, a kiválasztott iskolának, itt éretségiztem és ballagtam.</div></div>
			<div style="clear:both;"></div>
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(true,2);"/> 
				<div class="inline margin-hor"> Nem éretségiztem a kiválasztott iskolában, de egy ideig oda jártam. Válszd ki azt az osztályt ahol végzös diák lettél volna.</div></div>
			<div style="clear:both;"></div>
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(false,3);"/>
				<div class="inline margin-hor"> Nem voltam a felsorolt iskolák diákja, sok kedves ismerösöm és barátommal szeretnék kapcsolatba maradni.</div></div>
			<div style="clear:both;"></div>
			<div class="margin-def">
				<input class="left fb-radio" type="radio" name="role" onclick="setRole(false,4);"/> 
				<div class="inline margin-hor">A kiválasztott iskolának tanárnője, tanárja voltam vagy vagyok </div></div>
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
                        <?php for($cs="A";$cs<"G";$cs++) { ?>
								<option value="<?php echo "12".$cs ?>"><?php echo "12".$cs ?></option>
						<?php } ?>
                        <?php for($cs="A";$cs<"G";$cs++) { ?>
                            <option value="<?php echo "13".$cs ?>"><?php echo "13".$cs ?></option>
                        <?php } ?>
                        <?php for($cs="A";$cs<"G";$cs++) { ?>
                            <option value="<?php echo "11".$cs ?>"><?php echo "11".$cs ?></option>
                        <?php } ?>
                        <?php for($cs="A";$cs<"G";$cs++) { ?>
                            <option value="<?php echo "10".$cs ?>"><?php echo "10".$cs ?></option>
                        <?php } ?>
					</select>
				</div>
			</div>
            <?php } ?>
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
                <span style="min-width:150px; text-align:right" class="input-group-addon">Megszólítás</span>
                <select class="form-control"  id="gender">
                    <option value="">válassz</option>
                    <option value="f">hölgy</option>
                    <option value="m">úr</option>
                </select>
            </div>
            <div class="input-group" >
                <span style="min-width:150px; text-align:right" class="input-group-addon">Titulus</span>
                <select class="form-control"  id="title">
                    <option value="" ></option>
                    <option value="Dr.">Dr.</option>
                    <option value="Dr.Med.">Dr.Med.</option>
                    <option value="Prof.">Prof.</option>
                    <option value="Dr.Prof.">Dr.Prof.</option>
                    <option value="Dr.Dr.">Dr.Dr.</option>
                </select>
            </div>
			<div class="input-group" >
				<span style="min-width:150px; text-align:right" class="input-group-addon">Családnevem</span>
				<input type="text" class="form-control" id="lastname" onchange="checkFormPerson();"  onkeyup="checkFormPerson();"/>
			</div>
			<div class="input-group" > 
				<span style="min-width:150px; text-align:right" class="input-group-addon">Keresztnevem</span>
				<input type="text" class="form-control" id="firstname"  onchange="checkFormPerson();"  onkeyup="checkFormPerson();" />
			</div>
			<div class="input-group" > 
				<span style="min-width:150px; text-align:right" class="input-group-addon">E-Mail címem</span>
				<input type="text" class="form-control" id="email" onchange="checkFormPerson();" onkeyup="checkFormPerson();"/>
			</div>
		</div>
        <div>
            <div class="input-group" id="gdprgrp" style="border: 1px lightgray solid">
                <input  style="width:50px; text-align:left" type="checkbox" class="form-control" id="gdpr" onchange="checkFormPerson();" />
                <span class="input-group">Europai adatvédelmi szabályzat értelmében beleegyezek, hogy személyes adataim csak az a célt szolgálják, hogy ezt az oldalt gazdagítsák. Ezeknek törlését bármikor feltétel nélkül kérvényezni lehet.</span>
            </div>

        </div>
		<button class="margin-def btn btn-success disabled"  onclick="signin()" id="signinbtn">Bejelentkezem</button>
        <button class="margin-def btn btn-warning"  onclick="document.location.href='index'" >Kilép</button>
		<?php } else {?>
			<button class="btn btn-default" onclick="javascript:document.location.href='editDiak?uid=<?php echo $newUserReturnValue?>'">Mutasd személyes adataim</button> Bejelentkezés sikerült
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
		var url="ajax/getPersonsInClass";
		if (role==4 || role==3)
			url+="?classid="+<?php echo $db->getStafClassIdBySchoolId(getActSchoolId())!=null?$db->getStafClassIdBySchoolId(getActSchoolId()):0 ?>;
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
                        $("#personlist").append($("<option />").val(i).text((d.title!=null?(d.title+ " "):"")+d.lastname+" "+d.firstname));
                    });
			  }
			});
	}

	function showPerson() {
		var id=parseInt($("#personlist").val());
		if (id<-1) {
		    $("#page3").hide("slow");
		    $("#signinbtn").addClass("disabled");
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
                $("#gender").val(personList[id].gender);
                $("#title").val(personList[id].title);
			} else  {
			    $("#lastname").val("");
			    $("#firstname").val("");
			    $("#email").val("");
			}
		<?php } ?>
        checkFormPerson();
		$("#page3").show("slow");
	}

	function setRole(visibleClass,r) {
		role=r;
		if (visibleClass) {
			$("#idclass").show("slow");
		} else {
		    $("#idclass").hide("slow");
		}
		if (r==1 || r==2 || r==4) {
            showPersons();
        } else {
            $("#page2").hide("slow");
            $("#page3").show("slow");
        }
	}

    function changeSchool() {
        document.location.href="signin?action=newUser&schoolid="+$("#selectSchool option:selected" ).val();
    }

    function checkFormPerson() {
        var ok = true;
        if (validateEmail($("#email").val())) {
            $("#email").css("border-color","green");
        } else {
            $("#email").css("border-color","red");
            ok=false;
        }
        if ($("#lastname").val().length<3) {
            $("#lastname").css("border-color","red");
            ok=false;
        } else {
            $("#lastname").css("border-color","green");
        }
        if ($("#firstname").val().length<3) {
            $("#firstname").css("border-color","red");
            ok=false;
        } else {
            $("#firstname").css("border-color","green");
        }
        if ($('#gdpr').is(":checked")) {
            $("#gdprgrp").css("border-color","green");
        } else {
            $("#gdprgrp").css("border-color","red");
            ok=false;
        }
        if (ok)
            $("#signinbtn").removeClass("disabled");
        else
            $("#signinbtn").addClass("disabled");
    }

	function signin() {
        var url="signin?action=newUser";
        if (role==1 || role==4) url+="&role=";
        if (role==2 || role==3) url+="&role=guest";
        if (role==3 || role==4) url+="&classtext=0 staf";
        if (role==1 || role==2) url+="&classtext="+$("#year").val()+" "+$("#class").val();
        url+="&firstname="+$("#firstname").val();
        url+="&lastname="+$("#lastname").val();
        url+="&email="+$("#email").val();
        url+="&gender="+$("#gender").val();
        url+="&title="+$("#title").val();
        var id=parseInt($("#personlist").val());
        if(id>=0) url+="&id="+personList[id].id;
        <?php if(isset($_SESSION["FacebookId"])) {?>
            url+="&fid=<?php echo $_SESSION["FacebookId"]?>"
        <?php } ?>
        document.location.href=url;
	}


	function validateEmail(mail) {
	   	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	   	return re.test(mail);
	}
</script>
<?php
Appl::addJsScript('checkFormPerson();',true);
include "homefooter.inc.php";
?>

