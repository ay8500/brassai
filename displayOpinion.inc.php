<?php

/**
 * Display the opinion block for a person
 * @param dbDaOpinion $db the database business layer
 * @param int $id person id
 * @param bool $teacher is the person a teacher
 * @param bool $decesed person is decesed
 * @return void
 */
function displayPersonOpinion($db,$id,$teacher,$decesed) {
    $o=$db->getOpinionCount($id,'person');
    if ($teacher) {
        $ttt="Kedvenc tanárja ".$o->friends." véndiáknak";
        $tt = "Véndiákok kedvenc tanárja";
    } else {
        $ttt="Barátságainak száma: ".$o->friends ;
        $tt="Barátai";
    }
    $ooption = $o->opinions>0?'':'style="display:none"';
    $ofriends = $o->friends>0?'':'style="display:none"';
    $osport = $o->sport>0?'':'style="display:none"';
    $oeaster = $o->easter>0?'':'style="display:none"';
    $ocandles = $o->candles>1 && $decesed?'':'style="display:none"';
    ?>
    <div>
        <buton onclick="<?php
        if ($teacher)
            echo 'showTeacherOpinion('.$id.','.getLoggedInUserIdOrNull().')';
        else
            echo 'showPersonOpinion('.$id.','.getLoggedInUserIdOrNull().')';
        ?>" class="btn btn-default" >
            <img src="images/opinion.jpg" style="width: 22px"/> Véleményem
        </buton>
        <a id="c-person-candle-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Emlékére gyertyát gyújtottak:','person','candle',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="Égő gyertyák száma: <?php echo $o->candles-1 ?>" <?php echo $ocandles ?>>
            <span style="margin-right: -8px;">
                <img src="images/candle6.gif" style="border-radius:5px; width: 32px"/><span class="countTag"><?php echo $o->candles-1?></span>
            </span>
        </a>
        <a id="c-person-text-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Vélemények','person','text',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="Vélemények száma: <?php echo $o->opinions ?>" <?php echo $ooption?>>
            <span style="margin-right: -8px;">
                <img src="images/opinion.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->opinions ?></span>
            </span>
        </a>
        <a id="c-person-friend-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'<?php echo $tt ?>','person','friend',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="<?php echo $ttt ?>" <?php echo $ofriends?>>
            <span style="margin-right: -8px;">
                <img src="images/<?php echo $teacher?'favorite.png':'friendship.jpg'?>" style="width: 32px"/><span class="countTag"><?php echo $o->friends ?></span>
            </span>
        </a>
        <a id="c-person-sport-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Aktív beálítottságú','person','sport',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="Sportoló <?php echo $o->sport ?> személy véleménye alapján" <?php echo $osport?>>
            <span style="margin-right: -8px;">
                <img src="images/runner.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->sport ?></span>
            </span>
        </a>
        <a id="c-person-easter-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Húsvéti locsolók','person','easter',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="Öntözök száma <?php echo $o->easter ?> " <?php echo $oeaster?>>
            <span style="margin-right: -8px;">
                <img src="images/easter.png" style="width: 32px"/><span class="countTag"><?php echo $o->easter ?></span>
            </span>
        </a>
    </div>
    <div id="o-person-<?php echo $id ?>"></div>
    <?php
}

/**
 * Display the opinion block for a picture
 * @param dbBL $db the database business layer
 * @param int $id picture id
 */
