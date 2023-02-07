<?php
include_once Config::$lpfw.'view/formTools.inc.php';

global $db;
global $personid;
global $tabOpen;
$diak = $db->getPersonByID($personid);
?>
<?php if ( isUserAdmin() || isAktUserTheLoggedInUser()) { ?>
    <div class="user-setting">
        <div><h3><span class="glyphicon glyphicon-log-in"></span> Automatikus bejelentkezés</h3>Az oldal automatikusan bejelentkezik mint <?php echo $diak["lastname"]." ".$diak["firstname"]?>. </div>
        <div>
            <?php writeCheckBox(null,"autoLogOn","",false,"nem|igen","log-in"); ?>
        </div>
    </div>

    <div class="user-setting">
        <form action="editPerson" method="get">
			<div><h3><span class="glyphicon glyphicon-user"></span> Becenév módosítása</h3>A becenév minimum 6 karakter hosszú kell legyen. </div>
			<div><span>Becenév</span><input type="text" class="input2" name="user" value="<?php  echo $diak["user"] ?>" /></div>
			<div><span></span><input type="submit" class="btn btn-success" value="kimentés" title="Új becenév kimentése" /></div>
			<input type="hidden" value="changeuser" name="action" />
			<input type="hidden" value="<?php echo getActUserId(); ?>" name="uid" />
			<input type="hidden" value="<?php echo $tabOpen; ?>" name="tabOpen" />
		</form>
    </div>

    <div class="user-setting">
		<form action="editPerson" method="get">
			<div><p style="text-align:left"><h3><span class="glyphicon glyphicon-wrench"></span> Jelszó módosítása</h3> A jelszó minimum 6 karakter hosszú kell legyen. </p></div>
            <div><span>Jelszó</span><input type="password" xclass="form-control" name="newpwd1" value="" /></div>
            <div><span>Jelszó ismétlése</span><input type="password" xclass="form-control" name="newpwd2" value="" /></div>
			<div><span></span><input type="submit" class="btn btn-success" value="kimentés" title="Új jelszó kimentése" /></div>
			<input type="hidden" value="changepassw" name="action" />
			<input type="hidden" value="<?php echo getActUserId(); ?>" name="uid" />
			<input type="hidden" value="<?php echo $tabOpen; ?>" name="tabOpen" />
		</form>
    </div>

    <div class="user-setting">
        <div><p style="text-align:left" ><h3><span class="glyphicon glyphicon-link"></span> Direkt link az adataimhoz</h3>Ezzel a linkkel becenév és jelszó nélkül lehet bejelentkezni.</p></div>
        <div><span>Direkt link</span><a class="btn btn-default" href="editPerson?key=<?php echo generateUserLoginKey(getActUserId())?> "> <?php echo $diak["lastname"]." ".$diak["firstname"]?></a></div>
        <?php if (isUserAdmin()) {?>
            <div><span>Összes infó</span><a class="btn btn-default" href="personalData?allDataKey=<?php echo generateUserKeyThatExpires(getActUserId())?> "> <?php echo $diak["lastname"]." ".$diak["firstname"]?></a></div>
        <?php } ?>
        <div><p style="text-align:left" ><span class="glyphicon glyphicon-time"></span> Utolsó bejelentkezés: <?php echo $diak["userLastLogin"]?> </p></div>
    </div>

    <?php if (isset($diak["facebookid"]) && $diak["facebookid"]!='0' && (isUserAdmin() || (isset($_SESSION['FacebookId']) && $diak["facebookid"]==$_SESSION['FacebookId']))) : ?>
    <div class="user-setting">
        <form action="editPerson" method="get">
            <div><h3><span class="glyphicon glyphicon-scissors"></span> Facebook</h3>Jelenleg Facebook kapcsolat létezik közötted és "<?php echo isset($_SESSION["FacebookName"])?$_SESSION["FacebookName"]:"nem bejelentkezett" ?>" Facebook felhasználóval.</div>
            <div><span>Facebook kép</span><img src="https://graph.facebook.com/<?php echo $diak['facebookid']; ?>/picture" /></div>
            <div><span></span><input type="submit" class="btn btn-warning" value="Facebook kapcsolatot töröl" /></div>
            <input type="hidden" value="removefacebookconnection" name="action" />
            <input type="hidden" value="<?php echo getActUserId() ?>" name="uid" />
            <input type="hidden" value="<?php echo $tabOpen ?>" name="tabOpen" />
        </form>
	</div>
    <?php endif ?>
<?php } else {
	\maierlabs\lpfw\Appl::setMessage('Oldal hozzáférésí jog hiánzik!',"warning");
}

\maierlabs\lpfw\Appl::addCssStyle('
    .user-setting{
        display: inline-block;
        width: 430px; height: 210px;
        background-color: ivory;
        padding: 10px;
        margin: 10px;
        vertical-align: text-top;
        border-radius: 10px;
        -webkit-box-shadow: 7px 5px 17px 2px #59513D; 
        box-shadow: 7px 5px 17px 2px #59513D;
    }
    .user-setting>form>div>span, .user-setting>div>span {
        width: 120px;
        display: inline-block;
    }
    .user-setting>form>div>input {
        margin-top:5px;
    }
    .user-setting>div>a {
        margin-top:5px;
        margin-left:15px;
    }
    .form-group > label, .input-group-addon { display:none; }
    
');
