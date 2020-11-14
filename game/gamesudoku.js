/** Sudoku game */
function Sudoku(params) {
    this.INIT = 0;
    this.RUNNING = 1;
    this.END = 2;

    this.gameId = params.gameId;
    this.id = params.id || 'sudoku_container';
    this.init(this.fixCellsNr,params.secondsElapsed, params.score, params.board, params.boardSolution, params.boardValues, params.boardNotes);
    var t = this;
    setInterval(function(){ t.timer(); },1000);
    return this;
}

Sudoku.prototype.init = function(level,secondsElapsed, score, board, boardSolution, boardValues, boardNotes) {
    this.status = this.INIT;
    this.score = score!=null?score:0;
    this.board = board!=null?board:[];
    this.boardSolution = boardSolution!=null?boardSolution:[];
    this.boardValues = boardValues!=undefined?boardValues:new Array(81).fill(0,0,81);
    this.boardNotes = boardNotes!=undefined?boardNotes:new Array(81).fill('',0,81);
    this.secondsElapsed = secondsElapsed!=null?secondsElapsed:0;
    this.cell = null;
    this.markNotes = false;
    this.level=(level!=null && level<5 && level>0)?level:2;

    if (this.level==1) {
        $('#sudoku_title').html("Sudoku ügyeseknek");
        this.scoreMultiplicator = 20;
        this.fixCellsNr=45;
    } if (this.level==2) {
        $('#sudoku_title').html("Sudoku haladoknak");
        this.scoreMultiplicator = 45;
        this.fixCellsNr=40;
    } if (this.level==3) {
        $('#sudoku_title').html("Sudoku lángeszüknek");
        this.scoreMultiplicator = 70;
        this.fixCellsNr=35;
    } if (this.level==4) {
        $('#sudoku_title').html("Sudoku zseniknek");
        this.scoreMultiplicator = 85;
        this.fixCellsNr=30;
    }

    if (this.board.length==0) {
        this.boardGenerator(this.fixCellsNr);
    }


    console.log("GameInit id="+this.gameId+" level="+this.level+" scoreMultiplicator="+this.scoreMultiplicator+" fixedCells="+this.fixCellsNr);
    return this;
};

Sudoku.prototype.getCellsComplete = function() {
    this.cellsComplete=0;
    for (var i = 0; i < 81; i++) {
        if (parseInt(this.board[i]) > 0 || parseInt(this.boardValues[i]) == parseInt(this.boardSolution[i]))
            this.cellsComplete++;
    }
    return this.cellsComplete;
}

Sudoku.prototype.timer = function() {
    if (this.status === this.RUNNING) {
        this.secondsElapsed++;
        $('.time').text( '' + this.secondsElapsed );
    }
};

/** Shuffle array */
Sudoku.prototype.shuffle = function(array) {
    var currentIndex   = array.length,
        temporaryValue = 0,
        randomIndex = 0;

    while (0 !== currentIndex) {
        randomIndex   = Math.floor(Math.random() * currentIndex);
        currentIndex -= 1;
        temporaryValue      = array[currentIndex];
        array[currentIndex] = array[randomIndex];
        array[randomIndex]  = temporaryValue;
    }

    return array;
};

/** Generate the sudoku board
 * changes this.board this.boardSolution
 */
