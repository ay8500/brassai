<?PHP 
include_once("sessionManager.php");
Header ("Content-type: text/txt");

function writeSitemap($id,$link) 
{
	if (isset($_GET["htacces"])) {
		echo("RewriteRule ".$link." editDiak.php?uid=".$id."&scoolYear=".$_SESSION['scoolYear']."&scoolClass=".$_SESSION['scoolClass']."\r\n");
	} else {
		echo("<url>"."\r\n");
		echo("\t<loc>http://brassai.blue-l.de/".$link."</loc>"."\r\n");
		echo("\t<priority>0.5</priority>"."\r\n");
		echo("\t<lastmod>".date('Y.m.d')."</lastmod>"."\r\n");
		echo("\t<changefreq>monthly</changefreq>"."\r\n");
		echo("</url>"."\r\n");
	}
}

include_once("data.php");

		foreach ($data as $d) 
		{
			writeSitemap($d["id"],getPersonLink($d["lastname"],$d["firstname"]));
			if ((trim($d["birthname"])!="") && (trim($d["birthname"])!=trim($d["lastname"]))) 
			  writeSitemap($id,getPersonLink($d["birthname"],$d["firstname"])); 
		}


?>