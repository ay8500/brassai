$( document ).ready(function() {
    $("#wrapper").append('<div id="wrapper_frame"></div>');
    getWrapperData(); 
    initWrapper();
    setInterval(slide, 5000);
});

$( window ).resize(function() {
    initWrapper();
});

var countWrapper=4;
var aktWrapper=1;
var data = new Array();

function initWrapper() {
    aktWrapper=1;
    $("#wrapper_frame").empty();

    for (var i=1;i<=countWrapper;i++){
	addWrapperDiv(i);
    }
}

function addWrapperDiv(id) {
    if (data.length==countWrapper) {
	var d = data[id-1];
	var idx= d.id.split("-"); 
	if (idx.length==3) {
            var w=$("#wrapper").width();
            var width=Math.round(-0.5+w/Math.round(0.5+w/600));
            
            var html='<div style="width:'+width+'px" id="wrapper'+aktWrapper +'">';
            html +='<div style="display: inline-block; width:160px;">';
            html +='<a href="editDiak.php?uid='+idx[2]+'&amp;scoolYear='+idx[1]+'&amp;scoolClass='+idx[0]+'" title="'+d.name+'">';
            html +='<img src="images/'+data[id-1].image+'" border="0" title="'+d.name+'" class="diak_image_medium ">';
            html +='</a>';
            html +='</div>';
            html +='<div style="display: inline-block;max-width:56%;vertical-align: top;margin-bottom:10px;">';
            html +='<h4>';
            html +=d.name;
            html +='</h4>';
            html +='<div class="fields">'; 
            if (idx[1]=="teac") {
        	html +='<div><span>Tanár:</span>'+d['function']+'</div>';
        	if (d.children!=null) {
        	    html +='<div><span>Osztályfőnök:</span>';
        	    var kx= d.children.split(",");
        	    for (var k=0;k<kx.length;k++) {
        		html +='<a href="hometable.php?scoolYear='+kx[k].substring(3,7)+'&scoolClass='+kx[k].substring(0,3)+'">'+kx[k]+'</a>&nbsp;';
        	    }
        	    html +='</div>';
        	}
            } else {
        	html +='<div><span>Végzős osztály:</span><a href="hometable.php?scoolYear='+idx[1]+'&scoolClass='+idx[0]+'">'+idx[0]+'-'+idx[1]+'</a></div>';
            }
            if (d.place!=null)
        	html +='<div><span>Helyiség:</span><br/>&nbsp;&nbsp;&nbsp;'+d.place+'</div>';
            if (d.employer!=null)
        	html +='<div><span>Munkahely:</span><br/>&nbsp;&nbsp;&nbsp;'+d.employer+'</div>';
            html +='<div style="margin-top:5px">';
            if(d.email!=null)
        	html +='<a href="mailto:'+d.email+'"><img src="images/email.png" /></a>';
            if (d.facebook!=null)
        	html +='&nbsp;<a target="_new" href='+d.facebook+'><img src="images/facebook.png" /></a>';
            if (d.twitter!=null)
        	html +='&nbsp;<a target="_new" href='+d.twitter+'><img src="images/twitter.png" /></a>';
            if (d.homepage!=null)
        	html +='&nbsp;<a target="_new" href='+d.homepage+'><img src="images/www.png" /></a>';
            html +='</div>';

            html +='</div>';
            html +='</div>';
            $("#wrapper_frame").append(html);
            aktWrapper++;
	}
    }
}

function slide() {
    var ws='#wrapper'+(aktWrapper-countWrapper);
    $(ws).animate(
	{"marginLeft" : "-="+($(ws).width()+25)+"px" ,opacity: 0.25}, 
	1500,
        function() {
            getWrapperData();
            addWrapperDiv(countWrapper);
            $(ws).remove();
        });

}

function getWrapperData() {
    if (data.length==countWrapper)
	data.shift();
    var ids="";
    for (var i=0;i<data.length;i++) {
	if (i!=0)
	    ids +=",";
	ids +=data[i].id;
    }
    $.ajax({
	url:"getRandomPerson.php?ids="+ids,
 	type:"GET",
 	async:true,
 	success:function(person){
 	    data.push(person);
 	    initWrapper();
 	    if (data.length<countWrapper)
 		getWrapperData();
 	}
     });

}
