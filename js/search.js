function showSearchBox(noAnimation) {
    closeLogin();
    if (noAnimation==null || noAnimation==false)
        $("#uSearch").slideDown("slow");
    else
        $("#uSearch").show();
    $("#srcText").focus();
    onResize(135);
}

function closeSearch() {
    $("#uSearch").slideUp("slow");
    onResize(0);
}

function search() {
    document.location.href="search?srcText="+$("#srcText").val();
}


function searchPersonAndPicture() {
    $('#searchpicturebadge').html('<img src="images/loading.gif" style="height: 14px"/>');
    $('#searchpersonbadge').html('<img src="images/loading.gif" style="height: 14px"/>');

    $.ajax({
        url: "ajax/getPersonByName?name="+$("#srcText").val(),
        type:"GET",
        success:function(data){
            $('#searchpersontable').empty();
            $('#searchpersonbadge').html(data.length);
            if (data!=null && data.length>0) {
                data.forEach(function (row) {
                    if (row.schoolIdsAsTeacher===null) {
                        var pclass = row.scoolYear + ' ' + row.scoolClass + ' ';
                    } else {
                        if (row.gender=="f") {
                            var pclass = "<?php maierlabs\lpfw\Appl::_('Tanárnő')?>";
                        } else {
                            var pclass = "<?php maierlabs\lpfw\Appl::_('Tanár úr')?>";
                        }
                        row.schoolID = row.schoolIdsAsTeacher.substring(1,row.schoolIdsAsTeacher.indexOf(")"));
                    }
                    var pname = (row.title != null ? row.title + ' ' : '') + row.lastname + ' ' + row.firstname;
                    pname += ((row.birthname !== null && row.birthname !=='') ? ' ('+row.birthname+')':'');
                    if (row.picture!=null && row.picture.length>5) {
                        var pimg = '<img src="images/' + row.picture + '" class="diak_image_icon" />';
                    } else {
                        var pimg = '<img src="images/' + (row.gender==="f"?"woman.png":"man.png") + '" class="diak_image_icon" />';
                    }
                    var html = '<tr>';
                    html += '<td style="text-align: center">' + pimg + '</td>';
                    html += '<td><a href="editDiak?uid='+row.id+'">' + pname + '</a></td>';
                    html += '<td><img src="images/school'+row.schoolID+'/logo.jpg" style="height: 33px"/></td>';
                    html += '<td>' + pclass + '</td>';
                    html += '</tr>';
                    console.log(html);
                    $('#searchpersontable').append(html);
                });
            }
        },
        error:function(error) {
            $('#searchpersonbadge').html('');
            console.log(error);
        }
    });

    $.ajax({
        url: "ajax/getPictureByText?text="+$("#srcText").val(),
        type:"GET",
        success:function(data){
            $('#searchpicturetable').empty();
            $('#searchpicturebadge').html(data.length);
            if (data!=null && data.length>0) {
                data.forEach(function (row) {
                    var pclass = "<?php maierlabs\lpfw\Appl::_('Kép')?>";
                    var pname = '<b>'+$("<div>").html(row.title.toText().substring(0,20)).text()+'</b> ';
                    pname +=$("<div>").html(row.comment.toText().substring(0,25)).text();
                    var html = '<tr>';
                    html += '<td><a href="picture?id='+row.id+'">' + pname + '</a></td>';
                    //html += '<td>' + pclass + '</td>';
                    html += '</tr>';
                    console.log(html);
                    $('#searchpicturetable').append(html);
                });
            }
        },
        error:function(error) {
            $('#searchpicturebadge').html('');
            console.log(error);
        }
    });

    String.prototype.toText = function(){
        return $('<div>').html(this).text();
    };
}