Sudoku.prototype.boardGenerator = function(fixCellsNr) {

    //fill trivial solution
    /*this.boardSolution = [];
    for (var i = 0; i < 9; i++) {
        for (var j = 0; j < 9; j++) {
            this.boardSolution[i+j*9] = Math.floor( (i*3 + i/3 + j) % (9) + 1 );
        }
    }*/
    this.boardSolution = new Array(
        5,2,1,3,8,9,6,4,7,
        3,6,4,2,7,5,1,9,8,
        8,7,9,4,1,6,3,5,2,
        2,5,8,9,3,1,4,7,6,
        1,4,6,8,5,7,9,2,3,
        9,3,7,6,2,4,5,8,1,
        4,8,2,1,9,3,7,6,5,
        7,9,3,5,6,2,8,1,4,
        6,1,5,7,4,8,2,3,9);

    var shuffleidx = new Array(0,1,2);

    //shuffle colums
    tmpboard=[];
    for (var m=0;m<3;m++) {
        shuffleidx = this.shuffle(shuffleidx);
        for (var i = 0; i < 3; i++) {
            for (var j = 0; j < 9; j++) {
                tmpboard[j*9 + shuffleidx[i] + m*3] = this.boardSolution[j*9 + i + m*3];
            }
        }
    }
    this.boardSolution = tmpboard;

    //shuffle rows
    tmpboard=[];
    for (var m=0;m<3;m++) {
        shuffleidx = this.shuffle(shuffleidx);
        for (var i = 0; i < 3; i++) {
            for (var j = 0; j < 9; j++) {
                tmpboard[j + shuffleidx[i]*9 + m*27] = this.boardSolution[j + i*9 + m*27];
            }
        }
    }
    this.boardSolution = tmpboard;

    //Change values by shufflematrix
    var shufflematrix = this.shuffle(new Array(1,2,3,4,5,6,7,8,9));
    for (var i=0;i<81;i++) {
        this.boardSolution[i]=shufflematrix[this.boardSolution[i]-1];
    }

    //board init
    var board_indexes =[],
        board_init = [];

    //shuffle board indexes and cut empty cells
    for (var i=0; i < 81; i++) {
        board_indexes[i] = i;
        board_init[i] = 0;
    }

    board_indexes = this.shuffle(board_indexes);
    board_indexes = board_indexes.slice(0, fixCellsNr);

    //build the init board
    for (var i=0; i< 81; i++) {
        board_init[ board_indexes[i] ] = this.boardSolution[ board_indexes[i] ];
        if (parseInt(board_init[ board_indexes[i] ]) > 0) {
            this.cellsComplete++;
        }
    }

    this.board = board_init;
    this.boardNotes=new Array(81).fill('',0,81);
    this.boardValues=new Array(81).fill(0,0,81);

    for (var i =0; i<81 ; i++) {
        if (this.board[i]===0) {
            this.boardNotes[i]=this.getPossibleValues(i);
        }
    }

};

Sudoku.prototype.getPossibleValues = function(cellIdx) {
    var notes=Array(1,2,3,4,5,6,7,8,9);
    var row = Math.floor(cellIdx / 9);
    var col = cellIdx % 9;
    var square = Math.floor(col / 3) * 3 + Math.floor(row / 3) * 27;

    for (var j=0; j<9; j++) {
        //remove values from row
        var idx =row*9+j;
        if ( this.board[idx]!==0 && notes.indexOf(this.board[idx])!==-1)
            notes.splice(notes.indexOf(this.board[idx]),1);
        //remove values from column
        var idx =j*9+col;
        if ( this.board[idx]!==0 && notes.indexOf(this.board[idx])!==-1)
            notes.splice(notes.indexOf(this.board[idx]),1);
        //remove values from square
        var idx = square +(j % 3) +Math.floor(j / 3)*9;
        if (this.board[idx]!==0 && notes.indexOf(this.board[idx])!==-1)
            notes.splice(notes.indexOf(this.board[idx]),1);
    }
    return notes.join(",");
};

