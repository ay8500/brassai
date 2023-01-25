// define Card
var Card = function(newSuit, newColor, newValue) {
    this.id = newSuit + newValue;
    this.suit = newSuit;
    this.color = newColor;
    this.value = newValue;
    this.faceup = false;
}

// define Solitaire Game
var SG = {

    //////////////////////////
    // initialization stuff
    //////////////////////////

    init: function() {

        SG.cardSuits = ["clubs", "spades", "hearts", "diamonds"]; // must be in this order because of the playing card image

        SG.cardPositions = {
            stock: [],
            waste: [],
            foundation: [[],[],[],[]],
            tableau: [[],[],[],[],[],[],[]]
        };

        SG.previousMove = [];

        SG.previousFlippedCard = null;
        SG.gameID = undefined;
        SG.moveCount = 0;
        SG.score = 0;
        SG.time = 0;
        SG.isGameOver = false;
        if (SG.mytimer!==undefined)
            clearInterval(SG.mytimer);

    },

    timer: function() {
        SG.time++;
        $("#time").html((SG.time)+"s");
    },


    ///////////////
    // DOM stuff
    ///////////////

    // create a div element that will display a card
    drawCard: function(suit, color, value, faceup, offset,isGameOver) {
        var cardClass = "card " + color;
        if(!faceup) {
            cardClass += " facedown";
        }
        var cardDiv = document.createElement("div");
        cardDiv.setAttribute("class", cardClass);
        cardDiv.setAttribute("id", suit + value);
        if (!SG.isGameOver)
            cardDiv.setAttribute("onclick", "SG.attemptToMove(this)");
        cardDiv.style.top = offset + "%";
        var bgTop = SG.cardSuits.indexOf(suit) * 33.3333333333333;
        var bgLeft = (value - 1) * 7.692307692307692;
        if(faceup) {
            cardDiv.style.backgroundPosition = bgLeft + "% " + bgTop + "%";
        }
        return cardDiv;
    },

    // place card divs in each div position based on position arrays
    drawStack: function(pos, elementID, offset) {
        var stack = $('#' + elementID);
        stack.empty();
        pos.forEach(function(item, index, arr) {
            stack.append(SG.drawCard(item.suit, item.color, item.value, item.faceup, index*offset));
        });
    },

    // save the cards that were moved and their coordinate positions
    queueCardAnimations: function(cardArray) {
        var cardPositionsArray = [];
        cardArray.forEach(function(card, index, arr) {
            cardPositionsArray.push([card.id, $('#' + card.id).offset()]);
        });
        return cardPositionsArray;
    },

    // use the previous coordinate positions to animate the card move
    animateCards: function(prevCardPositions) {
        prevCardPositions.forEach(function(item, index, arr) {
            var card = $('#' + item[0]);
            var currentCardPosition = card.offset();
            var topOffset = item[1].top - currentCardPosition.top;
            var leftOffset = item[1].left - currentCardPosition.left;
            // move the card to it's previous position
            card.css("transform", "translate(" + leftOffset + "px, " + topOffset + "px)");
            // make sure the card is displayed over the others
            card.parent().css("z-index", 1000);
            // apply animation class
            card.addClass("slideCard");
            // remove css just before the animation finishes to avoid glitches
            setTimeout(function(){
                card.css("transform", "translate(0, 0)");
                card.parent().css("z-index", 0);
                card.removeClass("slideCard");
            }, 295);
        });
    },

    // redraw the move count
    drawMoveCount: function() {
        var moveCounter = SG.moveCount + " lépés";
        $("#move-count").html(moveCounter);
        $("#score").html("pontszám: " + SG.score);
    },

    // redraw the stack count
    drawStockCount: function() {
        $("#stock").attr("data-card-count", SG.cardPositions.stock.length);
    },

    // move and draw all card divs
    drawAllCards: function(cardsToAnimate) {
        var topRowOffset = 0;
        var bottomRowOffset = 20;
        SG.drawStack(SG.cardPositions.stock, "stock", topRowOffset);
        SG.drawStack(SG.cardPositions.waste, "waste", 0);
        SG.drawStack(SG.cardPositions.foundation[0], "foundation-1", 0);
        SG.drawStack(SG.cardPositions.foundation[1], "foundation-2", 0);
        SG.drawStack(SG.cardPositions.foundation[2], "foundation-3", 0);
        SG.drawStack(SG.cardPositions.foundation[3], "foundation-4", 0);
        SG.drawStack(SG.cardPositions.tableau[0], "tableau-1", bottomRowOffset);
        SG.drawStack(SG.cardPositions.tableau[1], "tableau-2", bottomRowOffset);
        SG.drawStack(SG.cardPositions.tableau[2], "tableau-3", bottomRowOffset);
        SG.drawStack(SG.cardPositions.tableau[3], "tableau-4", bottomRowOffset);
        SG.drawStack(SG.cardPositions.tableau[4], "tableau-5", bottomRowOffset);
        SG.drawStack(SG.cardPositions.tableau[5], "tableau-6", bottomRowOffset);
        SG.drawStack(SG.cardPositions.tableau[6], "tableau-7", bottomRowOffset);
        SG.drawMoveCount();
        SG.drawStockCount();
        if(cardsToAnimate) {
            SG.animateCards(cardsToAnimate);
        }
        saveGame(SG.gameID,SG.moveCount,SG.score,SG.time,SG.cardPositions,false,false);
    },


    /////////////////////
    // logic stuff
    /////////////////////

    // build a deck of cards
    createDeck: function() {
        var stock = [];
        SG.cardSuits.forEach(function(item, index, arr) {
            for(var val = 0; val < 13; val++) {
                var cardColor = "black";
                if(item == "hearts" || item == "diamonds") {
                    cardColor = "red";
                }
                stock.push(new Card(item, cardColor, val+1))
            }
        });
        return stock;
    },

    // shuffle an array
    // used to shuffle the deck of cards
    shuffleArray: function(o) {
        for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
        return o;
    },

    // return the top (last) card in the array
    // if position is empty, return null
    getTopCard: function(positionArray) {
        if(positionArray.length == 0) {
            return null;
        } else {
            return positionArray[positionArray.length - 1];
        }
    },

    // get card object from all cards based on div id
    getCardById: function(cardID) {
        // flatten the position array
        var deck = $.map(SG.cardPositions, function recurs(n) {
            return ($.isArray(n) ? $.map(n, recurs): n);
        });
        // iterate the flattened array
        for(var i = 0; i < deck.length; i++) {
            if(deck[i].id == cardID) {
                return deck[i];
            }
        }
    },

    // return the array that corresponds to the position div id
    getPositionArray: function(positionID) {
        switch(positionID) {
            case "stock":
                return SG.cardPositions.stock;
            case "waste":
                return SG.cardPositions.waste;
            case "foundation-1":
                return SG.cardPositions.foundation[0];
            case "foundation-2":
                return SG.cardPositions.foundation[1];
            case "foundation-3":
                return SG.cardPositions.foundation[2];
            case "foundation-4":
                return SG.cardPositions.foundation[3];
            case "tableau-1":
                return SG.cardPositions.tableau[0];
            case "tableau-2":
                return SG.cardPositions.tableau[1];
            case "tableau-3":
                return SG.cardPositions.tableau[2];
            case "tableau-4":
                return SG.cardPositions.tableau[3];
            case "tableau-5":
                return SG.cardPositions.tableau[4];
            case "tableau-6":
                return SG.cardPositions.tableau[5];
            case "tableau-7":
                return SG.cardPositions.tableau[6];
            default:
                return null;
        }
    },

    // move a single card and all cards stacked on top of it
    // returns an array of the selected card and subsequent cards
    moveCards: function(selectedCard, startPos, endPos) {
        var selectedIndex;
        // get index of selected card
        startPos.forEach(function(item, index, arr) {
            if(selectedCard.id == item.id) {
                selectedIndex = index;
            }
        });
        // grab the selected card and all of the cards above it
        var stack = startPos.slice(selectedIndex);
        startPos.splice(selectedIndex, startPos.length);
        // add them to the end position
        stack.forEach(function(item, index, arr) {
            endPos.push(item);
        });
        // store move for undo
        SG.previousMove = [];
        SG.previousMove.push(selectedCard, endPos, startPos);
        SG.moveCount += 1;
        cardsInFondation=SG.cardPositions.foundation[0].length+SG.cardPositions.foundation[1].length+SG.cardPositions.foundation[2].length+SG.cardPositions.foundation[3].length;
        SG.score = 1000 + cardsInFondation*650 - SG.moveCount - SG.time*3;
        return stack;
    },

    // top card of each tableau pile will be face up
    flipTableauTopCards: function() {
        SG.cardPositions.tableau.forEach(function(pos, index, arr) {
            flipCard = SG.getTopCard(pos);
            if(flipCard != null && flipCard.faceup == false) {
                flipCard.faceup = true;
                // store last card that was flipped for undo
                SG.previousFlippedCard = flipCard;
            }
        });
        SG.cardPositions.foundation.forEach(function(pos, index, arr) {
            flipCard = SG.getTopCard(pos);
            if(flipCard != null && flipCard.faceup == false) {
                flipCard.faceup = true;
                // store last card that was flipped for undo
                SG.previousFlippedCard = flipCard;
            }
        });
        flipCard = SG.getTopCard(SG.cardPositions.waste);
        if(flipCard != null && flipCard.faceup == false) {
            flipCard.faceup = true;
            // store last card that was flipped for undo
            SG.previousFlippedCard = flipCard;
        }
    },

    // deal cards from the stock to the tableau
    dealCards: function(cardPositions) {
        for(var i = 0; i < cardPositions.tableau.length; i++) {
            for(var x = 0; x <= i; x++) {
                SG.moveCards(SG.getTopCard(cardPositions.stock), cardPositions.stock, cardPositions.tableau[i]);
            }
        }
        SG.moveCount = 0;
        SG.score =0;
        SG.time=0;
        // make sure the player cannot undo a deal
        SG.previousMove = [];
        return cardPositions;
    },

    // create array of legal positions for the selected card
    getLegalMoves: function(selectedCard, selectedPosition) {
        var moves = [];
        if(selectedCard.faceup == true) {
            SG.cardPositions.foundation.forEach(function(pos, index, arr) {
                var targetCard = SG.getTopCard(pos);
                if(targetCard == null) {
                    if(selectedCard.value == 1) {
                        moves.push(pos);
                    }
                } else if( targetCard.suit == selectedCard.suit
                    && targetCard.value == (selectedCard.value - 1)
                    && selectedPosition[selectedPosition.length - 1].id == selectedCard.id)
                {
                    moves.push(pos);
                }
            });
            SG.cardPositions.tableau.forEach(function(pos, index, arr) {
                var targetCard = SG.getTopCard(pos);
                if(targetCard == null) {
                    if(selectedCard.value == 13) {
                        moves.push(pos);
                    }
                } else if( targetCard.color != selectedCard.color
                    && targetCard.value == (selectedCard.value + 1)
                    && targetCard.faceup)
                {
                    moves.push(pos);
                }
            });
        }
        return moves;
    },

    // check if the player won
    victory: function() {
        for(var i = 0; i < SG.cardPositions.foundation.length; i++) {
            if(SG.cardPositions.foundation[i].length != 13) {
                return false;
            }
        }
        return true;
    },


    // if card is able to be moved, move it
    attemptToMove: function(cardToMove) {
        var clickedCard = SG.getCardById(cardToMove.id);
        var clickedPosition = SG.getPositionArray($(cardToMove).parent().attr('id'));
        var possibleMoves = SG.getLegalMoves(clickedCard, clickedPosition);
        if(possibleMoves.length > 0) {
            var movedCards = SG.moveCards(cardToMove, clickedPosition, possibleMoves[0]);
            SG.previousFlippedCard = null;
            SG.flipTableauTopCards();
            SG.drawAllCards(SG.queueCardAnimations(movedCards));
            if(SG.victory()) {
                SG.previousMove = [];
                $("#victory").html("Hurrá sikerült " + SG.moveCount + " lépésből megoldani. Pontszám:"+SG.score);
                $("#victory").css("display", "block");
                saveGame(SG.gameID,SG.moveCount,SG.score,SG.time,SG.cardPositions,true,true);
            }
        } else {
            // card cannot be moved
            $('#' + cardToMove.id).addClass("jiggleCard");
            setTimeout(function(){
                $('#' + cardToMove.id).removeClass("jiggleCard");
            }, 200);
        }
    },

    // if there is a previous move stored, undo it
    undoMove: function() {
        var movedCards;
        if(SG.previousMove.length == 3) {
            // last move was normal
            movedCards = SG.moveCards(SG.previousMove[0], SG.previousMove[1], SG.previousMove[2]);
            if(SG.previousFlippedCard != null) {
                SG.previousFlippedCard.faceup = !SG.previousFlippedCard.faceup;
            }
        } else if(SG.previousMove == "waste") {
            // last move was from waste to stock
            movedCards = SG.moveCards(SG.cardPositions.stock[0], SG.cardPositions.stock, SG.cardPositions.waste);
            // flip em
            SG.cardPositions.waste.forEach(function(item, index, arr) {
                item.faceup = true;
            });
            // reverse the order
            SG.cardPositions.waste.reverse();
        }
        SG.previousMove = [];
        SG.previousFlippedCard = null;
        if(movedCards != null) {
            SG.drawAllCards(SG.queueCardAnimations(movedCards));
        }
    },

    // move a card from the stock to the waste
    // if stock is empty, move all cards from waste to stock
    moveStockCard: function() {
        var movedCards;
        // select top card of stock
        var stockCard = SG.getTopCard(SG.cardPositions.stock);
        // if stock is not empty, move the top card to the waste and flip it
        if(stockCard != null) {
            stockCard.faceup = true;
            // store flipped card for undo
            SG.previousFlippedCard = stockCard;
            movedCards = SG.moveCards(stockCard, SG.cardPositions.stock, SG.cardPositions.waste);
        } else {
            // stock is empty
            // move all cards from the waste to the stock
            movedCards = SG.moveCards(SG.cardPositions.waste[0], SG.cardPositions.waste, SG.cardPositions.stock);
            // flip em
            SG.cardPositions.stock.forEach(function(item, index, arr) {
                item.faceup = false;
            });
            // reverse the order
            SG.cardPositions.stock.reverse();
            SG.previousMove = "waste";
        }
        SG.drawAllCards(SG.queueCardAnimations(movedCards));
    },

    // start a stored game
    startGame: function(gameID,gameStatus) {
        SG.init();
        SG.gameID = gameID;
        if (gameStatus===undefined || gameStatus===null || gameStatus.cardPositions===undefined)
            gameStatus = resetGame();
        SG.cardPositions = gameStatus.cardPositions;
        SG.moveCount = gameStatus.moveCount;
        SG.time = gameStatus.time;
        SG.score = gameStatus.score;
        SG.flipTableauTopCards();
        $("#victory").css("display", "none");
        SG.isGameOver = gameStatus.over;
        SG.drawAllCards();
        if (gameStatus.over!==true)
            SG.mytimer = setInterval(this.timer,1000);
    },

    // start the game
    newGame: function() {
        console.log("GameID:"+SG.gameID)
        saveGame(SG.gameID,SG.moveCount,SG.score,SG.time,SG.cardPositions,true,false);
        console.log("GameID:"+SG.gameID)
        var game = resetGame();
        SG.cardPositions = game.cardPositions;
        SG.moveCount = game.moveCount;
        SG.time = game.time;
        SG.score = game.score;
        SG.flipTableauTopCards();
        $("#victory").css("display", "none");
        if (SG.mytimer!==undefined)
            clearInterval(SG.mytimer);
        SG.mytimer = setInterval(this.timer,1000);
        SG.isGameOver = false;
        SG.drawAllCards();
    }

};

