
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

function pictureModal(o,file,id) {
    $("[class*=face]").remove();
    $("#thePicture").attr("data-id",id);
    $("#thePicture").attr("src",file);
    $('#pictureModal').modal({show: 'false' });
    $("#thePicture").on('load',function(){
    });

    return false;
}

function showTagging() {
    var pad=5;
    var img=$("#thePicture");
    $("[class*=face]").remove();

    $("#thePicture").faceDetection({
        complete: function (faces) {
            for (var i = 0; i < faces.length; i++) {
                if (faces[i].confidence>-1.0) {
                    $('<div>', {
                        'class': 'face',
                        'css': {
                            'left': pad + faces[i].x * faces[i].scaleX + 'px',
                            'top': pad + faces[i].y * faces[i].scaleY + 'px',
                            'width': faces[i].width * faces[i].scaleX + 'px',
                            'height': faces[i].height * faces[i].scaleY + 'px'
                        }
                    }).insertAfter(this);
                    $('<div>',{
                        'class': 'facename',
                        'css': {
                            'left': pad + faces[i].x * faces[i].scaleX + 'px',
                            'top': pad + faces[i].y * faces[i].scaleY + faces[i].height * faces[i].scaleY + 'px'
                        }
                    }).insertAfter(this);
                }
            }

        },
        error:function (code, message) {
            alert('Error: ' + message);
        }
    });


    $.ajax({
        url: "ajax/getPicturePersons.php?pictureid="+img.attr("data-id"),
        type:"GET",
        success:function(data){
            data.forEach(function(p){
                $('<div>', {
                    'class': 'face',
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
                    'css': {
                        'left': pad + p.xPos * img.width() + 'px',
                        'top': pad + p.yPos * img.height() + p.size * img.width()+ 'px'
                    }
                }).insertAfter(img);
            });
        },
        error:function(error) {
            console.log.error;
        }
    });


}


function newPerson(o) {
    return false;
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
