
function savePicture(id) {
    var t = $('#titleEdit_'+id).val();
    var c = $('#commentEdit_'+id).val();
    $('#titleShow_'+id).html(t);
    $('#commentShow_'+id).html(c);
    showWaitMessage();
    if (id>0) {
        $.ajax({
            url:encodeURI("ajax/setPictureTitle?id="+id+"&title="+t+"&comment="+c+"&tag="+ $('#tagEdit_'+id).val()),
            type:"GET",
            dataType: 'json',
            success:function(data){
                if (data.error==null) {
                    clearModalMessage();
                    hideedit(id);
                } else {
                    showModalMessage('<?php maierlabs\lpfw\Appl::_("Kimentés sikertlen")?>',"warning");
                }
            },
            error:function() {
                clearModalMessage();
                showDbMessage('<?php maierlabs\lpfw\Appl::_("Kimentés sikertlen")?>',"warning");
            }
        });
    }
    return false;
}

function changeVisibility(id) {
    var c = $('#visibility'+id).prop('checked')?1:0;
    $.ajax({
        url:"ajax/setPictureVisibility?id="+id+"&attr="+c,
        type:"GET",
        success:function(data){
            showDbMessage('<?php maierlabs\lpfw\Appl::_("Kép láthatósága sikeresen kimentve")?>',"success");
        },
        error:function(data){
            showDbMessage('<?php maierlabs\lpfw\Appl::_("Kimentés sikertlen")?>',"waring");
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
var pictureFile;
var isShowFaceRecognition=true;

function pictureModal(file,id) {
    pictureFile = file;
    getNextPicture(false, id)!=null?$("#prevpicture").show():$("#prevpicture").hide();
    getNextPicture(true, id)!=null?$("#nextpicture").show():$("#nextpicture").hide();
    $("#thePicture").hide();
    $("#thePicture").attr("src","images/loading.gif");
    $("[class*=recognition]").remove();$("[class*=face]").remove(); $("[class*=facename]").remove();
    $("[class*=personlist]").remove();$("[id*=personlist]").empty();
    $("#thePicture").attr("data-id",id);
    //$("#thePicture").css("max-width",$(window).width()-80+"px");
    $("#thePicture").css("max-height",$(window).height()-120+"px");
    $("#thePicture").on('load',function(){
        onPictureLoad();
    });
    $("#thePictureFaceRecognition").on('load',function(){
        onPictureFaceRecognitionLoad();
    });
    $("#thePicture").attr("src","imageConvert?width=1900&id="+id);
    $('#pictureModal').modal();
    return false;
}

function onPictureLoad() {
    if ($("#thePicture").width()==0) {
        setTimeout(onPictureLoad,100);
    } else {
        //$("#thePictureDiv").width($("#thePicture").width());
        $("#thePicture").show();
        showTagging(false);
        $("#thePictureFaceRecognition").attr("src",pictureFile);
    }
}

function onPictureFaceRecognitionLoad() {
    if ($("#thePictureFaceRecognition").width()==0) {
        setTimeout(onPictureLoad,100);
    } else {
        showFaceRecognition(isShowFaceRecognition);
    }
}

function showTagging(show) {
    var img=$("#thePicture");
    if (img.attr("data-id")!==null) {
        $.ajax({
            url: "ajax/getPicturePersons?pictureid=" + img.attr("data-id"),
            type: "GET",
            success: function (data) {
                $('[person-id]').remove();
                $("[id*=personlist]").empty();
                if (data.title != null) {
                    $("#personlist").append('<div><b>' + data.title + ' </b> ' + (data.comment == null ? '' : data.comment) + (data.tag == null?'':' Tartalom:'+data.tag) +'</div>');
                }
                data.face.forEach(function (p) {
                    $('<div>', {
                        'class': 'face',
                        'person-id': p.personID,
                        'onmouseover': "personShow(" + p.personID + ",true)",
                        'onmouseout': "personShow(" + p.personID + ",false)",
                        'onclick': "personModify(this," + p.personID + "," + p.pictureID + ")",
                        'css': {
                            'left': img.position().left + p.xPos * img.width() + 'px',
                            'top': img.position().top + +p.yPos * img.height() + 'px',
                            'width': p.size * img.width() + 'px',
                            'height': p.size * img.width() + 'px',
                            'opacity': (show ? '1' : '0')
                        }
                    }).insertAfter(img);
                    $('<div>', {
                        'text': (p.title != null ? p.title + ' ' : '') + p.lastname + ' ' + p.firstname,
                        'class': 'facename',
                        'person-id': p.personID,
                        'css': {
                            'left': img.position().left + +p.xPos * img.width() + 'px',
                            'top': img.position().top + +p.yPos * img.height() + p.size * img.width() + 'px'
                        }
                    }).insertAfter(img);
                    var html = '';
                    html += '<span onmouseover="personShow(' + p.personID + ',true)"';
                    html += ' onmouseout="personShow(' + p.personID + ',false)" class="personlist" ';
                    html += ' style="border-radius:3px" person-id="' + p.personID + '">';
                    html += '<a href="editDiak?uid=' + p.personID + '">' + (p.title != null ? p.title + ' ' : '') + p.lastname + ' ' + p.firstname + "</a>";
                    html += '&nbsp;<span title="Töröl" class="glyphicon glyphicon-remove-circle" onclick="deletePerson(' + p.personID + ',' + p.pictureID + ')"></span>';
                    html += '</span>';
                    $("#personlist").append(html);
                });
            },
            error: function (error) {
                console.log.error;
            }
        });
    }
}

function personModify(o,personid,pictureid) {
    closeNewModify();
    o = $(o);
    faceSize=parseInt(o.css("width"));
    x=o.position().left+faceSize;
    var img=$("#thePicture");
    var s = img.width()/img.get(0).naturalWidth;

    var html = '<h4>Jelölés modosítása</h4>Plus, minus illetve nyílgombokat lehet is használni.';
    html += '<button class="btn-xs" onclick="closeNewModify();" style="float: right"><span class="glyphicon glyphicon-remove-circle"></span></button>';
    html += '<hr/><div>';
    html += getButtonHtml();
    html += '</div>';
    html += '<hr/><div>';
    html += '<button class="btn btn-success" onclick="deletePerson('+personid+','+pictureid+',false,true);" ><span class="glyphicon glyphicon-save"</button></span> Kiment</button>';
    html += ' <button class="btn btn-warning" onclick="closeNewModify()" ><span class="glyphicon glyphicon-remove-circle"></span> Mégse</button>';
    html += '</div>';

    var n=$('<div>', {
        'class': 'newperson',
        'css': {
            'left': o.position().left+ 'px',
            'top':  o.position().top+ 'px',
            'width': faceSize + 'px',
            'height': faceSize + 'px',
            'html':html
        }
    }).insertAfter(img);

    $('<div>', {
        'class': 'personsearch',
        'css': {
            "top":o.position().top+faceSize+'px',
            "left":o.position().left+(x>350?-290+faceSize/2+20:-faceSize/2+20)+'px',
        },
        'html':html
    }).insertAfter(o);
}

function getButtonHtml() {
    var text = ('<?php maierlabs\lpfw\Appl::_("kissebb,nagyobb,balra,feljebb,lejjebb,jobbra")?>').split(',');
    var html = '<button class="btn-xs" title="'+text[0]+'" onclick="setFaceSize(false);" ><span class="glyphicon glyphicon-minus-sign"></span></button>';
    html += '<button class="btn-xs" title="'+text[1]+'" onclick="setFaceSize(true);" ><span class="glyphicon glyphicon-plus-sign"></span></button>';
    html += '&nbsp;&nbsp;';
    html += '<button class="btn-xs" title="'+text[2]+'" onclick="setPos(false,null);" ><span class="glyphicon glyphicon-arrow-left"></span></button>';
    html += '<button class="btn-xs" title="'+text[3]+'" onclick="setPos(null,true);" ><span class="glyphicon glyphicon-arrow-up"></span></button>';
    html += '<button class="btn-xs" title="'+text[4]+'" onclick="setPos(null,false);" ><span class="glyphicon glyphicon-arrow-down"></span></button>';
    html += '<button class="btn-xs" title="'+text[5]+'" onclick="setPos(true,null);" ><span class="glyphicon glyphicon-arrow-right"></span></button>';
    return html;
}

function toggleFaceRecognition(recognition) {
    if (recognition==null)
        isShowFaceRecognition=!isShowFaceRecognition;
    else
        isShowFaceRecognition=recognition;
    if (isShowFaceRecognition)
        $("#facebutton").css("background-color","white");
    else
        $("#facebutton").css("background-color","darkgray");
    showFaceRecognition();
}

function showFaceRecognition(force) {
    if (isShowFaceRecognition!==true) {
        $("[class*=recognition]").remove();
        return;
    }
    if ($("#thePicture").width()>0) {
        $("#thePictureFaceRecognition").faceDetection({
            complete: function (faces) {
                var img = $("#thePicture");
                var screenScale = $("#thePicture").width() / $("#thePictureFaceRecognition").width();
                for (var i = 0; i < faces.length; i++) {
                    var w = faces[i].width * faces[i].scaleX * screenScale;
                    if (faces[i].confidence > -1.0) {
                        $('<div>', {
                            'class': 'recognition',
                            'onclick': '$(".newperson").remove();showDetectionList(this,' + (faces[i].x * faces[i].scaleX * screenScale + w / 2) + ',' + (faces[i].y * faces[i].scaleY * screenScale + w / 2) + ',' + img.attr("data-id") + ',' + w + ')',
                            'css': {
                                'left': img.position().left + faces[i].x * faces[i].scaleX * screenScale + 'px',
                                'top': img.position().top + faces[i].y * faces[i].scaleY * screenScale + 'px',
                                'width': faces[i].width * faces[i].scaleX * screenScale + 'px',
                                'height': faces[i].height * faces[i].scaleY * screenScale + 'px'
                            }
                        }).insertAfter(img);
                    }
                }
                $("#facebutton").css("background-color", "coral");
            },
            error: function (code, message) {
                console.log(message);
            }
        });
    }
    return false;
}


$(function() {
    $("[class*=recognition]").remove();
    closeNewModify();

    $("[class*=ibtn]").each(function(){
        if($(this).attr("data-id")!==null) {
            $.ajax({
                url: "ajax/getPicturePersons?pictureid=" + $(this).attr("data-id"),
                type: "GET",
                success: function (data) {
                    if (data.face.length > 0) {
                        $('#imgspan' + data.face[0].pictureID).text("Megjelölt személyek a képen:" + data.face.length);
                        $('#imgspan' + data.face[0].pictureID).show();
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    });

    $(document).keydown(function(e){
        var event = window.event ? window.event : e;
        var key = e.which ? e.which : e.keyCode;
        if ($('.newperson').length>0 || $('.personsearch').length>0 ) {
            if (key == 107) setFaceSize(true);
            if (key == 109) setFaceSize(false);
            if (key == 37) setPos(false, null);//left
            if (key == 38) setPos(null, true); //up
            if (key == 39) setPos(true, null); //right
            if (key == 40) setPos(null, false);//down
        } else {
            if (key == 37) slideToNextPicture(false);//left
            if (key == 39) slideToNextPicture(true); //right
        }
    });

    $( window ).resize(function() {
        toggleFaceRecognition(false);
        showTagging();
    });

    toggleFaceRecognition(null===getUrlVar("id"));

});

function deletePerson(personid,pictureid,verbose,savenewposition) {
    if (verbose==null) verbose = true;
    if (savenewposition==null) savenewposition= false;
    if (!verbose || confirm('<?php maierlabs\lpfw\Appl::_("Személy megjelölést törölni szeretnéd?")?>')) {
        $.ajax({
            url: "ajax/deletePicturePerson?pictureid="+pictureid+"&personid="+personid,
            type:"GET",
            success:function(data){
                $('*[person-id='+personid+']').each(function(){
                    $(this).remove();
                });
                if (!verbose)
                    clearModalMessage();
                if (savenewposition) {
                    savePerson(personid, pictureid);
                }
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

    html = '<input placeholder="<?php maierlabs\lpfw\Appl::_("Személy neve")?>" id="personedit" style="width: 100%" onkeyup="searchPerson('+pictureid+','+x+','+y+')"/>';
    if (w == null) {
        html += '<br/>'+getButtonHtml();
    }
    html += '<button class="btn-xs" onclick="closeNewModify();" style="float: right"><span class="glyphicon glyphicon-remove-circle"</button></span></button>';
    html += '<div style="width: 100%;max-height: 200px;overflow-y: scroll;">';
    html += '<table id="persontable" style="width: 100%">';
    html += '</table>';
    html += '</div>';

    $('<div>', {
        'class': 'personsearch',
        'css': {
            "z-index":"600",
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

function setPos(hor,vert) {
    var x= parseFloat($('.newperson').css("left"));
    var y= parseFloat($('.newperson').css("top"));
    if (hor!=null && hor) x++;
    if (hor!=null && !hor) x--;
    if (vert!=null && vert) y--;
    if (vert!=null && !vert) y++;
    $('.newperson').css("left",x+'px');
    $('.newperson').css("top",y+'px');
    $('.personsearch').css("top",y+faceSize+'px');
}

function searchPerson(pictureid,x,y) {
    $('#persontable').empty();
    $("#personedit").focus();
    $.ajax({
        url: "ajax/getPersonByName?name="+$("#personedit").val(),
        type:"GET",
        success:function(data){
            if (data!=null && data.length>0) {
                data.forEach(function (row) {
                    if (row.schoolIdsAsTeacher===null) {
                        var pclass = row.schoolYear + ' ' + row.schoolClass + ' ';
                    } else {
                        if (row.gender=="f") {
                            var pclass = "<?php maierlabs\lpfw\Appl::_('Tanárnő')?>";
                        } else {
                            var pclass = "<?php maierlabs\lpfw\Appl::_('Tanár úr')?>";
                        }
                    }
                    var pname = (row.title != null ? row.title + ' ' : '') + row.lastname + ' ' + row.firstname;
                    if (row.picture!=null && row.picture.length>5) {
                        var pimg = '<img src="images/' + row.picture + '" class="diak_image_icon" />';
                    } else {
                        var pimg = '<img src="images/' + (row.gender==="f"?"woman.png":"man.png") + '" class="diak_image_icon" />';
                    }
                    var html = '<tr style="vertical-align: top"><td>' + pimg + '</td><td>' + pclass + '</td><td>' + pname + '</td><td>';
                    html += '<button title="<?php maierlabs\lpfw\Appl::_("Megjelöl")?>" class="btn-xs btn-success" onclick="savePerson(' + row.id + ',' + pictureid + ',' + x + ',' + y + ')"><span class="glyphicon glyphicon-save"></span></button></td></tr>';
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
    if (y == null) {
        x=$('.newperson').position().left+faceSize/2-picturePadding;
        y=$('.newperson').position().top+faceSize/2-picturePadding;
    }
    closeNewModify();
    var img=$("#thePicture");
    $.ajax({
        url: "ajax/setPicturePerson?pictureid="+pictureid+"&personid="+personid+"&x="+(x-faceSize/2)/img.width()+"&y="+(y-faceSize/2)/img.height()+"&w="+faceSize/img.width(),
        type:"GET",
        success:function(data){
            showTagging(true);
            showConfirmMessage(
                "<?php maierlabs\lpfw\Appl::_('Személy megjelölése sikerült')?>",
                "<?php maierlabs\lpfw\Appl::_('Akkor perfekt e személy megjelölése:<ul><li>ha a képen a személy szemei, orra és szája látszik</li><li>a személy naka és teljes frizurája nem fontos a jelöléshez</li><li>ha más személyek teljes arca nincs a megjelölt mezőben</li></ul>Ha nem sikerült, akkor kérünk törölj, és probáld meg újból.<br/>Köszönjük szépen.')?>",
                "imageTaggedPerson?pictureid="+pictureid+"&personid="+personid+"&size=100&padding=20",
                pictureid,personid
            )
        },
        error:function(error) {
            console.log(error);
        }
    });
}

function slideToNextPicture(direction) {
    toggleFaceRecognition(false);
    picture = getNextPicture(direction, $("#thePicture").attr("data-id"));
    if (picture==null)
        return false;
    var img =$("#thePicture");
    var id = picture.id;
    var file=picture.file;
    $("#prevpicture").hide();
    $("#nextpicture").hide();

    $("#thePicture").off('load');
    $("#thePicture").attr("data-id",id);
    $("[class*=recognition]").remove();$("[class*=face]").remove(); $("[class*=facename]").remove();
    $("[class*=personlist]").remove();$("[id*=personlist]").empty();

    if (direction)
        img.animate(
            {marginLeft: "+="+($(".modal-content").width()+picturePadding)+"px"},
            {
                complete:  function() {
                    $("#thePicture").on('load',function(){
                        img.css("marginLeft",(-img.width()-picturePadding)+"px");
                        //$("#thePictureDiv").width($("#thePicture").width());
                        img.animate(
                            {marginLeft: "+="+(img.width()+picturePadding)+"px"},
                            {
                                complete: function () {
                                    getNextPicture(false, id)!=null?$("#prevpicture").show():$("#prevpicture").hide();
                                    getNextPicture(true, id)!=null?$("#nextpicture").show():$("#nextpicture").hide();
                                    showTagging(false);
                                    $("#thePictureFaceRecognition").attr("src",file);
                                }
                            }
                        );

                    });
                    img.attr("src","imageConvert?width=1200&id="+id);
                }
            }
        );
    else
        img.animate(
            {marginLeft: "-="+(img.width()+picturePadding)+"px"},
            {
                complete:  function() {
                    $("#thePicture").on('load',function() {
                        img.css("marginLeft", ($(".modal-content").width() + picturePadding) + "px");
                        //$("#thePictureDiv").width($("#thePicture").width());
                        img.animate(
                            {marginLeft: "-=" + ($(".modal-content").width() + picturePadding) + "px"},
                            {
                                complete: function () {
                                    getNextPicture(false, id)!=null?$("#prevpicture").show():$("#prevpicture").hide();
                                    getNextPicture(true, id)!=null?$("#nextpicture").show():$("#nextpicture").hide();
                                    showTagging(false);
                                    $("#thePictureFaceRecognition").attr("src",file);
                                }
                            }
                        );
                    });
                    img.attr("src","imageConvert?width=1200&id="+id);
                }
            }
        );
    return false;
}

function showConfirmMessage(title,text,picture,pictureid,personid) {
    $(".modal-title").html(title);
    $(".modal-body").html('<div>'+text+'<div style="margin-top:15px;width: 100%;text-align: center"><img style="border-radius: 80px;box-shadow: 3px 2px 20px 4px black;" src="'+picture+'" /></div></div>' );
    $(".modal-footer").html(
        '<button class="btn btn-danger" onclick="deletePerson('+personid+','+pictureid+',false);">Kitörlöm</button>' +
        '<button class="btn btn-success" onclick="clearModalMessage();">Rendben meghagyom</button>'
    );
    $('#myModal').modal({ show: 'true'});
}

function getNextPicture(direction,id) {
    var before=null;
    var after =null;
    var foundId=false;
    for(var i=0;i<pictures.length;i++){
        var picture=pictures[i];
        if (foundId) {
            after=picture;
            if (direction===true) {
                return after;
            }
        }
        if (picture.id === parseInt(id)) {
            foundId=true;
            if (direction===false) {
                return before;
            }
        }
        before=picture;
    }
}

function imageCss() {
    var brightness = $("#slider_brightness").slider("value");
    var contrast = $("#slider_contrast").slider("value");
    var rotate = $("#slider_hue_rotate").slider("value");
    var saturate = $("#slider_saturate").slider("value")/10;
    $("#thePicture").css("-webkit-filter",
        "brightness(" + brightness + "%)" +
        "hue-rotate(" + rotate + "deg)" +
        "contrast(" + contrast + "%)"  +
        "saturate(" + saturate + ")"
    );
}

function resetImageCss() {
    $("#slider_brightness").slider("option","value",100);
    $("#slider_contrast").slider("option","value",100);
    $("#slider_hue_rotate").slider("option","value",0);
    $("#slider_saturate").slider("option","value",10);
    imageCss();
}

function showImageSettings() {
    $(".modal-title").html("Kép beállítások");
    $(".modal-body").html('<div style="margin-top:15px;width: 100%;text-align: center">' +
        '<span class="slider_text">Fényerő:</span> <div id="slider_brightness"></div><br/>' +
        '<span class="slider_text">Kontraszt:</span> <div id="slider_contrast"></div><br/>' +
        '<span class="slider_text">Szín:</span> <div id="slider_hue_rotate"></div><br/>' +
        '<span class="slider_text">Színerő:</span> <div id="slider_saturate"></div><br/>' +
        '</div>' );
    $(".modal-footer").html(
        '<button class="btn btn-danger" onclick="resetImageCss();">Visszaállít</button>' +
        '<button class="btn btn-success" onclick="clearModalMessage();">Rendben meghagyom</button>'
    );
    $( "#slider_brightness" ).slider({min:40,max:300,value:100, slide:imageCss, change:imageCss });
    $( "#slider_contrast" ).slider({min:0,max:300,value:100, slide:imageCss, change:imageCss });
    $( "#slider_hue_rotate" ).slider({min:-30,max:30,value:0, slide:imageCss, change:imageCss });
    $( "#slider_saturate" ).slider({min:0,max:20,value:10, slide:imageCss, change:imageCss });
    $('#myModal').modal({ show: 'true'});
}

function newPerson(event) {
    $('.newperson').remove();
    var img=$("#thePicture");
    var s = img.width()/img.get(0).naturalWidth;
    var n=$('<div>', {
        'class': 'newperson',
        'css': {
            'left': img.position().left+event.offsetX-faceSize/2+ 'px',
            'top': img.position().top+event.offsetY-faceSize/2 + 'px',
            'width': faceSize + 'px',
            'height': faceSize + 'px'
        }
    }).insertAfter(img);
    showDetectionList(n,event.offsetX,null,img.attr("data-id"),null);
}

function closeNewModify() {
    $('.newperson').remove();
    $('.personsearch').remove();
}

function getUrlVar(varName) {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        if (hash[0]===varName && hash.length==2 && null!==hash[1])
            return hash[1];

    }
    return null;
}