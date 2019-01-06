
function savePicture(id) {
    var t = $('#titleEdit_'+id).val();
    var c = $('#commentEdit_'+id).val();
    $('#titleShow_'+id).html(t);
    $('#commentShow_'+id).html(c);
    showWaitMessage();
    if (id>0) {
        $.ajax({
            url:encodeURI("ajax/setPictureTitle.php?id="+id+"&title="+t+"&comment="+c),
            type:"GET",
            dataType: 'json',
            success:function(data){
                if (data.error==null) {
                    clearModalMessage();
                    hideedit(id);
                } else {
                    showModalMessage("Kimentés sikertlen",data.error,"warning");
                }
            },
            error:function() {
                clearModalMessage();
                showDbMessage("Kimentés sikertlen","warning");
            }
        });
    }
    return false;
}

function changeVisibility(id) {
    var c = $('#visibility'+id).prop('checked')?1:0;
    $.ajax({
        url:"ajax/setPictureVisibility.php?id="+id+"&attr="+c,
        type:"GET",
        success:function(data){
            $('#ajaxStatus').html(' Kimetés sikerült. ');
            $('#ajaxStatus').show();
            setTimeout(function(){
                $('#ajaxStatus').html('');
                $('#ajaxStatus').hide();
            }, 2000);
        }
    });
}

function hideedit(id) {
    $("#edit_"+id).hide();
    $("#show_"+id).show();
    return false;
}

function displayedit(id) {
    $("#show_"+id).hide();
    $("#edit_"+id).show();
    return false;
}

var faceSize = 30;
var picturePadding = 5;

function pictureModal(o,file,id) {
    $("#thePicture").attr("data-id",id);
    $("#thePicture").attr("src",file);
    $("#thePicture").css("max-width",$(window).width()-80+"px");
    $("#thePicture").css("max-height",$(window).height()-120+"px");
    $('#pictureModal').modal({show: 'false' });
    $("#thePicture").on('load',function(){
        onPictureLoad();
    });
    return false;
}

function onPictureLoad() {
    if ($("#thePicture").width()==0) {
        setTimeout(onPictureLoad,100);
    } else {
        showTagging();
    }
}

function showTagging(show) {
    var img=$("#thePicture");

    $.ajax({
        url: "ajax/getPicturePersons.php?pictureid="+img.attr("data-id"),
        type:"GET",
        success:function(data){
            $("[class*=recognition]").remove();
            $('[person-id]').remove();
            data.forEach(function(p){
                $('<div>', {
                    'class': 'face',
                    'person-id':p.personID,
                    'onmouseover':"personShow("+p.personID+",true)",
                    'onmouseout':"personShow("+p.personID+",false)",
                    'css': {
                        'left': picturePadding + p.xPos * img.width() + 'px',
                        'top': picturePadding + p.yPos * img.height() + 'px',
                        'width': p.size * img.width() + 'px',
                        'height': p.size * img.width() + 'px',
                        'opacity':(show?'1':'0')
                    }
                }).insertAfter(img);
                $('<div>',{
                    'text': (p.title!=null?p.title+' ':'')+p.lastname+' '+p.firstname,
                    'class': 'facename',
                    'person-id':p.personID,
                    'css': {
                        'left': picturePadding + p.xPos * img.width() + 'px',
                        'top': picturePadding + p.yPos * img.height() + p.size * img.width()+ 'px'
                    }
                }).insertAfter(img);
                var html='<span onmouseover="personShow('+p.personID+',true)"';
                html += ' onmouseout="personShow('+p.personID+',false)" class="personlist" ';
                html += ' style="border-radius:3px" person-id="'+p.personID+'">';
                html += (p.title!=null?p.title+' ':'')+p.lastname+' '+p.firstname;
                html +='&nbsp;<span title="Töröl" class="glyphicon glyphicon-remove-circle" onclick="deletePerson('+p.personID+','+p.pictureID+')"></span></span>';
                $("#personlist").append(html);
            });
        },
        error:function(error) {
            console.log.error;
        }
    });


}

function showFaces() {

    $("#thePicture").faceDetection({
        complete: function (faces) {
            var img=$("#thePicture");
            for (var i = 0; i < faces.length; i++) {
                var w = faces[i].width * faces[i].scaleX;
                if (faces[i].confidence > -1.0) {
                    $('<div>', {
                        'class': 'recognition',
                        'onclick':'$(".newperson").remove();showDetectionList(this,' +(faces[i].x*faces[i].scaleX+w/2)+','+(faces[i].y*faces[i].scaleY+w/2)+','+img.attr("data-id")+','+w+ ')',
                        'css': {
                            'left': picturePadding + faces[i].x * faces[i].scaleX + 'px',
                            'top': picturePadding + faces[i].y * faces[i].scaleY + 'px',
                            'width': faces[i].width * faces[i].scaleX + 'px',
                            'height': faces[i].height * faces[i].scaleY + 'px'
                        }
                    }).insertAfter(img);
                }
            }

        },
        error: function (code, message) {
            alert('Error: ' + message);
        }
    });
    return false;
}



$(function() {
    $("[class*=ibtn]").each(function(){
        $.ajax({
            url: "ajax/getPicturePersons.php?pictureid="+$(this).attr("data-id"),
            type:"GET",
            success:function(data){
                if (data.length>0) {
                    $('#imgspan' + data[0].pictureID).text("Megjelölt személyek a képen:" + data.length);
                    $('#imgspan' + data[0].pictureID).show();
                }
            },
            error:function(error) {
                console.log(error);
            }
        });
    });
});

