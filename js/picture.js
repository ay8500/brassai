
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


function pictureModal(o,file,id) {
    $("#thePicture").attr("data-id",id);
    $("#thePicture").attr("src",file);
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

function showTagging() {
    var pad=5;
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
                        'left': pad + p.xPos * img.width() + 'px',
                        'top': pad + p.yPos * img.height() + 'px',
                        'width': p.size * img.width() + 'px',
                        'height': p.size * img.width() + 'px'
                    }
                }).insertAfter(img);
                $('<div>',{
                    'text': (p.title!=null?p.title+' ':'')+p.lastname+' '+p.firstname,
                    'class': 'facename',
                    'person-id':p.personID,
                    'css': {
                        'left': pad + p.xPos * img.width() + 'px',
                        'top': pad + p.yPos * img.height() + p.size * img.width()+ 'px'
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
    var pad=5;
    var img=$("#thePicture");

    $("#thePicture").faceDetection({
        complete: function (faces) {
            for (var i = 0; i < faces.length; i++) {
                if (faces[i].confidence > -1.0) {
                    $('<div>', {
                        'class': 'recognition',
                        'onclick':'showDetectionList(this,' +faces[i].x+','+faces[i].y+','+faces[i].width+','
                                                            +faces[i].scaleX+','+img.attr("data-id")+','+
                                                            img.get(0).naturalWidth+','+img.get(0).naturalHeight+ ')',
                        'css': {
                            'left': pad + faces[i].x * faces[i].scaleX + 'px',
                            'top': pad + faces[i].y * faces[i].scaleY + 'px',
                            'width': faces[i].width * faces[i].scaleX + 'px',
                            'height': faces[i].height * faces[i].scaleY + 'px'
                        }
                    }).insertAfter(this);
                }
            }

        },
        error: function (code, message) {
            alert('Error: ' + message);
        }
    });

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
                console.log.error;
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
                console.log.error;
            }
        });
    }
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

function showDetectionList(o,x,y,w,s,pictureid,nw,nh) {
    $(".personsearch").remove();
    var px = x/nw;
    var py = y/nh;
    var pw = w/nw;

    html = '<input placeholder="Személy neve" id="personedit" style="width: 194px" onkeyup="searchPerson('+pictureid+','+px+','+py+','+pw+')"/>';
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
            "top":$(o).offset().top+w*s/2+'px',
            "left":$(o).offset().left+(px>0.5?-250+w*s/2:-w*s/2)+'px',
            "padding":"5px"
        },
        'html':html
    }).insertAfter($(o));

    searchPerson(pictureid,x,y,w);
}

function searchPerson(pictureid,x,y,w) {
    $('#persontable').empty();
    $.ajax({
        url: "ajax/getPersonByName.php?name="+$("#personedit").val(),
        type:"GET",
        success:function(data){
            data.forEach(function(row) {
                var pclass=row.scoolYear+' '+row.scoolClass+' ';
                var pname=(row.title!=null?row.title+' ':'')+row.lastname+' '+row.firstname;
                var pimg=(row.picture!=null?'<img src="images/'+row.picture+'" class="diak_image_icon" />':'');
                var html ='<tr style="vertical-align: top"><td>'+pimg+'</td><td>'+pclass+'</td><td>'+pname+'</td><td>';
                html +='<button title="Megjelöl" class="btn-xs btn-success" onclick="savePerson('+row.id+','+pictureid+','+x+','+y+','+w+')"><span class="glyphicon glyphicon-save"></span></button></td></tr>';
                $('#persontable').append(html);
            });
        },
        error:function(error) {
            console.log.error;
        }
    });
}

function savePerson(personid,pictureid,x,y,w) {
    $('.personsearch').remove();
    $.ajax({
        url: "ajax/setPicturePerson.php?pictureid="+pictureid+"&personid="+personid+"&x="+x+"&y="+y+"&w="+w,
        type:"GET",
        success:function(data){
            showTagging();
        },
        error:function(error) {
            console.log.error;
        }
    });
}