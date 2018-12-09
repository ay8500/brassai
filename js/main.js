$( document ).ready(function() {
    $(window).resize(function() {
        onResize();
    });
    onResize();
    setTimeout(clearDbMessages, 10000);
});
var logoTimer;
var logoTop=-20;
var logoDirection =-1;

function onResize(hplus) {
    var h= 	removePX($(".sub_title").css("height"))+
        removePX($(".appltitle").css("height"))+
        removePX($("#main-menu").css("height"))+32;
    if (null!=hplus)
        h +=hplus;
    var hh = removePX($("#homelogo").css("height"));

    $(".homeLogo").css("height",(h)+"px");
    clearInterval(logoTimer);
    logoTimer = setInterval(function() {
        $("#homelogo").css("top",logoTop+"px");
        logoTop=logoTop+logoDirection;
        if (logoTop<h-hh) 	logoDirection=1;
        if( logoTop>=0) 	logoDirection=-1;
    }, 50);
}

function removePX(p) {
    if (null!=p)
        return parseInt(p.substr(0,p.length-2));
    else
        return 0;
}

function clearDbMessages() {
    if ($(".resultDBoperation").html()!="")
        $(".resultDBoperation").slideUp("slow");
}

function showDbMessage(text,type) {
    $(".resultDBoperation").html('<div class="alert alert-'+type+'">'+text+'</div>');
    $(".resultDBoperation").slideDown("slow");
    setTimeout(clearDbMessages, 10000);
}

function checkSession() {
    var timezone_offset_minutes = new Date().getTimezoneOffset();
    timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
    $.ajax({
        url: "ajax/isSessionAlive.php?timezone="+timezone_offset_minutes,
        type:"GET",
        success:function(data){
            setTimeout(checkSession,10000);
        },
        error:function(error) {
            document.location.href="index.php";
        }
    });
}

function showModalMessage(title,text,type) {
    if (type==null) type="default";
    $(".modal-title").html(title);
    $(".modal-body").html(('<div class="alert alert-'+type+'">'+text+'</div>'));
    $("#modal-close").show();$(".modal-footer").show();
    $('#myModal').modal({show: 'false' });
}

function showWaitMessage() {
    $(".modal-title").html('Adatok kimentése');
    $(".modal-body").html(('<div class="alert alert-default">Köszönjük a módosítást. Az adatok feldolgozása folyamatban...<div style="margin-top:15px;width: 100%;text-align: center"><img src="images/loading.gif" /></div></div>'));
    $("#modal-close").hide();$(".modal-footer").hide();
    $('#myModal').modal({
        show: 'true',
        backdrop: 'static',
        keyboard: false
    });
}

function clearModalMessage() {
    $('#myModal').modal('hide');
}