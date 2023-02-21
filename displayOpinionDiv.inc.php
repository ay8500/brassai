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

<div id="opinionmusic" style="display: none">
    <div class="optiondiv">
        <span class="otitle">Véleményem erről az zenéről</span>
        <span style="display: inline-block; float: right;">
            <button onclick="return saveOpinion({id},'music','text',{uid})" title="Kimentem" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-save-file"></span> Kiment</button>
            <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div class="taopinion">
            <textarea id='t-{type}-{id}' style="height: 100%;width: 100%;border-radius: 5px" placeholder="Írd ide véleményed, megjegyzésed, gondolatod, hozzászólásod"></textarea>
        </div>
        <div>
            <hr/>
            <button onclick="return saveOpinion({id},'music','favorite',{uid})" title="Kedvenc zeném közé tartozik." class="btn btn-sm"><img src="images/favorite.png" style="width: 16px"/> Kedvencem</button>
            <button onclick="return saveOpinion({id},'music','content',{uid})" title="A zene tetszik nekem" class="btn btn-sm"><img src="images/funny.png" style="width: 16px"/> Jó zene</button>
        </div>
    </div>
</div>

<div id="opinionlist" style="display: none">
    <div class="optiondiv">
        <span class="otitle">{title}</span>
        <span style="display: inline-block; float: right;">
            <a class="btn btn-warning" id="lightcandle-{id}" style="display:none;height:34px;margin:3px;color:black" href="editPerson?&uid={id}&tabOpen=candles" ><img style="height: 25px;border-radius: 33px;" src="images/match.jpg" alt="Meggyújt"> Gyertyát gyújt</a>
            <button onclick="return closeOpinionList({id},'{type}')" title="Bezár" class="btn btn-sm "><span class="glyphicon glyphicon-remove-circle"></span> </button>
        </span>
        <div style="display: inline-block; height:150px; width:100%; overflow:auto; background-color:white;border-radius: 5px;">
            {text}
        </div>
    </div>
</div>
