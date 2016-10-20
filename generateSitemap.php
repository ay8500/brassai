<?PHP 
include_once("sessionManager.php");
Header ("Content-type: text/txt");

function writeSitemap($id,$link) 
{
	if (isset($_GET["htacces"])) {
		echo("Redirect 301 /".$link." /".$link."-".$db."-".$id."\r\n");
	} else {
		echo("<url>"."\r\n");
		echo("\t<loc>http://brassai.blue-l.de/".$link."-".$id."</loc>"."\r\n");
		echo("\t<priority>0.5</priority>"."\r\n");
		echo("\t<lastmod>".date('Y-m-d')."</lastmod>"."\r\n");
		echo("\t<changefreq>monthly</changefreq>"."\r\n");
		echo("</url>"."\r\n");
	}
}

include_once("dbDAO.class.php");
include_once("data.php");
	
	$db->queryPersons();
	while ($d=$db->getQueryRow()) {
		writeSitemap($d["id"],getPersonLink($d["lastname"],$d["firstname"]));
	}
		


?>