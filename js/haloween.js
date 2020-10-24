var flyingObjects = (function(window, document) {

    $("#mainmenucontainer").css("background-image","url('images/fire.gif')");
    $("#mainmenucontainer").css("background-size","cover");

    var img='images/ghost.png';

    var element = document.createElement("div");

    element.innerHTML='<img src="../images/haloween.png" />';

    document.body.appendChild(element);

}(window, document));