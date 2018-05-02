var countWrapper=5;		//Wrappen in line
var wrapperWidth=700;		//Max wrapper width
var wrapperSlideCorrection=30;	//This is a correction value of horizontal sliding

var aktWrapper=0;
var animate=false;
var resizeToDo=false;
var data = new Array();

$( document ).ready(function() {
    $("#wrapper").append('<div id="wrapper_frame"></div>');
    getWrapperData(); 
    setInterval(slide, 5000);
});

$( window ).resize(function() {
    if (!animate) {
	resizeWrapper();	//risize only if the wrapper not sliding
    } else {
	resizeToDo=true;	//while slide animation don't resize the wrapper
    }
});

function resizeWrapper() {
    aktWrapper=1;
    $("#wrapper_frame").empty();	//delete all wrapper divs

    for (var i=0;i<countWrapper;i++){
	addWrapperDiv(i);		//create them with the new size
    }
    resizeToDo=false;
}

function addWrapperDiv(id) {
    if (data.length>=id+1) {
	var d = data[id];

	var w=$("#wrapper").width();
        var width=Math.round(0.5+w/Math.round(0.5+w/wrapperWidth));
        
        var html='<div style="height:230px;width:'+width+'px" id="wrapper'+aktWrapper +'">';
        html +='<div style="display: inline-block; margin: 0px 10px">';
        html +='<a href="editDiak.php?uid='+d.id+'" title="'+d.name+'">';
        html +='<div >';
        html +='<img src="images/'+d.image+'" border="0" title="'+d.name+'" class="diak_image_medium">';
        if (d["deceasedYear"]!=null && parseInt(d["deceasedYear"])>=0) {
        	html +='<div style="background-color: black;color: white;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">';
        	html += parseInt(d["deceasedYear"])==0?"†":"† "+parseInt(d["deceasedYear"]); 
			html +='</div>';
		}
        html +='</div>';
        html +='</a></div>';
        html +='<div style="display: inline-block;max-width:50%;vertical-align: top;margin-bottom:10px;">';
        html +='<h4>'+d.name+'</h4>';
        html +='<div class="fields">'; 
        if (d.classID=="0") {
            if (d['function']!=null) {
        	html +='<div><div>Tanár:</div><div>'+d['function']+'</div></div>';
            }
            if (d.children!=null) {
    	    	html +='<div><div>Osztályfőnök:</div><div>';
    	    	var kx= d.children.split(",");
    	    	for (var k=0;k<kx.length;k++) {
    	    	    if (k!=0) html+=',';
    	    	    html +='<a href="hometable.php?classid='+kx[k]+'">'+kx[k]+'</a> ';
    	    	}
    	    	html +='</div></div>';
            }
        } else {
            if (d.isGuest==0)
        	html +='<div><div>Osztály:</div><div><a href="hometable.php?classid='+d.classID+'">'+d.classText+'</a></div></div>';
            else
        	html +='<div><div>Osztály:</div><div><a href="hometable.php?classid='+d.classID+'">'+d.classText+'</a></div></div>';
        }
        if (d.place!=null)
    		html +='<div><div>Helyiség:</div><div>'+d.place+'</div></div>';
        if (d.employer!=null)
    		html +='<div><div>Munkahely:</div><div>'+d.employer+'</div></div>';
        html +='<div class="diakCardIcons">';
        if(d.email!=null)
    		html +='<a href="mailto:'+d.email+'"><img src="images/email.png" /></a>';
        if (d.facebook!=null)
    		html +='&nbsp;<a target="_new" href='+d.facebook+'><img src="images/facebook.png" /></a>';
        if (d.twitter!=null)
    		html +='&nbsp;<a target="_new" href='+d.twitter+'><img src="images/twitter.png" /></a>';
        if (d.homepage!=null)
    		html +='&nbsp;<a target="_new" href='+d.homepage+'><img src="images/www.png" /></a>';
        if (d.geolocation===1)
        	html +='&nbsp;<a href=editDiak.php?tabOpen=5&uid='+d.id+'><img style="width:25px" src="images/geolocation.png" /></a>';
        html +='</div>';

        html +='</div>';
        html +='</div>';
        $("#wrapper_frame").append(html);
        aktWrapper++;
    }
}

function slide() {
    var ws='#wrapper'+(aktWrapper-countWrapper);
    animate=true;
    $(ws).animate(
	{"marginLeft" : "-="+($(ws).width()+wrapperSlideCorrection)+"px" ,opacity: 0.15}, 
    	1500,
        function() {
	    getWrapperData();
            $(ws).remove();
            animate=false;
            if (resizeToDo) {
        	resizeWrapper();
            }
        }
    );
}

function getWrapperData() {
    //take care that the first element is removed if the max count of wrapper element are reached
    if (data.length==countWrapper)
	data.shift();
    //collect the id from the list to retrieve a different one
    var ids="";
    for (var i=0;i<data.length;i++) {
	if (i!=0)
	    ids +=",";
	ids +=data[i].id;
    }
    //get new data
    $.ajax({
	url:"getRandomPerson.php?ids="+ids,
 	type:"GET",
 	async:true,
 	success:function(person){
 	    data.push(person);
 	    addWrapperDiv(data.length-1);
 	    //if the max count not reached call the function recursive
 	    if (data.length<countWrapper)
 		getWrapperData();
 	}
     });

}