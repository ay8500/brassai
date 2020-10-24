var flyingObjects = (function(window, document) {

    this.animationInterval = 30;
    this.movingObjects = [];
    this.movingObjects[0] = [];
    this.movingObjects[0].url = "images/haloweenwitch.png";
    this.movingObjects[0].xdir = 5.7;
    this.movingObjects[0].width = 50;
    this.movingObjects[0].startY = 50
    this.movingObjects[0].element = document.createElement("div");

    this.movingObjects[1] = [];
    this.movingObjects[1].url = "images/haloweenghost.png";
    this.movingObjects[1].xdir = -5.9;
    this.movingObjects[1].width = 50;
    this.movingObjects[1].startY = 250
    this.movingObjects[1].element = document.createElement("div");

//****************************************************************************************************
    $("#mainmenucontainer").css("background-image","url('images/fire.gif')");
    $("#mainmenucontainer").css("background-size","cover");

    this.moveObject = function(element,x,y,ydir,xdir) {
        var s = document.documentElement.scrollTop || document.body.scrollTop;
        var ydirection = ydir;
        var random = Math.random();
        if (random>0.99)
            ydirection *= -1;
        var yy = y + ydirection;
        if (yy<element.width)
            yy=element.width;
        if (yy>window.screen.height+element.width)
            yy=window.screen.height;
        var xx = x + xdir;
        if (xx>window.screen.availWidth-element.width) {
            if (random < 0.001) {
                $(element.element).show();
                xx = 0;
            } else {
                $(element.element).hide();
            }
        }
        if (xx<0) {
            if (random < 0.001) {
                $(element.element).show();
                xx = window.screen.availWidth - element.width;
            } else {
                $(element.element).hide();
            }
        }
        $(element.element).css("top",s+yy+"px");
        $(element.element).css("left",xx+"px");
        setTimeout(this.moveObject,this.animationInterval, element, xx, yy,ydirection,xdir);
    };

    this.movingObjects.forEach(function(item, index){
        $(item.element).css("position","absolute");
        $(item.element).css("top","55px");
        $(item.element).css("left","-45px");
        item.element.innerHTML='<img style="width: '+item.width+'px;" src="'+item.url+'" />';
        document.body.appendChild(item.element);
        this.moveObject(item,-item.width,item.startY,1,item.xdir);
    });

}(window, document));

