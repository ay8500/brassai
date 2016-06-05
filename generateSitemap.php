<?PHP 
include_once("sessionManager.php");
Header ("Content-type: text/txt");

function writeSitemap($id,$db,$link) 
{
	if (isset($_GET["htacces"])) {
		echo("Redirect 301 /".$link." /".$link."-".$db."-".$id."\r\n");
	} else {
		echo("<url>"."\r\n");
		echo("\t<loc>http://brassai.blue-l.de/".$link."-".$db."-".$id."</loc>"."\r\n");
		echo("\t<priority>0.5</priority>"."\r\n");
		echo("\t<lastmod>".date('Y-m-d')."</lastmod>"."\r\n");
		echo("\t<changefreq>monthly</changefreq>"."\r\n");
		echo("</url>"."\r\n");
	}
}

include_once("data.php");

	foreach ($dataBase as $db) {

		openDatabase($db);
		
		foreach ($data as $d) {
			writeSitemap($d["id"],$db,getPersonLink($d["lastname"],$d["firstname"]));
		}
		
	}


?>