function displayPictureOpinion($db,$id){
    $o = $db->getOpinionCount($id,'picture');
    $onice = $o->nice>0?'':'style="display:none"';
    $oopinion = $o->opinions>0?'':'style="display:none"';
    $ofavorite = $o->favorite>0?'':'style="display:none"';
    $ocontent = $o->content>0?'':'style="display:none"';
    ?>
    <div>
        <buton onclick="<?php
        echo 'showPictureOpinion('.$id.','.getLoggedInUserId().')';
        ?>" class="btn btn-default" >
            <img src="images/opinion.jpg" style="width: 22px"/> Véleményem
        </buton>
        <a id="c-picture-text-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Vélemények','picture','text',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="Vélemények száma: <?php echo $o->opinions ?>" <?php echo $oopinion?>>
            <span style="margin-right: -8px;">
                <img src="images/opinion.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->opinions ?></span>
            </span>
        </a>
        <a id="c-picture-favorite-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Kedvenc képe','picture','favorite',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="<?php echo $o->favorite ?> személynek a kedvenc képei közé tartozik." <?php echo $ofavorite?>>
            <span style="margin-right: -8px;">
                <img src="images/favorite.png" style="width: 32px"/><span class="countTag"><?php echo $o->favorite ?></span>
            </span>
        </a>
        <a id="c-picture-content-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Képnek jó tartalma','picture','content',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="<?php echo $o->content ?> vélemény szerint ennek a képnek jó a tartalma." <?php echo $ocontent?>>
            <span style="margin-right: -8px;">
                <img src="images/funny.png" style="width: 32px"/><span class="countTag"><?php echo $o->content ?></span>
            </span>
        </a>
        <a id="c-picture-nice-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Szép a kép tartalma','picture','nice',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="Ennek a képnek szép a tartalma <?php echo $o->nice ?> vélemény szerint." <?php echo $onice?>>
            <span style="margin-right: -8px;">
                <img src="images/star.png" style="width: 32px"/><span class="countTag"><?php echo $o->nice ?></span>
            </span>
        </a>
    </div>
    <div id="o-picture-<?php echo $id ?>"></div>
    <?php
}

/**
 * Display the opinion block for a message
 * @param dbDaOpinion $db
 * @param int $id picture id
 */
function displayMessageOpinion($db,$id){
    $o = $db->getOpinionCount($id,'message');
    $onice = $o->nice>0?'':'style="display:none"';
    $oopinion = $o->opinions>0?'':'style="display:none"';
    $ofavorite = $o->favorite>0?'':'style="display:none"';
    $ocontent = $o->content>0?'':'style="display:none"';
    ?>
    <div>
        <buton onclick="<?php
        echo 'showMessageOpinion('.$id.','.getLoggedInUserId().')';
        ?>" class="btn btn-default" >
            <img src="images/opinion.jpg" style="width: 22px"/> Véleményem
        </buton>
        <a id="c-message-text-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Vélemények','message','text',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="Vélemények száma: <?php echo $o->opinions ?>" <?php echo $oopinion?>>
            <span style="margin-right: -8px;">
                <img src="images/opinion.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->opinions ?></span>
            </span>
        </a>
        <a id="c-message-favorite-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Kedvenc üzenetem','message','favorite',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="<?php echo $o->favorite ?> személynek a kedvenc képei közé tartozik." <?php echo $ofavorite?>>
            <span style="margin-right: -8px;">
                <img src="images/favorite.png" style="width: 32px"/><span class="countTag"><?php echo $o->favorite ?></span>
            </span>
        </a>
        <a id="c-message-content-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Üzenetnek jó tartalma','message','content',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="<?php echo $o->content ?> vélemény szerint ennek a képnek jó a tartalma." <?php echo $ocontent?>>
            <span style="margin-right: -8px;">
                <img src="images/funny.png" style="width: 32px"/><span class="countTag"><?php echo $o->content ?></span>
            </span>
        </a>
        <a id="c-message-nice-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Szép kép van az üzenetbe','message','nice',<?php echo getLoggedInUserIdOrNull() ?>)"
           title="Ennek a képnek szép a tartalma <?php echo $o->nice ?> vélemény szerint." <?php echo $onice?>>
            <span style="margin-right: -8px;">
                <img src="images/star.png" style="width: 32px"/><span class="countTag"><?php echo $o->nice ?></span>
            </span>
        </a>
    </div>
    <div id="o-message-<?php echo $id ?>"></div>
    <?php
}

function getLoggedInUserIdOrNull() {
    if (getLoggedInUserId()!=null)
        return getLoggedInUserId();
    return "null";
}


