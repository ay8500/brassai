
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
    $("#thePicture").attr("src",file);
    $("#thePicture").on('load',function(){
        $("#thePicture").faceDetection({
            complete: function (faces) {
                for (var i = 0; i < faces.length; i++) {
                    $('<div>', {
                        'class':'face',
                        'css': {
                            'position': 'absolute',
                            'left':     faces[i].x * faces[i].scaleX + 'px',
                            'top':      faces[i].y * faces[i].scaleY + 'px',
                            'width':    faces[i].width  * faces[i].scaleX + 'px',
                            'height':   faces[i].height * faces[i].scaleY + 'px'
                        }
                    })
                        .insertAfter(this);
                }
            },
            error:function (code, message) {
                alert('Error: ' + message);
            }
        });
        });
    $('#pictureModal').modal({show: 'false' });
    return false;
}

function startTagging(o,id) {
    $(o).parent().parent().css("max-width","none");
    var div = $($(o).siblings('div')[0]);
    $(div.children()[0]).hide();$(div.children()[1]).hide();
    $(div.children()[2]).show();$(div.children()[3]).show();

    $(o).faceDetection({
        complete: function (faces) {
            for (var i = 0; i < faces.length; i++) {
                $('<div>', {
                    'class':'face',
                    'css': {
                        'position': 'absolute',
                        'left':     faces[i].x * faces[i].scaleX + 'px',
                        'top':      faces[i].y * faces[i].scaleY + 'px',
                        'width':    faces[i].width  * faces[i].scaleX + 'px',
                        'height':   faces[i].height * faces[i].scaleY + 'px'
                    }
                })
                    .insertAfter(this);
            }
        },
        error:function (code, message) {
            alert('Error: ' + message);
        }
    });

    return false;
}

function stopTagging(o) {
    $(o).parent().parent().css("max-width","395px");
    var div = $($(o).siblings('div')[0]);
    $(div.children()[2]).hide();$(div.children()[3]).hide();
    $(div.children()[0]).show();$(div.children()[1]).show();
    $("[class*=face]").remove();
    return false;
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
