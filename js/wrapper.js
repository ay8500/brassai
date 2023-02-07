var countWrapper=5; //Wrappen in line
var wrapperWidth=700; //Max wrapper width
var wrapperSlideCorrection=30; //This is a correction value of horizontal sliding

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
        var width=Math.round(0.5+w/Math.round(0.7+w/wrapperWidth))-10;
        width = width<460?460:width;
        
        var html='<div style="height:230px;width:'+width+'px" id="wrapper'+aktWrapper +'">';
        html +='<div style="display: inline-block; margin: 0px 10px">';
        html +=     '<a href="editPerson?uid='+d.id+'" title="'+d.name+'">';
        html +=     '<div >';
        html +=         '<img src="images/'+d.image+'" border="0" title="'+d.name+'" class="diak_image_medium">';
        if (d["deceasedYear"]!=null && parseInt(d["deceasedYear"])>=0) {
        	html +='<div style="background-color: black;color: white;hight:20px;text-align: center;border-radius: 0px 0px 10px 10px;position: relative;top: -8px;">';
        	html += parseInt(d["deceasedYear"])==0?"†":"† "+parseInt(d["deceasedYear"]); 
			html +='</div>';
		}
        html +=     '</div>';
        html +='</a></div>';
        html +='<div style="display: inline-block;max-width:250px;vertical-align: top;margin-bottom:10px;">';
        html +='<h4>'+d.name+'</h4>';
        html +='<h6>'+d.schoolName+'</h6>';
        html +='<div class="fields">';
        if (d.classText.indexOf("Tanár")==-1 && d.classText!='') {
            if (d.isGuest==0)
                html +='<div><div>Végzős osztály:</div><div><a href="hometable?classid='+d.classID+'">'+d.classText+'</a></div></div>';
            else
                html +='<div><div>Osztály vendég:</div><div><a href="hometable?classid='+d.classID+'">'+d.classText+'</a></div></div>';
        }
        if (d.schoolIdsAsTeacher) {
            if (d['function']!=null) {
        	    html +='<div><div>Tanár: </div><div>'+d['function']+'</div></div>';
            }
            var schools = d.schoolIdsAsTeacher.split(")",);
            if (schools.length>0) {
                html +='<div><div style="display: inline-block">Iskola:</div><div style="display: inline-block">';
                schools.forEach((schoolId, idx) => {
                    try {
                        var pjson = JSON.parse(d.employer);
                        var period = pjson[schoolId.slice(1)];
                    } catch (err) {
                        var period = d.employer !== undefined ? d.employer : "&nbsp;";
                    }
                    if (period != undefined) {
                        var periodArray = period.split("-");
                        if (periodArray.length == 2) {
                            period = periodArray[0] + "-" + periodArray[1].slice(-2);
                        }
                    } else {
                        period = "";
                    }
                    if (schoolId != "") {
                        if (schoolId === "5")
                            html += '<span><img style="height:20px;" src="images/school' + schoolId.slice(1) + '/logo.png" />' + period + '&nbsp;&nbsp;</span>';
                        else
                            html += '<span><img style="height:20px;" src="images/school' + schoolId.slice(1) + '/logo.jpg" />' + period + '&nbsp;&nbsp;</span>';
                    }
                });
                html +='</div></div>';
            }
        }
        if (d.place!=null)
    		html +='<div><div>Helység:</div><div>'+d.place+'</div></div>';
        if (d.employer!=null && !d.isPersonTeacher)
    		html +='<div><div>Munkahely:</div><div>'+d.employer+'</div></div>';
        html +='<div class="diakCardIcons">';
        
        if(d.email!=null) 
    		html +='<a href='+d.email+'><img src="images/email.png" /></a>';
        if (d.facebook!=null)
    		html +='&nbsp;<a target="_new" href='+d.facebook+'><img src="images/facebook.png" /></a>';
        if (d.twitter!=null)
    		html +='&nbsp;<a target="_new" href='+d.twitter+'><img src="images/twitter.png" /></a>';
        if (d.homepage!=null)
    		html +='&nbsp;<a target="_new" href='+d.homepage+'><img src="images/www.png" /></a>';
        if (d.geolocation===1)
        	html +='&nbsp;<a href=editPerson?tabOpen=geoplace&uid='+d.id+'><img style="width:25px" src="images/geolocation.png" /></a>';
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
	url:"ajax/getRandomPerson?ids="+ids,
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

function hiddenData(title) {
	showModalMessage(title,'Személyes adat védve!<br/>Csak iskola vagy osztálytárs tekintheti meg ezt az informácíót.');
}
