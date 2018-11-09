<?php

/**
 * Display the opinion block for a person
 * @param dbBL $db the database business layer
 * @param int $id person id
 * @param bool $teacher
 */
function displayPersonOpinion($db,$id,$teacher) {
    $o=$db->getPersonOpinionCount($id);
    if ($teacher) {
        $ttt="Kedvenc tanárja ".$o->friends." véndiáknak";
        $tt = "Véndiákok kedvenc tanárja";
        $t = "Kellemes tanár";
    } else {
        $ttt="Barátságainak száma: ".$o->friends ;
        $tt="Barátai";
        $t ="Kellemes vicces véndiák";
    }
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
        <?php if ($o->opinions>0) {?>
            <a href="javascript:showOpinions(<?php echo $id ?>,'Vélemények','person')" title="Vélemények száma: <?php echo $o->opinions ?>">
            <span style="margin-left: 20px;">
                <img src="images/opinion.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->opinions ?></span>
            </span>
            </a>
        <?php } if ($o->friends>0) { ?>
            <a href="javascript:showOpinions(<?php echo $id ?>,'<?php echo $tt ?>','person','friends')" title="<?php echo $ttt ?>">
            <span style="margin-left: 20px;">
             <img src="images/<?php echo $teacher?'favorite.png':'friendship.jpg'?>" style="width: 32px"/><span class="countTag"><?php echo $o->friends ?></span>
            </span>
            </a>
        <?php } if ($o->funny>0) {?>
            <a href="javascript:showOpinions(<?php echo $id ?>,'<?php echo $t ?>','person','friendly')" title="Kedves vicces személy <?php echo $o->funny ?> vélemény alapján">
            <span style="margin-left: 20px;">
                <img src="images/funny.png" style="width: 32px"/><span class="countTag"><?php echo $o->funny ?></span>
            </span>
            </a>
        <?php } if ($o->sport>0) {?>
            <a href="javascript:showOpinions(<?php echo $id ?>,'Aktív beálítottságú','person','sport')" title="Sportoló <?php echo $o->sport ?> személy véleménye alapján">
            <span style="margin-left: 20px;">
                <img src="images/runner.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->sport ?></span>
            </span>
            </a>
        <?php } ?>
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
    $o = $db->getPictureOpinionCount($id);
    ?>
    <div>
        <buton onclick="<?php
        echo 'showPictureOpinion('.$id.','.getLoggedInUserId().')';
        ?>" class="btn btn-default" >
            <img src="images/opinion.jpg" style="width: 22px"/> Véleményem
        </buton>
        <?php if ($o->opinions>0) {?>
            <a href="javascript:showOpinions(<?php echo $id ?>,'Vélemények','picture')" title="Vélemények száma: <?php echo $o->opinions ?>">
            <span style="margin-left: 20px;">
                <img src="images/opinion.jpg" style="width: 32px"/><span class="countTag"><?php echo $o->opinions ?></span>
            </span>
            </a>
        <?php } if ($o->favorite>0) {?>
            <a href="javascript:showOpinions(<?php echo $id ?>,'Kedvenc képe','picture','favorite')" title="<?php echo $o->favorite ?> személynek a kedvenc képei közé tartozik.">
            <span style="margin-left: 20px;">
                <img src="images/favorite.png" style="width: 32px"/><span class="countTag"><?php echo $o->favorite ?></span>
            </span>
            </a>
        <?php } if ($o->content>0) {?>
            <a href="javascript:showOpinions(<?php echo $id ?>,'Képnek jó tartalma','picture','content')" title="<?php echo $o->content ?> vélemény szerint ennek a képnek jó a tartalma.">
            <span style="margin-left: 20px;">
                <img src="images/funny.png" style="width: 32px"/><span class="countTag"><?php echo $o->content ?></span>
            </span>
            </a>
        <?php } if ($o->nice>0) {?>
            <a href="javascript:showOpinions(<?php echo $id ?>,'Szép a kép tartalma','picture','nice')" title="Ennek a képnek szép a tartalma <?php echo $o->opinions ?> vélemény szerint.">
            <span style="margin-left: 20px;">
                <img src="images/star.png" style="width: 32px"/><span class="countTag"><?php echo $o->opinions ?></span>
            </span>
            </a>
        <?php } ?>
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
    border-radius: 4px;background-color: sandybrown;
    font-size: 10px;color: black;
    position: relative;left: -13px;top: 9px;
}
.btnb, .btns { width:105px; height:26px;  margin-top:2px; text-align: left;}

.btns { position:relative;top:15px;}

.uname {     background-color: lightgray; padding: 4px; margin: 3px; border-radius: 4px; }
");