/** Draw sudoku board in the specified container */
Sudoku.prototype.drawBoard = function(){
    var index = 0,
        position       = { x: 0, y: 0 },
        group_position = { x: 0, y: 0 };

    var sudoku_board = $('<div></div>').addClass('sudoku_board');
    var sts ='<b>Mezők:</b> <span class="cells_complete">'+ this.getCellsComplete() +'/'+81;
    sts +='</span> <b>Idő:</b> <span class="time">' + this.secondsElapsed + '</span>';
    sts +='</span> <b>Pontszám:</b> <span class="score">' + Math.round(this.score) + '</span>';
    var sudoku_statistics = $('<div></div>')
        .addClass('statistics')
        .html( sts);

    $('#'+ this.id).empty();

    //draw board
    for (i=0; i < 9; i++) {
        for (j=0; j < 9; j++) {
            position       = { x: i+1, y: j+1 };
            group_position = { x: Math.floor((position.x -1)/3), y: Math.floor((position.y-1)/3) };

            var value = (this.board[index] > 0 ? this.board[index] : ''),
                value_solution = (this.boardSolution[index] > 0 ? this.boardSolution[index] : ''),
                cell = $('<div></div>')
                    .addClass('cell')
                    .attr('x', position.x)
                    .attr('y', position.y)
                    .attr('gr', group_position.x +''+ group_position.y)
                    .html('<span>'+ value +'</span>' );

            if ( value > 0) {
                cell.addClass('fix');
            }
            //Set player values
            if( this.boardValues[index]>0) {
                cell.html('<span>'+ this.boardValues[index] +'</span>');
                if (this.boardValues[index]!==this.boardSolution[index])
                    cell.addClass('notvalid');
            }
            //Set player notes
            if(this.boardNotes[index]!==undefined && this.boardNotes[index]!=='') {
                var notes=this.boardNotes[index].toString().split(',');
                var note_width = Math.floor($(this.cell).width() / 2);
                for (var ino=0;ino<notes.length;ino++) {
                    if (parseInt(notes[ino])>0) {
                        $('<div></div>')
                            .addClass('note')
                            .css({'line-height': note_width + 'px', 'height': note_width - 1, 'width': note_width - 1})
                            .text(notes[ino])
                            .appendTo(cell);
                    }
                }
            }

            if ( position.x % 3 === 0 && position.x != 9 ) {
                cell.addClass('border_h');
            }

            if ( position.y % 3 === 0 && position.y != 9 ) {
                cell.addClass('border_v');
            }

            cell.appendTo(sudoku_board);
            index++;
        }
    }

    sudoku_board.appendTo('#'+ this.id);

    //draw console
    var sudoku_console_cotainer = $('<div></div>').addClass('board_console_container');
    var sudoku_console = $('<div></div>').addClass('board_console');

    for (i=1; i <= 9; i++) {
        $('<div></div>').addClass('num').text(i).appendTo(sudoku_console);
    }
    $('<div></div>').addClass('num remove').text('töröl').appendTo(sudoku_console);
    $('<div></div>').addClass('num note').text('?').appendTo(sudoku_console);


    //add all to sudoku container
    sudoku_console_cotainer.appendTo('#'+ this.id).hide();
    sudoku_console.appendTo(sudoku_console_cotainer);
    sudoku_statistics.appendTo('#'+ this.id);

    //adjust size
    this.resizeWindow();
};

Sudoku.prototype.resizeWindow = function(){
    var screen = { w: $(window).width(), h: $(window).height() };

    //adjust the board
    var b_pos = $('#'+ this.id +' .sudoku_board').offset(),
        b_dim = { w: $('#'+ this.id +' .sudoku_board').width(),  h: $('#'+ this.id +' .sudoku_board').height() },
        s_dim = { w: $('#'+ this.id +' .statistics').width(),    h: $('#'+ this.id +' .statistics').height()   };

    var screen_wr = screen.w + s_dim.h + b_pos.top + 10;

    if (screen_wr > screen.h) {
        $('#'+ this.id +' .sudoku_board').css('width', (screen.h - b_pos.top - s_dim.h - 14) );
        $('#'+ this.id +' .board_console').css('width', (b_dim.h/2) );
    } else {
        $('#'+ this.id +' .sudoku_board').css('width', '98%' );
        $('#'+ this.id +' .board_console').css('width', '50%' );
    }

    var cell_width = $('#'+ this.id +' .sudoku_board .cell:first').width(),
        note_with  = Math.floor(cell_width/3) -1;

    $('#'+ this.id +' .sudoku_board .cell').height(cell_width);
    $('#'+ this.id +' .sudoku_board .cell span').css('line-height', cell_width+'px');
    $('#'+ this.id +' .sudoku_board .cell .note').css({'line-height': note_with+'px' ,'width' : note_with, 'height': note_with});

    //adjust the console
    var console_cell_width = $('#'+ this.id +' .board_console .num:first').width();
    $('#'+ this.id +' .board_console .num').css('height', console_cell_width);
    $('#'+ this.id +' .board_console .num').css('line-height', console_cell_width+'px');

    //adjust console
    b_dim = { w: $('#'+ this.id +' .sudoku_board').width(),  h: $('#'+ this.id +' .sudoku_board').width() };
    b_pos = $('#'+ this.id +' .sudoku_board').offset();
    c_dim = { w: $('#'+ this.id +' .board_console').width(), h: $('#'+ this.id +' .board_console').height() };

    var c_pos_new = { left : ( b_dim.w/2 - c_dim.w/2 + b_pos.left ), top  : ( b_dim.h/2 - c_dim.h/2 + b_pos.top ) };
    $('#'+ this.id +' .board_console').css({'left': c_pos_new.left, 'top': c_pos_new.top});

    //adjust the gameover container
    var gameover_pos_new = { left : ( screen.w/20 ), top  : ( screen.w/20 + b_pos.top ) };

    $('#'+ this.id +' .gameover').css({'left': gameover_pos_new.left, 'top': gameover_pos_new.top});

};

