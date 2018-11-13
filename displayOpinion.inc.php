<?php
/**
 * Display the opinion block for a person
 * @param dbBL $db the database business layer
 * @param int $id person id
 * @param bool $teacher is the person a teacher
 */
function displayPersonOpinion($db,$id,$teacher) {
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
    ?>
    <div>
        <buton onclick="<?php
        if ($teacher)
            echo 'showTeacherOpinion('.$id.','.getLoggedInUserId().')';
        else
            echo 'showPersonOpinion('.$id.','.getLoggedInUserId().')';
        ?>" class="btn btn-default" >
            <img src="images/opinion.jpg" style="width: 22px"/> Véleményem
        </buton>
        <a id="c-person-text-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Vélemények','person','text',<?php echo getLoggedInUserId() ?>)"
           title="Vélemények száma: <?php echo $o->opinions ?>" <?php echo $ooption?>>
            <span style="margin-right: -8px;">
                <img src="images/opinion.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->opinions ?></span>
            </span>
        </a>
        <a id="c-person-friend-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'<?php echo $tt ?>','person','friend',<?php echo getLoggedInUserId() ?>)"
           title="<?php echo $ttt ?>" <?php echo $ofriends?>>
            <span style="margin-right: -8px;">
                <img src="images/<?php echo $teacher?'favorite.png':'friendship.jpg'?>" style="width: 32px"/><span class="countTag"><?php echo $o->friends ?></span>
            </span>
        </a>
        <a id="c-person-sport-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Aktív beálítottságú','person','sport',<?php echo getLoggedInUserId() ?>)"
           title="Sportoló <?php echo $o->sport ?> személy véleménye alapján" <?php echo $osport?>>
            <span style="margin-right: -8px;">
                <img src="images/runner.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->sport ?></span>
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
        <a id="c-picture-text-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Vélemények','picture','text',<?php echo getLoggedInUserId() ?>)"
           title="Vélemények száma: <?php echo $o->opinions ?>" <?php echo $oopinion?>>
            <span style="margin-right: -8px;">
                <img src="images/opinion.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->opinions ?></span>
            </span>
        </a>
        <a id="c-picture-favorite-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Kedvenc képe','picture','favorite',<?php echo getLoggedInUserId() ?>)"
           title="<?php echo $o->favorite ?> személynek a kedvenc képei közé tartozik." <?php echo $ofavorite?>>
            <span style="margin-right: -8px;">
                <img src="images/favorite.png" style="width: 32px"/><span class="countTag"><?php echo $o->favorite ?></span>
            </span>
        </a>
        <a id="c-picture-content-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Képnek jó tartalma','picture','content',<?php echo getLoggedInUserId() ?>)"
           title="<?php echo $o->content ?> vélemény szerint ennek a képnek jó a tartalma." <?php echo $ocontent?>>
            <span style="margin-right: -8px;">
                <img src="images/funny.png" style="width: 32px"/><span class="countTag"><?php echo $o->content ?></span>
            </span>
        </a>
        <a id="c-picture-nice-<?php echo $id ?>" class="aopinion" onclick="showOpinions(<?php echo $id ?>,'Szép a kép tartalma','picture','nice',<?php echo getLoggedInUserId() ?>)"
           title="Ennek a képnek szép a tartalma <?php echo $o->nice ?> vélemény szerint." <?php echo $onice?>>
            <span style="margin-right: -8px;">
                <img src="images/star.png" style="width: 32px"/><span class="countTag"><?php echo $o->nice ?></span>
            </span>
        </a>
    </div>
    <div id="o-picture-<?php echo $id ?>"></div>
    <?php
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

    function saveOpinion(id,type,stype,uid) {
        closeOpinionList(id,type);
        var text='';
        if (stype=='text')
            text=$('#t-'+type+'-'+id).val();
        $.ajax({
            url:'ajax/setOpinion.php?id='+id+'&type='+type+'&count='+stype+'&text='+text,
            type:'GET',
            success:function(data){
                if (data.result=='ok') {
                    showOpinionLogo(id,type,stype,data.count);
                } else if (data.result=='empty') {
                    showModalMessage('Vélemény','Sajnos a vélemény mező szövege üres. Kérjük ismételd meg véleményed. Köszönjük szépen.');
                } else if (data.result=='exists') {
                    showModalMessage('Vélemény','Ezt a tipusú véleményt már megadtad.');
                } else if (data.result=='count') {
                    showModalMessage('Anonim felhasználó véleménye','Sajnos véleményt mint anonim felhasználó csak bizonyos mértékben adhatsz. Kérjük jelentkezz be és ismételd meg véleményed. Köszönjük szépen.');
                } else if (data.result=='login') {
                    showModalMessage('Anonim felhasználó véleménye','Sajnos ezt a véleményt csak bejelentkezett felhasználók adhatják meg. Kérjük jelentkezz be és ismételd meg véleményed. Köszönjük szépen.');
                }
            },
            error:function(error) {
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
        $.ajax({
            url:'ajax/deleteOpinion.php?id='+id,
            type:'GET',
            success:function(data){
                if (data.count>=0) {
                    showOpinions(data.id,'',data.table,data.type);
                    showOpinionLogo(data.id,data.table,data.type,data.count);
                }
            },
            error:function(error) {
                alert('error');
            }
        });
        return false;
    }
");
?>
<div id="opinionperson" style="display: none">
    <div class="optiondiv">
        <span class="otitle">Véleményem</span>
        <span style="display: inline-block; float: right;">
            <button onclick="return saveOpinion({id},'person','text',{uid})" title="Kimentem" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
            <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div  class="taopinion">
            <textarea id='t-{type}-{id}' style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megyjegyzésed, gondolatod"></textarea>
        </div>
        <div>
            <hr/>
            <button onclick="return saveOpinion({id},'person','friend',{uid})" title="Jó barátok vagyunk illetve voltunk." class="btn btn-sm"><img src="images/friendship.jpg" style="width: 16px"/> Barátom</button>
            <button onclick="return saveOpinion({id},'person','sport',{uid})" title="Aktív beállítotságú (sportoló)" class="btn btn-sm"><img src="images/runner.jpg" style="width: 16px"/> Sportoló</button>
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
            <textarea id='t-{type}-{id}' style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megyjegyzésed, gondolatod"></textarea>
        </div>
        <div>
            <hr/>
            <button onclick="return saveOpinion({id},'person','friend',{uid})" title="Kedvenc tanáraim közé tartozik." class="btn btn-sm"><img src="images/favorite.png" style="width: 16px"/> Kedvencem</button>
            <button onclick="return saveOpinion({id},'person','sport',{uid})" title="Aktív beállítotságú (sportoló)" class="btn btn-sm"><img src="images/runner.jpg" style="width: 16px"/> Aktív</button>
        </div>
    </div>
</div>

<div id="opinionpicture" style="display: none">
    <div class="optiondiv">
        <span class="otitle">Véleményem erröl a kéröl</span>
        <span style="display: inline-block; float: right;">
            <button onclick="return saveOpinion({id},'picture','text',{uid})" title="Kimentem" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
            <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div class="taopinion">
            <textarea id='t-{type}-{id}' style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megyjegyzésed, gondolatod"></textarea>
        </div>
        <div>
            <hr/>
            <button onclick="return saveOpinion({id},'picture','favorite',{uid})" title="Kedvenc képeim közé tartozik." class="btn btn-sm"><img src="images/favorite.png" style="width: 16px"/> Kedvencem</button>
            <button onclick="return saveOpinion({id},'picture','content',{uid})" title="Nagyon jó a kép tartalma" class="btn btn-sm"><img src="images/funny.png" style="width: 16px"/> Jó tartalom</button>
            <button onclick="return saveOpinion({id},'picture','nice',{uid})" title="Nagyon szép a kép tartalma" class="btn btn-sm"><img src="images/star.png" style="width: 16px"/> Szép kép</button>
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