\maierlabs\lpfw\Appl::addJsScript("
    function showPersonOpinion(id,uid) {
        showOpinion(id,uid,$('#opinionperson').html());
    }
    function showTeacherOpinion(id,uid) {
        showOpinion(id,uid,$('#opinionteacher').html());
    }
    
    function showOpinion(id,uid,html) {
        html = html.replace(new RegExp('{id}', 'g'),id);
        html = html.replace(new RegExp('{uid}', 'g'),uid);
        $('#o-person-'+id).hide();
        $('#o-person-'+id).html(html);
        $('#o-person-'+id).show('slow');
    }

    function showPictureOpinion(id,uid) {
        var html=$('#opinionpicture').html();
        html = html.replace(new RegExp('{id}', 'g'),id);
        html = html.replace(new RegExp('{uid}', 'g'),uid);
        $('#o-picture-'+id).hide();
        $('#o-picture-'+id).html(html);
        $('#o-picture-'+id).show('slow');
    }

    function savePersonOpinion(id,uid) {
        $('#o-person-'+id).hide('slow');
        $('#o-person-'+id).html('');
    }

    function savePictureOpinion(id,uid) {
        $('#o-picture-'+id).hide('slow');
        $('#o-picture-'+id).html('');
    }

    function closeOpinionList(id,type) {
        $('#o-'+type+'-'+id).hide('slow');
        $('#o-'+type+'-'+id).html('');
    }
    
    function addFriendship(id,uid) {
        alert('Friendship '+id);
    }

    function addFriendly(id,uid) {
        alert('Friendly '+id);
    }

    function addSports(id,uid) {
        alert('Sports '+id);
    }
    
    function showOpinions(id,title,type,count) {
        if (count==null) count='';
        $.ajax({
            url:'ajax/getOpinions.php?id='+id+'&type='+type+'&count='+count,
            type:'GET',
            success:function(data){
                var html=$('#opinionlist').html();
                var text = '';
                data.forEach(function(e) {
                    text +='<div><div class=\"uname\">'+e.name;
                    text +='<span style=\"float:right\">'+e.date+'</span></div>';
                    if (null!=e.text)
                     text +=e.text;
                    text +='</div>';
                });
                html = html.replace(new RegExp('{id}', 'g'),id);
                html = html.replace(new RegExp('{type}', 'g'),type);
                html = html.replace(new RegExp('{text}', 'g'),text);
                html = html.replace(new RegExp('{title}', 'g'),title);
                $('#o-'+type+'-'+id).hide();
                $('#o-'+type+'-'+id).html(html);
                $('#o-'+type+'-'+id).show('slow');
            },
            error:function(error) {
                $('#o-'+type+'-'+id).hide();
                $('#o-'+type+'-'+id).html('error');
                $('#o-'+type+'-'+id).show('slow');
            }
        });
    }
");
?>
<div id="opinionperson" style="display: none">
    <div class="optiondiv">
        <span style="display: inline-block; float: right;">
            <button onclick="addFriendship({id},{uid})" title="Jó barátok vagyunk illetve voltunk." class="btnb btn btn-sm"><img src="images/friendship.jpg" style="width: 16px"/> Barátom</button><br/>
            <button onclick="addFriendly({id},{uid})" title="Kellemes, jókedvű, vicces és szóraztató" class="btnb btn btn-sm"><img src="images/funny.png" style="width: 16px"/> Vicces</button><br/>
            <button onclick="addSports({id},{uid})" title="Sportoló, aktív beállítotságú" class="btnb btn btn-sm"><img src="images/runner.jpg" style="width: 16px"/> Sportoló</button><br/>
            <button onclick="savePersonOpinion({id},{uid})" title="Kimentem megjegyzésem" class="btns btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button><br/>
        </span>
        <span style="display: inline-block; height:130px;width:75%">
            <textarea style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megyjegyzésed, gondolatod"></textarea>
        </span>
    </div>
</div>

<div id="opinionteacher" style="display: none">
    <div class="optiondiv">
        <span style="display: inline-block; float: right;">
            <button onclick="addFriendship({id},{uid})" title="Kedvenc tanáraim közé tartozik." class="btnb btn btn-sm"><img src="images/favorite.png" style="width: 16px"/> Kedvencem</button><br/>
            <button onclick="addFriendly({id},{uid})" title="Kellemes, jókedvű" class="btnb btn btn-sm"><img src="images/funny.png" style="width: 16px"/> Kellemes</button><br/>
            <button onclick="addSports({id},{uid})" title="Aktív beállítotságú" class="btnb btn btn-sm"><img src="images/runner.jpg" style="width: 16px"/> Sportoló</button><br/>
            <button onclick="savePersonOpinion({id},{uid})" title="Kimentem megjegyzésem" class="btns btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button><br/>
        </span>
        <span style="display: inline-block; height:130px;width:75%">
            <textarea style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megyjegyzésed, gondolatod"></textarea>
        </span>
    </div>
</div>

<div id="opinionpicture" style="display: none">
    <div class="optiondiv">
        <span style="display: inline-block; float: right;">
            <button onclick="addFriendship({id},{uid})" title="Kedvenc képeim közé tartozik." class="btnb btn btn-sm"><img src="images/favorite.png" style="width: 16px"/> Kedvencem</button><br/>
            <button onclick="addFriendly({id},{uid})" title="Nagyon jó a kép tartalma" class="btnb btn btn-sm"><img src="images/funny.png" style="width: 16px"/> Jó tartalom</button><br/>
            <button onclick="addSports({id},{uid})" title="Nagyon szép a kép tartalma" class="btnb btn btn-sm"><img src="images/star.png" style="width: 16px"/> Szép kép</button><br/>
            <button onclick="savePictureOpinion({id},{uid})" title="Kimentem megjegyzésem" class="btns btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button><br/>
        </span>
        <span style="display: inline-block; height:130px;width:75%">
            <textarea style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megyjegyzésed, gondolatod"></textarea>
        </span>
    </div>
</div>

<div id="opinionlist" style="display: none">
    <div class="optiondiv">
        <span style="color:white;margin: 9px;display: inline-block; font-weight: bold">{title}</span>
        <span style="display: inline-block; float: right;">
            <button onclick="closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div style="display: inline-block; height:150px; width:100%; overflow:auto; background-color:white;border-radius: 5px;">
            {text}
        </div>
    </div>
</div>
