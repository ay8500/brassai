<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
include_once "dbBL.class.php";
use maierlabs\lpfw\Appl as Appl;
global $db;

Appl::setSiteTitle("Kolzsvári középiskola");
Appl::setSiteSubTitle(getActSchoolName());
include("homemenu.inc.php");
if (getActSchoolId()==null) {
    ?><div class="well">Iskola nincs kiválasztva.</div><?php
    include ("homefooter.inc.php");
}

$school=getActSchool();
if (!isset($school["history"]) || strlen($school["history"])<100 ) {
    $content = json_decode('[
        {"title":"Cím",
        "picture":{"url":"images/avatar.jpg","text":"Kép"},
        "text":"Szöveg:Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime mollitia, molestiae quas vel sint commodi repudiandae consequuntur voluptatum laborum minima nesciunt dolorem! Officiis iure rerum voluptates a cumque velit "}
        ,{"title":"Cím",
        "text":"Szöveg:Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime mollitia, molestiae quas vel sint commodi repudiandae consequuntur voluptatum laborum minima nesciunt dolorem! Officiis iure rerum voluptates a cumque velit "}
    ]',true);
} else {
    $content = json_decode($school["history"]);
}
?>
    <div class="well">
        <?php foreach ($content as $paragraph) { ?>
            <div class="row">
                <h3><?php echo $paragraph["title"] ?></h3>
                <div class="col-md-7"><?php echo $paragraph["text"] ?></div>
                <?php if (isset($paragraph["picture"]) && isset($paragraph["picture"]["url"]) ) {?>
                <div class="col-md-4" style="border: 1px solid lightgrey; padding: 10px;margin: 10px;">
                    <img src="<?php echo $paragraph["picture"]["url"] ?>"><br/>
                    <?php echo $paragraph["picture"]["text"] ?>
                </div>
                <?php } ?>
            </div>
        <?php } ?>
        <hr />
        <button class="btn btn-default" onclick="schoolEdit()">módosítom / kiegészítem</button>
    </div>

<script>
    function schoolEdit() {
        document.location.href="school?action=edit";
    }
</script>
<?php  include("homefooter.inc.php");?>