\maierlabs\lpfw\Appl::addCssStyle("
.optiondiv{
    background-color: #9ba3ab; border-radius: 5px; width: 100%; 
    padding: 5px; margin-top:5px;
}

.countTag{
    border-radius: 7px;background-color: sandybrown;
    font-size: 10px;color: black; padding: 2px;
    position: relative;left: -13px;top: 9px;
}

.aopinion {cursor:pointer}
.taopinion {display: inline-block; height:150px; width:100%; overflow:auto; background-color:white;border-radius: 5px;} 
.uname {background-color: lightgray; padding: 4px; margin: 3px; border-radius: 4px; }
.oglyph{background-color: sandybrown; padding: 0px; cursor: pointer; border-radius: 8px;}
.otitle{margin: 9px;display: inline-block; color:black; font-weight: bold;}
");

\maierlabs\lpfw\Appl::addJsScript("
    function showPersonOpinion(id,uid) {
        showOpinion(id,uid,$('#opinionperson').html());
        return false;
    }
    function showTeacherOpinion(id,uid) {
        showOpinion(id,uid,$('#opinionteacher').html());
        return false;
    }
    
    function showOpinion(id,uid,html) {
        html = html.replace(new RegExp('{id}', 'g'),id);
        html = html.replace(new RegExp('{uid}', 'g'),uid);
        html = html.replace(new RegExp('{type}', 'g'),'person');
        $('#o-person-'+id).html(html);
        $('#o-person-'+id).show('fast');
    }

    function showPictureOpinion(id,uid) {
        var html=$('#opinionpicture').html();
        html = html.replace(new RegExp('{id}', 'g'),id);
        html = html.replace(new RegExp('{uid}', 'g'),uid);
        html = html.replace(new RegExp('{type}', 'g'),'picture');
        $('#o-picture-'+id).html(html);
        $('#o-picture-'+id).show('fast');
        return false;
    }

    function showMessageOpinion(id,uid) {
        var html=$('#opinionmessage').html();
        html = html.replace(new RegExp('{id}', 'g'),id);
        html = html.replace(new RegExp('{uid}', 'g'),uid);
        html = html.replace(new RegExp('{type}', 'g'),'message');
        $('#o-message-'+id).html(html);
        $('#o-message-'+id).show('fast');
        return false;
    }

    function showPictureOpinion(id,uid) {
        var html=$('#opinionmessage').html();
        html = html.replace(new RegExp('{id}', 'g'),id);
        html = html.replace(new RegExp('{uid}', 'g'),uid);
        html = html.replace(new RegExp('{type}', 'g'),'message');
        $('#o-message-'+id).html(html);
        $('#o-message-'+id).show('fast');
        return false;
    }


    function saveEasterOpinion(id,type,stype,uid) {
        showModalMessage('Virtuális husvéti locsolás','Rózsa, rózsa szép virágszál,<br/>Szálló szélben hajladozzál.<br/>Napsütésben nyiladozzál,<br/>Meglocsollak, illatozzál.','info',{
            'Megszabad locsolni?':function() {
                saveOpinion(id,type,stype,uid);
                clearModalMessage();
            }
        });
    }

    function saveOpinion(id,type,stype,uid) {
        showWaitMessage();
        closeOpinionList(id,type);
        var text='';
        if (stype=='text')
            text=$('#t-'+type+'-'+id).val();
        $.ajax({
            url:'ajax/setOpinion.php?id='+id+'&type='+type+'&count='+stype+'&text='+text,
            type:'GET',
            success:function(data){
                    $('#fb-root').hide();
                    if (data.result=='ok') {
                        clearModalMessage();
                        showOpinionLogo(id,type,stype,data.count);
                    } else if (data.result=='empty') {
                        showModalMessage('Vélemény','Sajnos a vélemény mező szövege üres. Kérjük ismételd meg véleményed. Köszönjük szépen.');
                    } else if (data.result=='exists') {
                        if (type=='easter')
                            alert('Ezt a virágszálat már meglocsoltad.');
                        else
                            showModalMessage('Vélemény','Ezt a tipusú véleményt már megadtad.','warning');
                    } else if (data.result=='count') {
                        showModalMessage('Anonim felhasználó véleménye','Sajnos véleményt mint anonim felhasználó csak bizonyos mértékben adhatsz. Kérjük jelentkezz be és ismételd meg véleményed. Köszönjük szépen.','warning');
                    } else if (data.result=='login') {
                        if (type=='easter')
                            alert('Sajnos csak bejelentkezett felhasználók tudnak locsolni. Kérjük jelentkezz be és locsolj újból. Köszönjük szépen.');
                        else
                            showModalMessage('Anonim felhasználó véleménye','Sajnos ezt a véleményt csak bejelentkezett felhasználók adhatják meg. Kérjük jelentkezz be és ismételd meg véleményed. Köszönjük szépen.','warning');
                    }
            },
            error:function(error) {
                clearModalMessage();
                $('#o-'+type+'-'+id).html('error');
                $('#o-'+type+'-'+id).show('fast');
            }
        });
        return false;
    }

    function showOpinionLogo(id,type,stype,count) {
        $('#c-'+type+'-'+stype+'-'+id).show();
        $($($('#c-'+type+'-'+stype+'-'+id).children()[0]).children()[1]).html(count);
        return false;
    }

    
    function closeOpinionList(id,type) {
        $('#o-'+type+'-'+id).hide('fast');
        return false;
    }
    
    var opinionTitle = '';

    function showOpinions(id,title,type,count,uid) {
        if (count==null) count='';
        $('#o-'+type+'-'+id).html('pillanat <img src=\"images/loading.gif\" />');
        $('#o-'+type+'-'+id).show();
        $.ajax({
            url:'ajax/getOpinions.php?id='+id+'&type='+type+'&count='+count,
            type:'GET',
            success:function(data){
                var html=$('#opinionlist').html();
                var text = '';
                data.forEach(function(e) {
                    text +='<div><div class=\"uname\">'+e.name;
                    text +='<span title=\"' +e.ip+  '\" onclick=\"showip(\''+e.ip+ '\')\"> '+(e.ip==''?'':' show IP')+'</span>';
                    text +='<span style=\"float:right\">'+e.date;
                    if (e.myopinion) {
                        text +=' <span title=\"kitöröl\" class=\"oglyph glyphicon glyphicon-remove\" onclick=\"deleteOpinion('+e.id+')\"></span>';
                    }
                    text +='</span></div>';
                    if (null!=e.text)
                     text +=e.text;
                    text +='</div>';
                });
                html = html.replace(new RegExp('{id}', 'g'),id);
                html = html.replace(new RegExp('{type}', 'g'),type);
                html = html.replace(new RegExp('{text}', 'g'),text);
                if (title!='') {
                    opinionTitle=title;
                } else {
                    title=opinionTitle;
                }
                html = html.replace(new RegExp('{title}', 'g'),title);
                $('#o-'+type+'-'+id).hide();
                $('#o-'+type+'-'+id).html(html);
                $('#o-'+type+'-'+id).show('fast');
            },
            error:function(error) {
                $('#o-'+type+'-'+id).html('error');
                $('#o-'+type+'-'+id).show('fast');
            }
        });
        return false;
    }
    
    function deleteOpinion(id) {
        showWaitMessage();
        $.ajax({
            url:'ajax/deleteOpinion.php?id='+id,
            type:'GET',
            success:function(data){
                clearModalMessage();
                showOpinions(data.id,'',data.table,data.type);
                showOpinionLogo(data.id,data.table,data.type,data.count);
            },
            error:function(error) {
                alert('error');
            }
        });
        return false;
    }
");
if (!isActionParam("showmore")) {
?>
<div id="opinionperson" style="display: none">
    <div class="optiondiv">
        <span class="otitle">Véleményem</span>
        <span style="display: inline-block; float: right;">
            <button onclick="return saveOpinion({id},'person','text',{uid})" title="Kimentem" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
            <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div  class="taopinion">
            <textarea id='t-{type}-{id}' style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megjegyzésed, gondolatod, dicséreted"></textarea>
        </div>
        <div>
            <hr/>
            <button onclick="return saveOpinion({id},'person','friend',{uid})" title="Jó barátok vagyunk illetve voltunk." class="btn btn-default"><img src="images/friendship.jpg" style="width: 16px"/> Barátom</button>
            <button onclick="return saveOpinion({id},'person','sport',{uid})" title="Aktív beállítotságú (sportoló)" class="btn btn-default"><img src="images/runner.jpg" style="width: 16px"/> Sportoló</button>
        </div>
    </div>

</div>

<div id="opinionteacher" style="display: none">
    <div class="optiondiv">
        <span class="otitle">Véleményem volt tanáromról</span>
        <span style="display: inline-block; float: right;">
            <button onclick="return saveOpinion({id},'person','text',{uid})" title="Kimentem" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
            <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div  class="taopinion">
            <textarea id='t-{type}-{id}' style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megjegyzésed, gondolatod, élményed"></textarea>
        </div>
        <div>
            <hr/>
            <button onclick="return saveOpinion({id},'person','friend',{uid})" title="Kedvenc tanáraim közé tartozik." class="btn btn-default"><img src="images/favorite.png" style="width: 16px"/> Kedvencem</button>
            <button onclick="return saveOpinion({id},'person','sport',{uid})" title="Aktív beállítotságú (sportoló)" class="btn btn-default"><img src="images/runner.jpg" style="width: 16px"/> Aktív</button>
        </div>
    </div>
</div>

<div id="opinionpicture" style="display: none">
    <div class="optiondiv">
        <span class="otitle">Véleményem erről a képről</span>
        <span style="display: inline-block; float: right;">
            <button onclick="return saveOpinion({id},'picture','text',{uid})" title="Kimentem" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
            <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div class="taopinion">
            <textarea id='t-{type}-{id}' style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide dicséreted, megjegyzésed, gondolatod"></textarea>
        </div>
        <div>
            <hr/>
            <button onclick="return saveOpinion({id},'picture','favorite',{uid})" title="Kedvenc képeim közé tartozik." class="btn btn-sm"><img src="images/favorite.png" style="width: 16px"/> Kedvencem</button>
            <button onclick="return saveOpinion({id},'picture','content',{uid})" title="Nagyon jó a kép tartalma" class="btn btn-sm"><img src="images/funny.png" style="width: 16px"/> Jó tartalom</button>
            <button onclick="return saveOpinion({id},'picture','nice',{uid})" title="Nagyon szép a kép tartalma" class="btn btn-sm"><img src="images/star.png" style="width: 16px"/> Szép kép</button>
        </div>
    </div>
</div>

<div id="opinionmessage" style="display: none">
    <div class="optiondiv">
        <span class="otitle">Véleményem erről az üzenetről</span>
        <span style="display: inline-block; float: right;">
        <button onclick="return saveOpinion({id},'message','text',{uid})" title="Kimentem" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
        <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
    </span>
        <div class="taopinion">
            <textarea id='t-{type}-{id}' style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megjegyzésed, gondolatod, hozzászólásod"></textarea>
        </div>
        <div>
            <hr/>
            <button onclick="return saveOpinion({id},'message','favorite',{uid})" title="Kedvenc üzeneteim közé tartozik." class="btn btn-sm"><img src="images/favorite.png" style="width: 16px"/> Kedvencem</button>
            <button onclick="return saveOpinion({id},'message','content',{uid})" title="Az üzenet tartalma tetszik nekem" class="btn btn-sm"><img src="images/funny.png" style="width: 16px"/> Jó tartalom</button>
        </div>
    </div>
</div>


<div id="opinionlist" style="display: none">
    <div class="optiondiv">
        <span class="otitle">{title}</span>
        <span style="display: inline-block; float: right;">
            <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div style="display: inline-block; height:150px; width:100%; overflow:auto; background-color:white;border-radius: 5px;">
            {text}
        </div>
    </div>
</div>
<?php } ?>