function deletePerson(personid,pictureid) {
    if (confirm("Személy megjelölést törölni szeretnéd?")) {
        $.ajax({
            url: "ajax/deletePicturePerson.php?pictureid="+pictureid+"&personid="+personid,
            type:"GET",
            success:function(data){
                $('*[person-id='+personid+']').each(function(){
                    $(this).remove();
                });
            },
            error:function(error) {
                console.log(error);
            }
        });
    }
}

function personShowAll(visible) {
    $('*[person-id]').each(function(){
        if ($(this).is('div')) {
            if (visible) {
                $(this).css("opacity", "1");
            } else {
                $(this).css("opacity", "0");
            }
        }
    });
}


function personShow(id,visible) {
    $('*[person-id='+id+']').each(function(){
        if ($(this).is('div')) {
            if (visible) {
                $(this).css("opacity", "1");
            } else {
                $(this).css("opacity", "0");
            }
        } else {
            if (visible) {
                $(this).css("background-color", "lightgray");
            } else {
                $(this).css("background-color", "");
            }
        }
    });
}

function showDetectionList(o,x,y,pictureid,w) {
    $(".personsearch").remove();
    if (w!=null) faceSize=w;

    html = '<input placeholder="Személy neve" id="personedit" style="width: 154px" onkeyup="searchPerson('+pictureid+','+x+','+y+')"/>';
    if (w == null) {
        html += '<button class="btn-xs" title="nagyobb" onclick="setFaceSize(false);" ><span class="glyphicon glyphicon-minus"</button></span></button>';
        html += '<button class="btn-xs" title="kissebb" onclick="setFaceSize(true);" ><span class="glyphicon glyphicon-plus"</button></span></button>';
    }
    html += '<button class="btn-xs" onclick="$(\'.personsearch\').remove();" style="float: right"><span class="glyphicon glyphicon-remove-circle"</button></span></button>';
    html += '<div style="width: 100%;max-height: 200px;overflow-y: scroll;">';
    html += '<table id="persontable" style="width: 100%">';
    html += '</table>';
    html += '</div>';

    $('<div>', {
        'class': 'personsearch',
        'css': {
            "z-index":"199",
            "position":"absolute",
            "top":$(o).position().top+faceSize+'px',
            "left":$(o).position().left+(x>350?-290+faceSize/2+20:-faceSize/2+20)+'px',
            "padding":"5px"
        },
        'html':html
    }).insertAfter($(o));
    searchPerson(pictureid,x,y);
}

function setFaceSize(bigger) {
    var x= parseFloat($('.newperson').css("left"));
    var y= parseFloat($('.newperson').css("top"));
    x +=  faceSize/2;
    y +=  faceSize/2;
    if (bigger) {
        faceSize = faceSize * 1.3;
    } else {
        faceSize = faceSize / 1.3;
    }
    x -=  faceSize/2;
    y -=  faceSize/2;
    $('.newperson').css("width",faceSize+'px');
    $('.newperson').css("height",faceSize+'px');
    $('.newperson').css("left",x+'px');
    $('.newperson').css("top",y+'px');
    $('.personsearch').css("top",y+faceSize+'px');
}

function searchPerson(pictureid,x,y) {
    $('#persontable').empty();
    $("#personedit").focus();
    $.ajax({
        url: "ajax/getPersonByName.php?name="+$("#personedit").val(),
        type:"GET",
        success:function(data){
            if (data!=null && data.length>0) {
                data.forEach(function (row) {
                    if (row.isTeacher==="0") {
                        var pclass = row.scoolYear + ' ' + row.scoolClass + ' ';
                    } else {
                        var pclass = "Tanár"+(row.gender=="f"?"nő":" úr");
                    }
                    var pname = (row.title != null ? row.title + ' ' : '') + row.lastname + ' ' + row.firstname;
                    if (row.picture!=null && row.picture.length>5) {
                        var pimg = '<img src="images/' + row.picture + '" class="diak_image_icon" />';
                    } else {
                        var pimg = '<img src="images/' + (row.gender==="f"?"woman.png":"man.png") + '" class="diak_image_icon" />';
                    }
                    var html = '<tr style="vertical-align: top"><td>' + pimg + '</td><td>' + pclass + '</td><td>' + pname + '</td><td>';
                    html += '<button title="Megjelöl" class="btn-xs btn-success" onclick="savePerson(' + row.id + ',' + pictureid + ',' + x + ',' + y + ')"><span class="glyphicon glyphicon-save"></span></button></td></tr>';
                    $('#persontable').append(html);
                });
            }
        },
        error:function(error) {
            console.log(error);
        }
    });
}

function savePerson(personid,pictureid,x,y) {
    $('.personsearch').remove();
    $('.newperson').remove();
    var img=$("#thePicture");
    $.ajax({
        url: "ajax/setPicturePerson.php?pictureid="+pictureid+"&personid="+personid+"&x="+(x-faceSize/2)/img.width()+"&y="+(y-faceSize/2)/img.height()+"&w="+faceSize/img.width(),
        type:"GET",
        success:function(data){
            showTagging(true);
        },
        error:function(error) {
            console.log(error);
        }
    });
}

function newPerson(event) {
    $('.newperson').remove();
    var img=$("#thePicture");
    var s = img.width()/img.get(0).naturalWidth;
    var n=$('<div>', {
        'class': 'newperson',
        'css': {
            'left': picturePadding+event.offsetX-faceSize/2+ 'px',
            'top': picturePadding+event.offsetY-faceSize/2 + 'px',
            'width': faceSize + 'px',
            'height': faceSize + 'px'
        }
    }).insertAfter(img);
    showDetectionList(n,event.offsetX,event.offsetY,img.attr("data-id"),null);
}