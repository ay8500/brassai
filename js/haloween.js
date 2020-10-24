var flyingObjects = (function(window, document) {

    var img='images/ghost.png';

    var element = document.createElement("div");
    var ghost = element.createElement("img");

    element.innerHTML='<img src="../images/haloween.png" />';

    document.body.appendChild(element);

    alert('ok');

});