/** Show console */
Sudoku.prototype.showConsole = function(cell) {
    $('#'+ this.id +' .board_console_container').show();

    var t = this;
    var oldNotes = $(this.cell).find('.note');

    //init
    $('#'+ t.id +' .board_console .num').removeClass('selected');

    //mark buttons
    if(t.markNotes) {
        //select markNote button
        $('#'+ t.id +' .board_console .num.note').addClass('selected');

        //select buttons
        $.each(oldNotes, function() {
            var noteNum = $(this).text();
            $('#'+ t.id +' .board_console .num:contains('+ noteNum +')').addClass('selected');
        });
    }


    return this;
};

/** Hide console */
Sudoku.prototype.hideConsole = function(cell) {
    $('#'+ this.id +' .board_console_container').hide();
    return this;
};

/** Select cell and prepare it for input from sudoku board console */
Sudoku.prototype.cellSelect = function(cell){
    this.cell = cell;

    var value = $(cell).text() | 0,
        position       = { x: $(cell).attr('x'), y: $(cell).attr('y') } ,
        group_position = { x: Math.floor((position.x -1)/3), y: Math.floor((position.y-1)/3) },
        horizontal_cells = $('#'+ this.id +' .sudoku_board .cell[x="'+ position.x +'"]'),
        vertical_cells   = $('#'+ this.id +' .sudoku_board .cell[y="'+ position.y +'"]'),
        group_cells      = $('#'+ this.id +' .sudoku_board .cell[gr="'+ group_position.x +''+ group_position.y +'"]'),
        same_value_cells = $('#'+ this.id +' .sudoku_board .cell span:contains('+value+')');

    //remove all other selections
    $('#'+ this.id +' .sudoku_board .cell').removeClass('selected current group');
    //$('#'+ this.id +' .sudoku_board .cell span').removeClass('samevalue');
    $('#'+ this.id +' .sudoku_board .cell').removeClass('samevalue');
    //select current cell
    $(cell).addClass('selected current');

    //highlight select cells
    horizontal_cells.addClass('selected');
    vertical_cells.addClass('selected');
    group_cells.addClass('selected group');
    same_value_cells. parent() .addClass('samevalue');

    if ($( this.cell ).hasClass('fix')) {
        $('#'+ this.id +' .board_console .num').addClass('no');
    } else {
        /*$('#'+ this.id +' .board_console .num').removeClass('no');*/
        if (this.boardValues[this.getCellIndex()]!=this.boardSolution[this.getCellIndex()]) {
            this.showConsole();
            this.resizeWindow();
        }
    }
};

