<?php Header ("Content-type: text/txt");
echo('<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
');
include_once 'config.class.php';
include_once 'dbBL.class.php';
include_once 'dbDaSongVote.class.php';

$dbSongVote = new dbDaSongVote($db);




function writeSitemapPerson($id,$link,$date) {
	if (isset($_GET["htacces"])) {
		echo("Redirect 301 /".$link." /".$link."-".$id."\r\n");
	} else {
		writeSitemapLink($link."-".$id,$date);
	}
}

function writeSitemapLink($link,$date)
{
    if ($date==null) $date=date('Y-m-d');
	echo("<url>"."\r\n");
	echo("\t<loc>".Config::$siteUrl."/".$link."</loc>"."\r\n");
	echo("\t<priority>0.5</priority>"."\r\n");
	echo("\t<lastmod>".date_create($date)->format('Y-m-d')."</lastmod>"."\r\n"); //2005-01-01
	echo("\t<changefreq>monthly</changefreq>"."\r\n");
	echo("</url>"."\r\n");
}

include_once("dbDAO.class.php");
include_once("dbBL.class.php");

	$list=$dbSongVote->getSongList();
	foreach ($list as $element) {
		writeSitemapLink("zenePlayer?id=".$element["id"],$element["changeDate"]);
	}


	$pictureList=$db->getPictureList();
	foreach ($pictureList as $picture) {
		writeSitemapLink("picture?id=".$picture["id"],$element["changeDate"]);
	}

	$classList=$db->getClassList(getRealId(getAktSchool()));
	foreach ($classList as $class) {
		writeSitemapLink("hometable?classid=".$class["id"],$element["changeDate"]);
	}

	queryPersons($db);
	while ($d=getQueryRow($db)) {
		writeSitemapPerson($d["id"],getPersonLink($d["lastname"],$d["firstname"]),$d["changeDate"]);
	}


/**
 * Use in combination with getQueryRow if you want to make a loop over all personen
 * @param dbDAO $db
 * @return int
 */
function queryPersons($db) {
    $sql="select * from person where changeForID is null";
    $db->dataBase->query($sql);
    return $db->dataBase->count();
}

/**
 * Use in combination with queryPersons if you want to make a loop over all personen
 * @param dbDAO $db
 * @return array
 */
 function getQueryRow ($db) {
    return $db->dataBase->fetchRow();
}
?>
</urlset>

