<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once Config::$lpfw.'htmlParser.class.php';
include_once 'dbBL.class.php';
use maierlabs\lpfw\Appl as Appl;

Appl::setSiteSubTitle("Bartha Miklós céh, Kolozsvár társaság és kolozsvári véndiákok összevonása");
unsetActSchool();
include "homemenu.inc.php";
if ( !isUserSuperuser()) {
    Appl::setMessage("Hozzáféresi jog hiányzik!","danger");
    include_once "homefooter.inc.php";
    die();
}

$list = json_decode(\maierlabs\lpfw\htmlParser::loadUrl("https://bmceh.ro/interact.php"));
foreach($list as $id=>$person) {
    $list[$id]->t = 1;
}
$nextlist = json_decode(\maierlabs\lpfw\htmlParser::loadUrl("https://kolozsvartarsasag.bmceh.ro/interact.php"));
foreach($nextlist as $id=>$person) {
    $nextlist[$id]->t = 2;
}
$list = array_merge($list,$nextlist);
$nextlist = getLocalList();
foreach($nextlist as $id=>$person) {
    $nextlist[$id]->t = 3;
}
$list = array_merge($list,$nextlist);
usort($list,function($item1,$item2) {
    //$item1->sort = sortWords($item1->sort);
    //$item2->sort = sortWords($item2->sort);
    if ($item1->sort === $item2->sort) {
        if ($item1->t === $item2->t)
            return 0;
        else
            return strcmp($item1->t,$item2->t);
    }
    return strcmp($item1->sort,$item2->sort);
});
?>
    <div class="well">
        <?php
            $count = 1;
            foreach ($list as $idx=>$person) {
                if ($idx>0  && $list[$idx]->t != $list[$idx-1]->t) {
                    $sorte1 = explode(" ", $list[$idx]->sort);
                    $sorte1s = array_merge(array(),$sorte1);
                    sort($sorte1s);
                    $sorte2 = explode(" ", $list[$idx - 1]->sort);
                    $sorte2s = array_merge(array(),$sorte2);
                    sort($sorte2s);
                    if (
                        $list[$idx]->sort === $list[$idx-1]->sort ||
                        (sizeof($sorte1)>1 && sizeof($sorte2)>1 && $sorte1[0] === $sorte2[0] && $sorte1[1] === $sorte2[1]) ||
                        (sizeof($sorte1)>1 && sizeof($sorte2)>1 && substr($sorte1[0],0,4) === substr($sorte2[0],0,4) && substr($sorte1[1],0,3) === substr($sorte2[1],0,3)) ||
                        //(sizeof($sorte1s)>1 && sizeof($sorte2s)>1 && $sorte1s[0] === $sorte2s[0] && $sorte1s[1] === $sorte2s[1]) ||
                        (!empty($list[$idx - 1]->email) && !empty($list[$idx]->email) && $list[$idx]->email === $list[$idx - 1]->email) ||
                        (!empty($list[$idx - 1]->wikipedia) && !empty($list[$idx]->wikipedia) && $list[$idx]->wikipedia === $list[$idx - 1]->wikipedia) ||
                        (!empty($list[$idx - 1]->facebook) && !empty($list[$idx]->facebook) && $list[$idx]->facebook === $list[$idx - 1]->facebook)
                    )
                        displayPair($list[$idx - 1], $list[$idx], $count++);
                }
            }
        ?>

    </div>
<?php
global $db;

function getLocalList() {
    global $db;
    $ret = array();
    $list = $db->getPersonList("1=1", NULL,NULL,NULL,"id, lastname, firstname, deceasedYear, birthyear, email, facebook, wikipedia");
    foreach ($list as $item) {
        $object =  new stdClass();
        $object->id = $item["id"];
        $object->name = $item["lastname"]." ".$item["firstname"];
        $object->sort = strtolower($object->name);
        $object->sort = str_replace(array("á","é","í","ó","ö","ü","ő","ű","ä","ß"),array("a","e","i","o","o","u","o","u","a","s"),$object->sort);
        $object->email = $item["email"];
        $object->facebook = str_replace(array("https://","http://"),array("",""),$item["facebook"]);
        $object->wikopedia = str_replace(array("https://","http://"),array("",""),$item["wikipedia"]);
        $object->birthyear = $item["birthyear"];
        $object->deceaseyear = $item["deceasedYear"];

        $ret[] = $object;
    }
    return $ret;
}

function displayPair($p1,$p2, $count) {
    echo '<div>';
    echo '<span style="display:inline-block;width: 40px;">'.$count.'</span>';
    echo '<span style="display:inline-block;width: 100px;">';
        displayLogo($p1);displayLink($p1,$p2); displayLogo($p2);
    echo '</span>';
    displayField($p1,$p2,"name");
    echo ' <button onclick="<?php createLinkUrl($p1,$p2)?>" class="btn btn-info">Kapcsolatot létrehoz</button> ';
    displayMoreFields($p1,$p2,array("email","facebook","wikipedia","birthyear","deceasedyear"));
    echo '</div>';
    return $count++;
}

function displayField($p1,$p2,$field) {
    $s1 = !empty($p1->$field)?$p1->$field:"";
    $s2 = !empty($p2->$field)?$p2->$field:"";
    $css = $s1==$s2?"color:green":"color:black";
    echo '<div style="display:inline-block;width:440px;'.$css.'" >'.$s1.','.$s2. '</div>';
}

function displayMoreFields($p1,$p2,$fields) {
    echo '<button class="btn btn-default btn-details" onclick="showDetails(this)">részletek</button>';
    echo '<div class="div-details" style="display:none;" >';
    foreach ($fields as $field) {
        $s1 = !empty($p1->$field) ? $p1->$field : "";
        $s2 = !empty($p2->$field) ? $p2->$field : "";
        if (!empty($s1.$s2)) {
            $css = $s1 == $s2 ? "color:green" : "color:black";
            echo '<div style="' . $css . '" ><b>' . $field . ':</b> ' . $s1 . ' <=> ' . $s2 . '</div>';
        }
    }
    echo '</div>';
}

function sortWords($words) {
    $wordslist = explode(" ",$words);
    sort($wordslist);
    return implode(" ",$wordslist);
}

function displayLogo($p) {
    if ($p->t==1) {
        $src = "images/bmceh_logo.png";
        $title = "Barthe Miklós Céh";
    } else if ($p->t==2) {
        $src = "images/kt_logo.jpg";
        $title = "Kolozsvár társaság";
    } else if ($p->t==3) {
        $src= "images/kolozsvar.png";
        $title = "Koloszvári Véndiákok";
    }
    echo '<img title="'.$title.'-'.$p->id.'" src="'.$src.'"  style="height: 32px;width: 32px;border: none; border-radius:7px;margin-right:5px;"/>';
}

function displayLink($p1,$p2) {

    displayArrowLR();
}

function displayArrowLR() {
    echo( '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M1 11.5a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 11H1.5a.5.5 0 0 0-.5.5zm14-7a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H14.5a.5.5 0 0 1 .5.5z"/>
</svg> ' );
}

function displayArrowL() {
    echo( '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
</svg> ' );
}

function displayArrowR() {
    echo( '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
</svg> ' );
}

function createLinkUrl($p1, $p2) {
    $url = 'document.location.url=';
}

?>
<script>
   function showDetails(o) {
       $(".div-details").hide();
       $(o).next().show();
   }
</script>
<?php include "homefooter.inc.php";
?>