/** Add value from sudoku console to selected board cell */
Sudoku.prototype.addValue = function(value) {
    var
        position       = { x: $(this.cell).attr('x'), y: $(this.cell).attr('y') },
        group_position = { x: Math.floor((position.x -1)/3), y: Math.floor((position.y-1)/3) },

        horizontal_cells = '#'+ this.id +' .sudoku_board .cell[x="'+ position.x +'"]',
        vertical_cells   = '#'+ this.id +' .sudoku_board .cell[y="'+ position.y +'"]',
        group_cells      = '#'+ this.id +' .sudoku_board .cell[gr="'+ group_position.x +''+ group_position.y +'"]',

        horizontal_cells_exists = $(horizontal_cells + ' span:contains('+ value +')'),
        vertical_cells_exists   = $(vertical_cells + ' span:contains('+ value +')'),
        group_cells_exists      = $(group_cells + ' span:contains('+ value +')'),

        horizontal_notes = horizontal_cells + ' .note:contains('+ value +')',
        vertical_notes   = vertical_cells + ' .note:contains('+ value +')',
        group_notes      = group_cells + ' .note:contains('+ value +')',

        old_value = parseInt($( this.cell ).not('.notvalid').text()) || 0;


    if ($( this.cell ).hasClass('fix')) {
        return;
    }

    //delete value or write it in cell
    $( this.cell ).find('span').text( (value === 0) ? '' : value );
    this.boardValues[this.getCellIndex()]=value;

    console.log('Value added ', value);
    $(this.cell).removeClass('notvalid');
    if (value!=0) {
        if (this.boardSolution[this.getCellIndex()]==value) {
            var scoreFunction = 1+ (1 / this.secondsElapsed* (24600/this.scoreMultiplicator));
            var points = this.scoreMultiplicator * (scoreFunction > this.scoreMultiplicator ? this.scoreMultiplicator : scoreFunction);
            this.score += points;
            $(".score").html(Math.round(this.score));
            //remove all notes from current cell,  line column and group
            $(horizontal_notes).remove();
            $(vertical_notes).remove();
            $(group_notes).remove();
            this.animateScore(this.cell,points);
        } else {
            $(this.cell).addClass('notvalid');
            this.scoreMultiplicator *=0.9;
            console.log("scoreMultiplicator:"+this.scoreMultiplicator);
        }
    }

    //game over
    if (this.getCellsComplete() === 81) {
        $(this.cell).removeClass('selected current');
        this.gameOver();
    }

    $('#'+ this.id +' .statistics .cells_complete').text(''+this.getCellsComplete()+'/'+81);
    this.saveGame();

    return this;
};

Sudoku.prototype.animateScore = function(o,points) {
    var tag = $("<span></span>").addClass("score_tag").html("+"+Math.round(points));
    $(o).css("overflow","visible");
    tag.appendTo(o);
    $(".score_tag").fadeOut({
        duration:1300,
        step:function(now, fx){
            if (parseInt($(o).attr("x"))<4)
                $(this).css("top",  100 -(100 * now) + "px");
            else
                $(this).css("top",  -100 +(100 * now) + "px");
        },
        complete:function() {
            tag.remove();
        }
    });
}

/** save game status **/
Sudoku.prototype.saveGame = function() {
    //Ajax savescore
    var json = {"board":game.board,"boardSolution":game.boardSolution,
        "boardValues":game.boardValues,"boardNotes":game.boardNotes,
        "fixedCellsNr":game.fixCellsNr,"secondsElapsed":game.secondsElapsed,
        "score":game.score,"over":false,"won":game.getCellsComplete() === 81};

    $.ajax({
        url: "ajax/setGameStatus?gameid="+game.gameId+"&gamestatus="+JSON.stringify(json),
        type:"GET",
        success:function(data){
            game.gameId = data.id;
        },
        error:function(data) {
            alert("A játék szerver nem elérhetö, probáld késöbb újból!");
        }
    });

}

/** Add note from sudoku console to selected board cell */
Sudoku.prototype.addNote = function(value) {
    console.log('addNote', value);

    var
        oldNotes = $(this.cell).find('.note'),
        note_width = Math.floor($(this.cell).width() / 3);

    //add note to cell
    if (oldNotes.length < 14) {
        $('<div></div>')
            .addClass('note')
            .css({'line-height' : note_width+'px', 'height': note_width -1, 'width': note_width -1})
            .text(value)
            .appendTo( this.cell );
        this.addNoteToArray(value);
        this.saveGame();
    }

    return this;
};

Sudoku.prototype.getCellIndex = function() {
    return (parseInt(this.cell.attributes.x.value)-1)*9+parseInt(this.cell.attributes.y.value)-1;
}