/**
 * Save game status
 * @param gameID  the game id
 * @param moveCount
 * @param score
 * @param time
 * @param cardPosition
 * @param over this game is over a new game id will be generated
 * @param won game is won a new game id will be generated
 */
function saveGame(gameID,moveCount,score,time,cardPosition,over,won) {
    var save = new Object();
    save.moveCount = moveCount;
    save.score = score;
    save.time = time;
    save.cardPositions = cardPosition;
    save.over = over;
    save.won = won;
    var json = JSON.stringify(save);
    //console.log(json+"\n\r");

    $.when(
        $.ajax({url: "ajax/setGameStatus?gameid="+gameID+"&gamestatus="+json })
    ).then( function (data, textStatus, jqXHR){
        if (jqXHR.status === 200) {
            console.log("Save Ok. gameId="+data.id+"\n\r");
            if (SG!==undefined)
                SG.gameID = data.id;
        } else {
            alert("A játék szerver nem elérhetö, probáld késöbb újból! Error:"+jqXHR.status);
        }
    });

}

/**
 * reset a game schuffle the cards and set the deal cards
 * @returns {Object}
 */
function resetGame(gameID,game) {
    cardPositions = {
        stock: [],
        waste: [],
        foundation: [[],[],[],[]],
        tableau: [[],[],[],[],[],[],[]]
    };
    cardPositions.stock= SG.createDeck();
    SG.shuffleArray(cardPositions.stock);
    SG.dealCards(cardPositions);
    ret = new Object();
    ret.cardPositions = cardPositions;
    ret.moveCount = 0;
    ret.time = 0;
    ret.score = 0;
     //var ret = {"moveCount":97,"score":61789,"time":0,"cardPositions":{"stock":[{"id":"spades7","suit":"spades","color":"black","value":7,"faceup":false},{"id":"clubs8","suit":"clubs","color":"black","value":8,"faceup":false},{"id":"spades6","suit":"spades","color":"black","value":6,"faceup":false},{"id":"hearts8","suit":"hearts","color":"red","value":8,"faceup":false},{"id":"diamonds9","suit":"diamonds","color":"red","value":9,"faceup":false}],"waste":[],"foundation":[[{"id":"hearts1","suit":"hearts","color":"red","value":1,"faceup":true},{"id":"hearts2","suit":"hearts","color":"red","value":2,"faceup":true},{"id":"hearts3","suit":"hearts","color":"red","value":3,"faceup":true}],[{"id":"clubs1","suit":"clubs","color":"black","value":1,"faceup":true},{"id":"clubs2","suit":"clubs","color":"black","value":2,"faceup":true},{"id":"clubs3","suit":"clubs","color":"black","value":3,"faceup":true},{"id":"clubs4","suit":"clubs","color":"black","value":4,"faceup":true},{"id":"clubs5","suit":"clubs","color":"black","value":5,"faceup":true}],[{"id":"diamonds1","suit":"diamonds","color":"red","value":1,"faceup":true},{"id":"diamonds2","suit":"diamonds","color":"red","value":2,"faceup":true},{"id":"diamonds3","suit":"diamonds","color":"red","value":3,"faceup":true},{"id":"diamonds4","suit":"diamonds","color":"red","value":4,"faceup":true},{"id":"diamonds5","suit":"diamonds","color":"red","value":5,"faceup":true},{"id":"diamonds6","suit":"diamonds","color":"red","value":6,"faceup":true},{"id":"diamonds7","suit":"diamonds","color":"red","value":7,"faceup":true}],[{"id":"spades1","suit":"spades","color":"black","value":1,"faceup":true},{"id":"spades2","suit":"spades","color":"black","value":2,"faceup":true},{"id":"spades3","suit":"spades","color":"black","value":3,"faceup":true},{"id":"spades4","suit":"spades","color":"black","value":4,"faceup":true}]],"tableau":[[{"id":"spades13","suit":"spades","color":"black","value":13,"faceup":true},{"id":"diamonds12","suit":"diamonds","color":"red","value":12,"faceup":true},{"id":"spades11","suit":"spades","color":"black","value":11,"faceup":true},{"id":"hearts10","suit":"hearts","color":"red","value":10,"faceup":true},{"id":"clubs9","suit":"clubs","color":"black","value":9,"faceup":true},{"id":"diamonds8","suit":"diamonds","color":"red","value":8,"faceup":true},{"id":"clubs7","suit":"clubs","color":"black","value":7,"faceup":true},{"id":"hearts6","suit":"hearts","color":"red","value":6,"faceup":true},{"id":"spades5","suit":"spades","color":"black","value":5,"faceup":true},{"id":"hearts4","suit":"hearts","color":"red","value":4,"faceup":true}],[{"id":"diamonds13","suit":"diamonds","color":"red","value":13,"faceup":true},{"id":"spades12","suit":"spades","color":"black","value":12,"faceup":true},{"id":"diamonds11","suit":"diamonds","color":"red","value":11,"faceup":true},{"id":"spades10","suit":"spades","color":"black","value":10,"faceup":true},{"id":"hearts9","suit":"hearts","color":"red","value":9,"faceup":true},{"id":"spades8","suit":"spades","color":"black","value":8,"faceup":true},{"id":"hearts7","suit":"hearts","color":"red","value":7,"faceup":true},{"id":"clubs6","suit":"clubs","color":"black","value":6,"faceup":true},{"id":"hearts5","suit":"hearts","color":"red","value":5,"faceup":true}],[{"id":"diamonds10","suit":"diamonds","color":"red","value":10,"faceup":true},{"id":"spades9","suit":"spades","color":"black","value":9,"faceup":true}],[{"id":"hearts13","suit":"hearts","color":"red","value":13,"faceup":true},{"id":"clubs12","suit":"clubs","color":"black","value":12,"faceup":true},{"id":"hearts11","suit":"hearts","color":"red","value":11,"faceup":true},{"id":"clubs10","suit":"clubs","color":"black","value":10,"faceup":true}],[],[{"id":"clubs13","suit":"clubs","color":"black","value":13,"faceup":true},{"id":"hearts12","suit":"hearts","color":"red","value":12,"faceup":true},{"id":"clubs11","suit":"clubs","color":"black","value":11,"faceup":true}],[]]}};
     //var ret = {"moveCount":142,"score":131989,"time":0,"cardPositions":{"stock":[],"waste":[],"foundation":[[{"id":"hearts1","suit":"hearts","color":"red","value":1,"faceup":true},{"id":"hearts2","suit":"hearts","color":"red","value":2,"faceup":true},{"id":"hearts3","suit":"hearts","color":"red","value":3,"faceup":true},{"id":"hearts4","suit":"hearts","color":"red","value":4,"faceup":true},{"id":"hearts5","suit":"hearts","color":"red","value":5,"faceup":true},{"id":"hearts6","suit":"hearts","color":"red","value":6,"faceup":true},{"id":"hearts7","suit":"hearts","color":"red","value":7,"faceup":true},{"id":"hearts8","suit":"hearts","color":"red","value":8,"faceup":true},{"id":"hearts9","suit":"hearts","color":"red","value":9,"faceup":true},{"id":"hearts10","suit":"hearts","color":"red","value":10,"faceup":true},{"id":"hearts11","suit":"hearts","color":"red","value":11,"faceup":true},{"id":"hearts12","suit":"hearts","color":"red","value":12,"faceup":true}],[{"id":"clubs1","suit":"clubs","color":"black","value":1,"faceup":true},{"id":"clubs2","suit":"clubs","color":"black","value":2,"faceup":true},{"id":"clubs3","suit":"clubs","color":"black","value":3,"faceup":true},{"id":"clubs4","suit":"clubs","color":"black","value":4,"faceup":true},{"id":"clubs5","suit":"clubs","color":"black","value":5,"faceup":true},{"id":"clubs6","suit":"clubs","color":"black","value":6,"faceup":true},{"id":"clubs7","suit":"clubs","color":"black","value":7,"faceup":true},{"id":"clubs8","suit":"clubs","color":"black","value":8,"faceup":true},{"id":"clubs9","suit":"clubs","color":"black","value":9,"faceup":true},{"id":"clubs10","suit":"clubs","color":"black","value":10,"faceup":true},{"id":"clubs11","suit":"clubs","color":"black","value":11,"faceup":true},{"id":"clubs12","suit":"clubs","color":"black","value":12,"faceup":true}],[{"id":"diamonds1","suit":"diamonds","color":"red","value":1,"faceup":true},{"id":"diamonds2","suit":"diamonds","color":"red","value":2,"faceup":true},{"id":"diamonds3","suit":"diamonds","color":"red","value":3,"faceup":true},{"id":"diamonds4","suit":"diamonds","color":"red","value":4,"faceup":true},{"id":"diamonds5","suit":"diamonds","color":"red","value":5,"faceup":true},{"id":"diamonds6","suit":"diamonds","color":"red","value":6,"faceup":true},{"id":"diamonds7","suit":"diamonds","color":"red","value":7,"faceup":true},{"id":"diamonds8","suit":"diamonds","color":"red","value":8,"faceup":true},{"id":"diamonds9","suit":"diamonds","color":"red","value":9,"faceup":true},{"id":"diamonds10","suit":"diamonds","color":"red","value":10,"faceup":true},{"id":"diamonds11","suit":"diamonds","color":"red","value":11,"faceup":true},{"id":"diamonds12","suit":"diamonds","color":"red","value":12,"faceup":true}],[{"id":"spades1","suit":"spades","color":"black","value":1,"faceup":true},{"id":"spades2","suit":"spades","color":"black","value":2,"faceup":true},{"id":"spades3","suit":"spades","color":"black","value":3,"faceup":true},{"id":"spades4","suit":"spades","color":"black","value":4,"faceup":true},{"id":"spades5","suit":"spades","color":"black","value":5,"faceup":true},{"id":"spades6","suit":"spades","color":"black","value":6,"faceup":true},{"id":"spades7","suit":"spades","color":"black","value":7,"faceup":true},{"id":"spades8","suit":"spades","color":"black","value":8,"faceup":true},{"id":"spades9","suit":"spades","color":"black","value":9,"faceup":true},{"id":"spades10","suit":"spades","color":"black","value":10,"faceup":true},{"id":"spades11","suit":"spades","color":"black","value":11,"faceup":true},{"id":"spades12","suit":"spades","color":"black","value":12,"faceup":true}]],"tableau":[[{"id":"diamonds13","suit":"diamonds","color":"red","value":13,"faceup":true}],[{"id":"spades13","suit":"spades","color":"black","value":13,"faceup":true}],[{"id":"hearts13","suit":"hearts","color":"red","value":13,"faceup":true}],[{"id":"clubs13","suit":"clubs","color":"black","value":13,"faceup":true}],[],[],[]]}};
     return ret;
}