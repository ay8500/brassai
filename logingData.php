<?php
    include_once 'lpfw/sessionManager.php';
    include_once 'lpfw/userManager.php';
    include_once 'lpfw/logger.class.php';
	include('homemenu.inc.php');
?>
<div class="sub_title">Loging</div>
<?PHP
if (userIsAdmin() || userIsSuperuser()) {

	//initialise tabs
	$tabsCaption=Array("","Sikertelen&nbsp;bejelentkezés","Adatmódosítás","Bejelentkezési adatok","Facebook");
    //initialise tabs
    $tabsCaption = array();
    array_push($tabsCaption ,array("id" => "ok", "caption" => 'Bejelentkezés', "glyphicon" => "user"));
    array_push($tabsCaption ,array("id" => "change", "caption" => 'Adatmódosítás', "glyphicon" => ""));
    array_push($tabsCaption ,array("id" => "userdata", "caption" => 'Bejelentkezési adatok', "glyphicon" => "cloud"));
    array_push($tabsCaption ,array("id" => "data", "caption" => 'Képek', "glyphicon" => "picture"));
    array_push($tabsCaption ,array("id" => "facebook", "caption" => 'Facebook', "glyphicon" => "tower"));
    array_push($tabsCaption ,array("id" => "database", "caption" => 'Adatbázis', "glyphicon" => "cd"));

    include 'lpfw/view/tabs.inc.php';
	?>
	
	<?php
        $year=date("Y");
        $length=getIntParam("count",500);
        if (userIsSuperuser())
            $length = 25;
        if ($tabOpen=='ok') {
            $textTitle="Bejelentkezések";
	  		$logData= maierlabs\lpfw\Logger::readLogData("Login,LoginDirect",$year,$length);
        }
        if ($tabOpen=='change') {
            $textTitle="Adatmodosítások";
            $logData= maierlabs\lpfw\Logger::readLogData("SaveData,SaveGeo,SaveStory,UserPicture",$year,$length);
        }
        if ($tabOpen=='userdata') {
            $textTitle="Bejelentkezési adatok";
            $logData= maierlabs\lpfw\Logger::readLogData("SavePassw,SaveUsername,NewPassword",$year,$length);
        }
        if ($tabOpen=='data') {
            $textTitle="Adatok";
            $logData= maierlabs\lpfw\Logger::readLogData("PictureUpload,PictureDelete",$year,$length);
        }
        if ($tabOpen=='facebook') {
            $textTitle="Facebook";
            $logData= maierlabs\lpfw\Logger::readLogData("Facebook",$year,$length);
        }
        if ($tabOpen=='database') {
            $textTitle="Adatbázis";
            $logData= maierlabs\lpfw\Logger::readLogData("Database",$year,$length);
        }
    ?>
	<p align="center">
	    <?php echo $textTitle ?><br/>
        <table align="center" border="1">
            <tr><td>Date</td><td>Level</td><td>IP</td><td>URI</td><td>UserID</td><td>Text</td></tr>
            <?php
            foreach($logData as $slog) {
                if ($slog['Level']=="info")
                    echo('<tr style="background-color:#edffea">');
                if ($slog['Level']=="error")
                    echo('<tr style="background-color:#ffedea">');
                echo("<td>".$slog['Date']."</td><td>".$slog['Level']."</td><td>".$slog['IP']."</td><td>".str_replace("&"," ",$slog['URI'])."</td><td>".$slog['User']."</td><td>".$slog['Text']."</td>");
                echo("</tr>");
            }
            ?>
        </table>
    </p>
	<p>
        <a href="generateSitemap.php">Sitemap</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="generateSitemap.php?htacces=true">htacces</a>
	</p>

<?php }
else
	echo '<div class="alert alert-danger" >Adat hozzáférési jog hiányzik!</div>';
	
include 'homefooter.inc.php';
?>