Sudoku.prototype.addNoteToArray = function(value) {
    var notes = this.boardNotes[this.getCellIndex()].toString().split(',');
    if (notes.indexOf(value)==-1) {
        notes.push(value);
    }
    this.boardNotes[this.getCellIndex()]=notes.join(",");
}

Sudoku.prototype.removeNoteFromArray = function(value) {
    var notes = this.boardNotes[this.getCellIndex()].toString().split(',');
    if (notes.indexOf(value)==-1) {
        notes.splice(notes.indexOf(value),1);
    }
    this.boardNotes[this.getCellIndex()]=notes.join(",");
}

/**  Remove note from sudoku console to selected board cell */
Sudoku.prototype.removeNote = function(value) {
    if (value === 0) {
        $(this.cell).find('.note').remove();
        this.boardNotes[this.getCellIndex()]='';
    } else {
        $(this.cell).find('.note:contains('+value+')').remove();
        this.removeNoteFromArray(value);
    }
    this.saveGame();
    return this;
};

/** End game routine */
Sudoku.prototype.gameOver = function(){
    this.status = this.END;
    this.saveGame();
    $('.gameover_container').show();
    $('#gameover_score').html(Math.round(this.score));
};

/** Run a new sudoku game */
Sudoku.prototype.run = function(){
    this.status = this.RUNNING;

    var t = this;
    this.drawBoard();

    //click on board cell
    $('#'+ this.id +' .sudoku_board .cell').on('click', function(e){
        t.cellSelect(this);
    });

    //click on console num
    $('#'+ this.id +' .board_console .num').on('click', function(e){
        var
            value          = $.isNumeric($(this).text()) ? parseInt($(this).text()) : 0,
            clickMarkNotes = $(this).hasClass('note'),
            clickRemove = $(this).hasClass('remove'),
            numSelected    = $(this).hasClass('selected');

        if (clickMarkNotes) {
            console.log('clickMarkNotes'+t.markNotes);
            t.markNotes = !t.markNotes;
            t.showConsole(t.cell);

            if(t.markNotes) {
                $(this).addClass('selected');
            } else {
                $(this).removeClass('selected');
            }

        } else {
            if (t.markNotes) {
                if (!numSelected) {
                    if (!value) {
                        t.removeNote(0).hideConsole();
                    } else {
                        t.addValue(0).addNote(value).hideConsole();
                    }
                } else {
                    t.removeNote(value).hideConsole();
                }
            } else {
                t.removeNote(0).addValue(value).hideConsole();
            }
        }
    });

    //click outer console
    $('#'+ this.id +' .board_console_container').on('click', function(e){
        if ( $(e.target).is('.board_console_container') ) {
            $(this).hide();
        }
    });

    $( window ).resize(function() {
        t.resizeWindow();
    });
};

//main
var game;
function gamesudoku(gameId,fixedCellsNr,secondsElapsed,score,board,boardSolution,boardValues,boardNotes) {
    game = new Sudoku({
        id: 'sudoku_container',
        gameId :gameId,
        fixCellsNr: fixedCellsNr,
        secondsElapsed:secondsElapsed,
        score:score,
        board:board,
        boardSolution:boardSolution,
        boardValues:boardValues,
        boardNotes:boardNotes,
    });

    game.run();

    $('#sidebar-toggle').on('click', function (e) {
        $('#sudoku_menu').toggleClass("open-sidebar");
    });

    //restart game

    $('.restart1').on('click', function () {
        game.init(1).run();
        $('.gameover_container').hide();
        $('#sudoku_menu').removeClass('open-sidebar');
        game.saveGame();
    });

    $('.restart2').on('click', function () {
        game.init(2).run();
        $('.gameover_container').hide();
        $('#sudoku_menu').removeClass('open-sidebar');
        game.saveGame();
    });

    $('.restart3').on('click', function () {
        game.init(3).run();
        $('.gameover_container').hide();
        $('#sudoku_menu').removeClass('open-sidebar');
        game.saveGame();
    });

    $('.restart4').on('click', function () {
        game.init(4).run();
        $('.gameover_container').hide();
        $('#sudoku_menu').removeClass('open-sidebar');
        game.saveGame();
    });
}