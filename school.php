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
        <h2><img src="favicon.jpg"/> <?php echo $school["name"] ?></h2>


        <?php if (isActionParam("edit")) { ?>
            <?php foreach ($content as $idx=>$paragraph) { ?>
                <div class="row">
                    <div class="col-md-7">
                        <textarea id="text-<?php echo $idx ?>" style="width: 100%; height:250px">
<?php echo $paragraph["text"] ?>
                        </textarea>
                    </div>
                    <div class="col-md-4" style="border: 1px solid lightgrey; padding: 10px;margin: 10px;">
                            <?php if (isset($paragraph["picture"]["url"])) { ?>
                            <img src="<?php echo $paragraph["picture"]["url"] ?>"><br/>
                        <?php } ?>
                        <input  placeholder="url"
                                id="picture-url-<?php echo $idx ?>"
                                value="<?php echo isset($paragraph["picture"]["url"])?$paragraph["picture"]["url"]:"" ?>"
                                style="width:100%"/>
                        <input  placeholder="kép címe"
                                id="picture-text-<?php echo $idx ?>"
                                value="<?php echo isset($paragraph["picture"]["text"])?$paragraph["picture"]["text"]:"" ?>"
                                style="width:100%"/>
                    </div>
                </div>
                <div class="row col-md-11" style="padding: 15px;">
                    <form action="school">
                        <button name="action" value="save" class="btn btn-success">kiment</button>
                        <button name="action" value="cancel" class="btn btn-warning">elözö versió</button>
                        <button name="action" value="delete" class="btn btn-danger">töröl</button>
                        <button name="action" value="new" class="btn btn-warning">új bekedzés</button>
                        <button name="action" value="up" class="btn btn-warning">feljebb</button>
                        <button name="action" value="down" class="btn btn-warning">lejjebb</button>
                    </form>
                    <hr/>
                </div>
            <?php } ?>
            <div class="row"></div>
        <?php } else { ?>



            <div class="row">
                Utoljára módosította: <?php echo getPersonLinkAndPicture($db->getPersonByID($school["changeUserID"])) ?>
                <?php echo Appl::dateTimeAsStr($school["changeDate"]) ?>
                <form action="school">
                    <button name="action" value="edit" class="btn btn-success">módosítom / kiegészítem</button>
                </form>
            </div>
            <?php foreach ($content as $paragraph) { ?>
                <div class="row">
                    <h3><?php echo $paragraph["title"] ?></h3>
                    <?php if (isset($paragraph["picture"]) && isset($paragraph["picture"]["url"]) ) {?>
                        <div class="col-md-7"><?php echo $paragraph["text"] ?></div>
                        <div class="col-md-4" style="border: 1px solid lightgrey; padding: 10px;margin: 10px;">
                            <img src="<?php echo $paragraph["picture"]["url"] ?>"><br/>
                            <?php echo $paragraph["picture"]["text"] ?>
                        </div>
                    <?php } else { ?>
                        <div class="col-md-11"><?php echo $paragraph["text"] ?></div>
                    <?php } ?>
                </div>
            <?php } ?>
            <hr />
            <form action="school">
                <button name="action" value="edit" class="btn btn-success">módosítom / kiegészítem</button>
            </form>
        <?php } ?>
    </div>

<script>
    function schoolEdit() {
        document.location.href="school?action=edit";
    }
</script>
<?php  include("homefooter.inc.php");?>