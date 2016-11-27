<?PHP 
include_once 'tools/sessionManager.php';
Header ("Content-type: text/txt");


function writeSitemapPerson($id,$link) {
	if (isset($_GET["htacces"])) {
		echo("Redirect 301 /".$link." /".$link."-".$db."-".$id."\r\n");
	} else {
		writeSitemapLink($link."-".$id);
	}
}

function writeSitemapLink($link) 
{
	echo("<url>"."\r\n");
	echo("\t<loc>http://brassai.blue-l.de/".$link."</loc>"."\r\n");
	echo("\t<priority>0.5</priority>"."\r\n");
	echo("\t<lastmod>".date('Y-m-d')."</lastmod>"."\r\n");
	echo("\t<changefreq>monthly</changefreq>"."\r\n");
	echo("</url>"."\r\n");
}

include_once("dbDAO.class.php");
include_once("data.php");
	
	
	$classList=$db->getClassList();
	foreach ($classList as $class) {
		writeSitemapLink("hometable.php?classid=".$class["id"]);
		writeSitemapLink("worldmap.php?classid=".$class["id"]);
		writeSitemapLink("vote.php?classid=".$class["id"]);
		writeSitemapLink("picture.php?classid=".$class["id"]);
	}
		
	$db->queryPersons();
	while ($d=$db->getQueryRow()) {
		writeSitemapPerson($d["id"],getPersonLink($d["lastname"],$d["firstname"]));
	}
	

?>