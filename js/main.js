$( document ).ready(function() {
    $(window).resize(function() {
        onResize();
    });
    onResize();
    setTimeout(clearDbMessages, 10000);
    checkSession();
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

