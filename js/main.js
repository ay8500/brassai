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
var backGroundHPos = 0;

function onResize(hplus) {
    var h= 	$(".sub_title").height()+ $(".appltitle").height()+ $("#main-menu").height()+40;
    if (null!=hplus) {
        h += hplus;
        backGroundHPos = hplus;
    } else {
        h += backGroundHPos;
    }
    var hh = $("#homelogo").height();

    $(".homeLogo").height(h);
    clearInterval(logoTimer);
    if (hh>h) {
        logoTimer = setInterval(function () {
            $("#homelogo").offset({top: logoTop});
            logoTop = logoTop + logoDirection;
            if (logoTop < h - hh) logoDirection = 1;
            if (logoTop >= 0) logoDirection = -1;
        }, 60);
    } else {
        $("#homelogo").offset({top: 0});
    }
}

$(".diak_image_medium").hover(
    function (self) {
        console.log($(this).attr("src"));
        //$(this).css("border","solid 1px gray");
    },function (o) {
        //$(this).css("border","solid 1px lightgray");
        console.log("out");
    }
);