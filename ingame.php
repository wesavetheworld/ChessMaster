<?php
  require_once("db_config.php");
  require_once("session_config.php");
  include("chess.php");

  if (!isset($_SESSION['username'])) exit;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>

<title>ChessMaster Club - Ingame</title>

<link rel="stylesheet" type="text/css" href="css/common.css"/>
<link rel="stylesheet" type="text/css" href="css/ingame.css"/>

<script language="javascript" type="text/javascript" src="jquery.js"></script>
<script language="javascript" type="text/javascript" src="common.js"></script>

<script language="javascript" type="text/javascript">
var username;
var gameid;
var board;
var boardindex = new Array("A","B","C","D","E","F","G","H");
var white;
var opponent;
var selectedcol = "";
var selectedrow = 0;
var disabled;

function getColIndex(col) {
  for (var i=0; i<boardindex.length; i++) {
    if (boardindex[i] == col)
      return i;
  }
  return -1;
}

function isValidMovePawn(colf,rowf,colt,rowt) {
  var colfindex = getColIndex(colf);
  var coltindex = getColIndex(colt);
  var colnindex;
  var rown;

  var dy = new Array(-1,1);
  var piecef = board[colf][rowf];	  

  if (white) {
    if (rowf+1 == rowt) {
      for (var j=0; j<dy.length; j++) {
	if (colfindex+dy[j] == coltindex) {
	  if (board[boardindex[colfindex+dy[j]]][rowf+1] != "" && board[boardindex[colfindex+dy[j]]][rowf+1].charAt(0) != piecef.charAt(0))
	    return true;
	}
      }

      if (colfindex == coltindex) {
	if (board[boardindex[colfindex]][rowf+1] == "")
	  return true;
      }
    }

    if (rowf == 2 && rowf+2 == rowt && colfindex == coltindex) {
      if (board[boardindex[colfindex]][rowf+2] == "" && board[boardindex[colfindex]][rowf+1] == "")
	return true;
    }
  }
  else {
    if (rowf-1 == rowt) {
      for (var j=0; j<dy.length; j++) {
	if (colfindex+dy[j] == coltindex) {
	  if (board[boardindex[colfindex+dy[j]]][rowf-1] != "" && board[boardindex[colfindex+dy[j]]][rowf-1].charAt(0) != piecef.charAt(0))
	    return true;
	}
      }

      if (colfindex == coltindex) {
	if (board[boardindex[colfindex]][rowf-1] == "")
	  return true;
      }
    }

    if (rowf == 7 && rowf-2 == rowt && colfindex == coltindex) {
      if (board[boardindex[colfindex]][rowf-2] == "" && board[boardindex[colfindex]][rowf-1] == "")
	return true;
    }
  }

  return false;
}

function isValidMoveKing(colf,rowf,colt,rowt) {
  var colfindex = getColIndex(colf);
  var coltindex = getColIndex(colt);

  var dx = new Array(0,1,1,1,0,-1,-1,-1);
  var dy = new Array(-1,-1,0,1,1,1,0,-1);
  var piecef = board[colf][rowf];

  for (var i=0; i<dx.length; i++) {
    if (rowf+dx[i] < 1 || rowf+dx[i] > 8)
      continue;
    if (rowf+dx[i] != rowt)
      continue;

    for (var j=0; j<dy.length; j++) {
      if (colfindex+dy[j] < 0 || colfindex+dy[j] >= 8)
	continue;
      if (colfindex+dy[j] != coltindex)
	continue;
      
      var piecet = board[boardindex[colfindex+dy[j]]][rowf+dx[i]];
      if (piecet != "" && piecef.charAt(0) == piecet.charAt(0)) return false;
      else return true;
    }
  }

  return false;
}

function isValidMoveRook(colf,rowf,colt,rowt) {
  var colfindex = getColIndex(colf);
  var coltindex = getColIndex(colt);

  var piecef = board[colf][rowf];

  if (colf == colt) {
    var rown = rowf-1;
    while (rown>0 && (board[colt][rown] == "" || board[colt][rown].charAt(0) != piecef.charAt(0))) {
      if (rown == rowt) 
	return true;
      if (board[colt][rown] != "")
	break;
      rown--;
    }

    rown = rowf+1;
    while (rown<=8 && (board[colt][rown] == "" || board[colt][rown].charAt(0) != piecef.charAt(0))) {
      if (rown == rowt)
	return true;
      if (board[colt][rown] != "")
	break;
      rown++;
    }
  }

  if (rowf == rowt) {
    var colnindex = colfindex-1;
    while (colnindex>=0 && (board[boardindex[colnindex]][rowt] == "" || board[boardindex[colnindex]][rowt].charAt(0) != piecef.charAt(0))) {
      if (colnindex == coltindex)
	return true;
      if (board[boardindex[colnindex]][rowt] != "")
	break;
      colnindex--;
    }

    colnindex = colfindex+1;
    while (colnindex<8 && (board[boardindex[colnindex]][rowt] == "" || board[boardindex[colnindex]][rowt].charAt(0) != piecef.charAt(0))) {
      if (colnindex == coltindex)
	return true;
      if (board[boardindex[colnindex]][rowt] != "")
	break;
      colnindex++;
    }
  }

  return false;
}

function isValidMoveKnight(colf,rowf,colt,rowt) {
  var colfindex = getColIndex(colf);
  var coltindex = getColIndex(colt);

  var dx = new Array(1,2,2,1,-1,-2,-2,-1);
  var dy = new Array(-2,-1,1,2,2,1,-1,-2);
  var piecef = board[colf][rowf];

  for (var i=0; i<dx.length; i++) {
    if (rowf+dx[i] < 1 || rowf+dx[i] > 8)
      continue;
    if (rowf+dx[i] != rowt)
      continue;

    for (var j=0; j<dy.length; j++) {
      if (colfindex+dy[j] < 0 || colfindex+dy[j] >= 8)
	continue;
      if (colfindex+dy[j] != coltindex)
	continue;
      
      var piecet = board[boardindex[colfindex+dy[j]]][rowf+dx[i]];
      if (piecet != "" && piecef.charAt(0) == piecet.charAt(0)) return false;
      else return true;
    }
  }

  return false;
}

function isValidMoveBishop(colf,rowf,colt,rowt) {
  var colfindex = getColIndex(colf);
  var coltindex = getColIndex(colt);

  var dx = new Array(1,1,-1,-1);
  var dy = new Array(1,-1,1,-1);
  var piecef = board[colf][rowf];

  for (var i=0; i<dx.length; i++) {
    var rown = rowf+dx[i];
    var colnindex = colfindex+dy[i];

    while (rown>0 && rown<=8 && colnindex>=0 && colnindex<8 && (board[boardindex[colnindex]][rown] == "" || board[boardindex[colnindex]][rown].charAt(0) != piecef.charAt(0))) {
      if (rown == rowt && colnindex == coltindex)
	return true;
      if (board[boardindex[colnindex]][rown] != "")
	break;
      rown += dx[i];
      colnindex += dy[i];
    }
  }

  return false;
}

function isValidInitialSelection(col,row) {
  var piece = board[col][row];
  var color;

  if (white) color = 'W';
  else color = 'B';

  if (piece == "" || piece.charAt(0) != color) return false;
  return true;
}

function isValidMove(colf,rowf,colt,rowt) {
  if (!isValidInitialSelection(colf,rowf)) return false;
  
  var piece = board[colf][rowf];
  if (piece.charAt(1) == 'P') {
    if (isValidMovePawn(colf,rowf,colt,rowt)) return true;
    else return false;
  }
  else if (piece.charAt(1) == 'R') {
    if (isValidMoveRook(colf,rowf,colt,rowt)) return true;
    else return false;
  }
  else if (piece.charAt(1) == 'N') {
    if (isValidMoveKnight(colf,rowf,colt,rowt)) return true;
    else return false;
  }
  else if (piece.charAt(1) == 'B') {
    if (isValidMoveBishop(colf,rowf,colt,rowt)) return true;
    else return false;
  }
  else if (piece.charAt(1) == 'K') {
    if (isValidMoveKing(colf,rowf,colt,rowt)) return true;
    else return false;
  }
  else if (piece.charAt(1) == 'Q') {
    if (isValidMoveRook(colf,rowf,colt,rowt) || isValidMoveBishop(colf,rowf,colt,rowt)) 
      return true;
    else
      return false;
  }

  return false;
}

function performMoveOnBoard(colf,rowf,colt,rowt) {
  var piece = board[colf][rowf];
  var colfindex = getColIndex(colf);
  var pieceimage;

  board[colt][rowt] = board[colf][rowf];
  board[colf][rowf] = "";

  document.getElementById("square"+colt+rowt).innerHTML = "<img src='images/"+piece+".gif' />";

  if ((colfindex+rowf)%2 == 0) {
    pieceimage = "W";
  }
  else {
    pieceimage = "B";
  }

  document.getElementById("square"+colf+rowf).innerHTML = "<img src='images/"+pieceimage+".gif' />";
}

function selectSquareOnBoard(col,row) {
  document.getElementById("square"+col+row).innerHTML = "<img src='images/"+board[col][row]+"S.gif' />";
}

function unselectSquareOnBoard(col,row) {
  var piece = board[col][row];
  var pieceimage;

  if (piece == "") {
    if ((getColIndex(col)+row)%2 == 0) pieceimage = "W";
    else pieceimage = "B";
  }
  else {
    pieceimage = piece;
  }
  document.getElementById("square"+col+row).innerHTML = "<img src='images/"+pieceimage+".gif' />";
}

function squareClicked(col,row) {
  if (disabled) return;
  // need to call updateChat() somewhere here

  if (selectedcol == "" || selectedrow == 0) {
    if (isValidInitialSelection(col,row)) {
      selectedcol = col;
      selectedrow = row;
      selectSquareOnBoard(selectedcol,selectedrow);
    }
  }
  else {
    if (isValidMove(selectedcol,selectedrow,col,row)) {
      performMoveOnBoard(selectedcol,selectedrow,col,row);
      unselectSquareOnBoard(selectedcol,selectedrow);
      disableBoard();

      var colf = selectedcol;
      var rowf = selectedrow;
      var colt = col;
      var rowt = row;
	
      selectedcol = "";
      selectedrow = 0;

      var xmlhttp = createXMLHttpRequest();
      xmlhttp.onreadystatechange=function() {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	  var jsonText = xmlhttp.responseText;
	  var jsonObject = eval('('+jsonText+')');

	  if (jsonObject.status == 4) {
	    board = jsonObject.board;
	    updatePiecesOnBoard();

	    if (jsonObject.winner == username)
	      document.getElementById("result").innerHTML = "You won!";
	    else if (jsonObject.winner == opponent)
	      document.getElementById("result").innerHTML = "You lost!";
	    else
	      document.getElementById("result").innerHTML = "Opponent has left";
	  }
	}	
      }
      
      xmlhttp.open("GET","gameplay.php?&t="+Math.random()+"&g="+gameid+"&q=update&colf="+colf+"&rowf="+rowf+"&colt="+colt+"&rowt="+rowt);
      xmlhttp.send();
    }
    else {
      unselectSquareOnBoard(selectedcol,selectedrow);
      selectedcol = "";
      selectedrow = 0;
    }
  } 
}

function playGame() {
  var xmlhttp = createXMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var jsonText = xmlhttp.responseText;
      var jsonObject = eval('('+jsonText+')');
    
      if (jsonObject.status == 1) {
	board=jsonObject.board;
	updatePiecesOnBoard();
      }
      else if (jsonObject.status == 3) {
	enableBoard();
      }
      else if (jsonObject.status == 4) {
	board = jsonObject.board;
	updatePiecesOnBoard();

	if (jsonObject.winner == username)
	  document.getElementById("result").innerHTML = "You won!";
	else if (jsonObject.winner == opponent)
	  document.getElementById("result").innerHTML = "You lost!";
	else
	  document.getElementById("result").innerHTML = "Opponent has left";
      }

      setTimeout(updateChat,500);
    }
  }

  xmlhttp.open("GET","gameplay.php?&t="+Math.random()+"&g="+gameid+"&q=turn",true);
  xmlhttp.send();
}

function postChat() {
  var xmlhttp = createXMLHttpRequest();

  var message = document.getElementById("chatmessage").value;
  xmlhttp.open("POST","chatpost.php",true);
  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  xmlhttp.send("g="+gameid+"&m="+message);

  setTimeout(updateChat,500);
}

function updateChat() {
  var xmlhttp = createXMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var jsonText = xmlhttp.responseText;
      var jsonObject = eval('('+jsonText+')');

      for (var key in jsonObject) {
	if (jsonObject.hasOwnProperty(key)) {
	  insertChatMessage(jsonObject[key].username,jsonObject[key].message);
	}
      }

      setTimeout(playGame,500);
    }
  }

  xmlhttp.open("GET","chatget.php?t="+Math.random()+"&g="+gameid,true);
  xmlhttp.send();
}

function insertChatMessage(user,message) {
  var chatwindow = window.frames["chatframe"];
  var chatdocument = chatwindow.document;

  var newdiv = chatdocument.createElement("div");
  newdiv.innerHTML = "<strong>"+user+":</strong> "+message;

  chatdocument.getElementById("chatcontents").appendChild(newdiv);
  chatwindow.scrollTo(0,chatdocument.getElementById("chatcontents").offsetHeight);

  document.getElementById("chatmessage").value = "";
  document.getElementById("chatmessage").focus();
}

function disableBoard() {
//   document.getElementById("squareA1").disabled = true;
  if ($("#result").html() == "You" || $("#result").html() == "") {
    $("#result").html("Opponent");
    $("#result").fadeOut('slow');
    $("#result").fadeIn('slow');
  }
  disabled = true;
}

function enableBoard() {
//   document.getElementById("board").disabled = false;
  if ($("#result").html() == "Opponent" || $("#result").html() == "") {
    $("#result").html("You");
    $("#result").fadeOut('slow');
    $("#result").fadeIn('slow');
  }
  disabled = false;
}

function updatePiecesOnBoard() {
  var piece;
  var pieceimage;

  for (var i=1; i<=8; i++) {
    for (var j=0; j<8; j++) {
      piece = board[boardindex[j]][i];
      
      if (piece == "") {
	if ((i+j)%2 == 0) pieceimage = "W";
	else pieceimage = "B";
      }
      else {
	pieceimage = piece;
      }

      document.getElementById("square"+boardindex[j]+i).innerHTML = "<img src='images/"+pieceimage+".gif' />";
    }
  }
}

function setupGame() {
  xmlhttp = createXMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var jsonText = xmlhttp.responseText;
      var jsonObject = eval('('+jsonText+')');
      
      username = jsonObject.username;
      gameid = parseInt(jsonObject.gameid);
      board = jsonObject.board;
      opponent = jsonObject.opponent;
      if (username == jsonObject.white) white = true;
      else white = false;

      if (white) {
	$("#piececolor").html("White");
      }
      else {
	$("#piececolor").html("Black");
      }

      $("#boardlegend").html(username+" vs "+opponent);

      updatePiecesOnBoard();
      disableBoard();
      playGame();
    }
  }

  xmlhttp.open("GET","gameplay.php?&t="+Math.random()+"&q=get",true);
  xmlhttp.send(); 
}

function endGame() {
  $.ajax({
    type: "POST",
    url: "gameover.php",
    async: false,
    data: "g="+gameid,
    success: function(data) {
    }
  });
}

function leaveGame() {
  window.location = "profile.php";
}

function mouseOverButton() {
  document.getElementById("quitbutton").style.backgroundColor = "#666666";
  document.getElementById("quitbutton").style.borderTopColor = "#666666";
  document.getElementById("quitbutton").style.borderBottomColor = "#666666";
  document.getElementById("quitbutton").style.borderLeftColor = "#777777";
  document.getElementById("quitbutton").style.borderRightColor = "#888888";
}

function mouseOutButton() {
  document.getElementById("quitbutton").style.backgroundColor = "#999999";
  document.getElementById("quitbutton").style.borderTopColor = "#999999";
  document.getElementById("quitbutton").style.borderBottomColor = "#999999";
  document.getElementById("quitbutton").style.borderLeftColor = "#AAAAAA";
  document.getElementById("quitbutton").style.borderRightColor = "#BBBBBB";
}

function mouseOverSquare(col,row) {
  if (disabled) return;

//   if (selectedcol == "" || selectedrow == 0) {
//     if (isValidInitialSelection(col,row)) {
//       var square = "square"+col+row;
//       document.getElementById(square).innerHTML = "<img src='images/"+board[col][row]+"S.gif' />";
//     }
//   }
}

function mouseOutSquare(col,row) {
  if (disabled) return;

//   if (selectedcol == "" || selectedrow == 0) {
//     if (isValidInitialSelection(col,row)) {
//       var square = "square"+col+row;	
//       document.getElementById(square).innerHTML = "<img src='images/"+board[col][row]+".gif' />";
//     }
//   }
}

</script>
</head>

<body onload="setupGame()" onbeforeunload="endGame()">

<div name="divboard" id="divboard">
<fieldset id="fieldboard" class="gamefield"><legend id="boardlegend"></legend>
<table border="1" cellspacing="0" cellpadding="0" name="board" id="board">
  <tr><td name="squareA8" id="squareA8" onclick="squareClicked('A',8)" onMouseOver="mouseOverSquare('A',8)" onMouseOut="mouseOutSquare('A',8)"></td>
      <td name="squareB8" id="squareB8" onclick="squareClicked('B',8)" onMouseOver="mouseOverSquare('B',8)" onMouseOut="mouseOutSquare('B',8)"></td>
      <td name="squareC8" id="squareC8" onclick="squareClicked('C',8)" onMouseOver="mouseOverSquare('C',8)" onMouseOut="mouseOutSquare('C',8)"></td>
      <td name="squareD8" id="squareD8" onclick="squareClicked('D',8)" onMouseOver="mouseOverSquare('D',8)" onMouseOut="mouseOutSquare('D',8)"></td>
      <td name="squareE8" id="squareE8" onclick="squareClicked('E',8)" onMouseOver="mouseOverSquare('E',8)" onMouseOut="mouseOutSquare('E',8)"></td>
      <td name="squareF8" id="squareF8" onclick="squareClicked('F',8)" onMouseOver="mouseOverSquare('F',8)" onMouseOut="mouseOutSquare('F',8)"></td>
      <td name="squareG8" id="squareG8" onclick="squareClicked('G',8)" onMouseOver="mouseOverSquare('G',8)" onMouseOut="mouseOutSquare('G',8)"></td>
      <td name="squareH8" id="squareH8" onclick="squareClicked('H',8)" onMouseOver="mouseOverSquare('H',8)" onMouseOut="mouseOutSquare('H',8)"></td>
  </tr>	
  <tr><td name="squareA7" id="squareA7" onclick="squareClicked('A',7)" onMouseOver="mouseOverSquare('A',7)" onMouseOut="mouseOutSquare('A',7)"></td>
      <td name="squareB7" id="squareB7" onclick="squareClicked('B',7)" onMouseOver="mouseOverSquare('B',7)" onMouseOut="mouseOutSquare('B',7)"></td>
      <td name="squareC7" id="squareC7" onclick="squareClicked('C',7)" onMouseOver="mouseOverSquare('C',7)" onMouseOut="mouseOutSquare('C',7)"></td>
      <td name="squareD7" id="squareD7" onclick="squareClicked('D',7)" onMouseOver="mouseOverSquare('D',7)" onMouseOut="mouseOutSquare('D',7)"></td>
      <td name="squareE7" id="squareE7" onclick="squareClicked('E',7)" onMouseOver="mouseOverSquare('E',7)" onMouseOut="mouseOutSquare('E',7)"></td>
      <td name="squareF7" id="squareF7" onclick="squareClicked('F',7)" onMouseOver="mouseOverSquare('F',7)" onMouseOut="mouseOutSquare('F',7)"></td>
      <td name="squareG7" id="squareG7" onclick="squareClicked('G',7)" onMouseOver="mouseOverSquare('G',7)" onMouseOut="mouseOutSquare('G',7)"></td>
      <td name="squareH7" id="squareH7" onclick="squareClicked('H',7)" onMouseOver="mouseOverSquare('H',7)" onMouseOut="mouseOutSquare('H',7)"></td>
  </tr>	  
  <tr><td name="squareA6" id="squareA6" onclick="squareClicked('A',6)" onMouseOver="mouseOverSquare('A',6)" onMouseOut="mouseOutSquare('A',6)"></td>
      <td name="squareB6" id="squareB6" onclick="squareClicked('B',6)" onMouseOver="mouseOverSquare('B',6)" onMouseOut="mouseOutSquare('B',6)"></td>
      <td name="squareC6" id="squareC6" onclick="squareClicked('C',6)" onMouseOver="mouseOverSquare('C',6)" onMouseOut="mouseOutSquare('C',6)"></td>
      <td name="squareD6" id="squareD6" onclick="squareClicked('D',6)" onMouseOver="mouseOverSquare('D',6)" onMouseOut="mouseOutSquare('D',6)"></td>
      <td name="squareE6" id="squareE6" onclick="squareClicked('E',6)" onMouseOver="mouseOverSquare('E',6)" onMouseOut="mouseOutSquare('E',6)"></td>
      <td name="squareF6" id="squareF6" onclick="squareClicked('F',6)" onMouseOver="mouseOverSquare('F',6)" onMouseOut="mouseOutSquare('F',6)"></td>
      <td name="squareG6" id="squareG6" onclick="squareClicked('G',6)" onMouseOver="mouseOverSquare('G',6)" onMouseOut="mouseOutSquare('G',6)"></td>
      <td name="squareH6" id="squareH6" onclick="squareClicked('H',6)" onMouseOver="mouseOverSquare('H',6)" onMouseOut="mouseOutSquare('H',6)"></td>
  </tr>	  
  <tr><td name="squareA5" id="squareA5" onclick="squareClicked('A',5)" onMouseOver="mouseOverSquare('A',5)" onMouseOut="mouseOutSquare('A',5)"></td>
      <td name="squareB5" id="squareB5" onclick="squareClicked('B',5)" onMouseOver="mouseOverSquare('B',5)" onMouseOut="mouseOutSquare('B',5)"></td>
      <td name="squareC5" id="squareC5" onclick="squareClicked('C',5)" onMouseOver="mouseOverSquare('C',5)" onMouseOut="mouseOutSquare('C',5)"></td>
      <td name="squareD5" id="squareD5" onclick="squareClicked('D',5)" onMouseOver="mouseOverSquare('D',5)" onMouseOut="mouseOutSquare('D',5)"></td>
      <td name="squareE5" id="squareE5" onclick="squareClicked('E',5)" onMouseOver="mouseOverSquare('E',5)" onMouseOut="mouseOutSquare('E',5)"></td>
      <td name="squareF5" id="squareF5" onclick="squareClicked('F',5)" onMouseOver="mouseOverSquare('F',5)" onMouseOut="mouseOutSquare('F',5)"></td>
      <td name="squareG5" id="squareG5" onclick="squareClicked('G',5)" onMouseOver="mouseOverSquare('G',5)" onMouseOut="mouseOutSquare('G',5)"></td>
      <td name="squareH5" id="squareH5" onclick="squareClicked('H',5)" onMouseOver="mouseOverSquare('H',5)" onMouseOut="mouseOutSquare('H',5)"></td>
  </tr>	  
 <tr> <td name="squareA4" id="squareA4" onclick="squareClicked('A',4)" onMouseOver="mouseOverSquare('A',4)" onMouseOut="mouseOutSquare('A',4)"></td>
      <td name="squareB4" id="squareB4" onclick="squareClicked('B',4)" onMouseOver="mouseOverSquare('B',4)" onMouseOut="mouseOutSquare('B',4)"></td>
      <td name="squareC4" id="squareC4" onclick="squareClicked('C',4)" onMouseOver="mouseOverSquare('C',4)" onMouseOut="mouseOutSquare('C',4)"></td>
      <td name="squareD4" id="squareD4" onclick="squareClicked('D',4)" onMouseOver="mouseOverSquare('D',4)" onMouseOut="mouseOutSquare('D',4)"></td>
      <td name="squareE4" id="squareE4" onclick="squareClicked('E',4)" onMouseOver="mouseOverSquare('E',4)" onMouseOut="mouseOutSquare('E',4)"></td>
      <td name="squareF4" id="squareF4" onclick="squareClicked('F',4)" onMouseOver="mouseOverSquare('F',4)" onMouseOut="mouseOutSquare('F',4)"></td>
      <td name="squareG4" id="squareG4" onclick="squareClicked('G',4)" onMouseOver="mouseOverSquare('G',4)" onMouseOut="mouseOutSquare('G',4)"></td>
      <td name="squareH4" id="squareH4" onclick="squareClicked('H',4)" onMouseOver="mouseOverSquare('H',4)" onMouseOut="mouseOutSquare('H',4)"></td>
  </tr>
  <tr><td name="squareA3" id="squareA3" onclick="squareClicked('A',3)" onMouseOver="mouseOverSquare('A',3)" onMouseOut="mouseOutSquare('A',3)"></td>
      <td name="squareB3" id="squareB3" onclick="squareClicked('B',3)" onMouseOver="mouseOverSquare('B',3)" onMouseOut="mouseOutSquare('B',3)"></td>
      <td name="squareC3" id="squareC3" onclick="squareClicked('C',3)" onMouseOver="mouseOverSquare('C',3)" onMouseOut="mouseOutSquare('C',3)"></td>
      <td name="squareD3" id="squareD3" onclick="squareClicked('D',3)" onMouseOver="mouseOverSquare('D',3)" onMouseOut="mouseOutSquare('D',3)"></td>
      <td name="squareE3" id="squareE3" onclick="squareClicked('E',3)" onMouseOver="mouseOverSquare('E',3)" onMouseOut="mouseOutSquare('E',3)"></td>
      <td name="squareF3" id="squareF3" onclick="squareClicked('F',3)" onMouseOver="mouseOverSquare('F',3)" onMouseOut="mouseOutSquare('F',3)"></td>
      <td name="squareG3" id="squareG3" onclick="squareClicked('G',3)" onMouseOver="mouseOverSquare('G',3)" onMouseOut="mouseOutSquare('G',3)"></td>
      <td name="squareH3" id="squareH3" onclick="squareClicked('H',3)" onMouseOver="mouseOverSquare('H',3)" onMouseOut="mouseOutSquare('H',3)"></td>
  </tr>
  <tr><td name="squareA2" id="squareA2" onclick="squareClicked('A',2)" onMouseOver="mouseOverSquare('A',2)" onMouseOut="mouseOutSquare('A',2)"></td>
      <td name="squareB2" id="squareB2" onclick="squareClicked('B',2)" onMouseOver="mouseOverSquare('B',2)" onMouseOut="mouseOutSquare('B',2)"></td>
      <td name="squareC2" id="squareC2" onclick="squareClicked('C',2)" onMouseOver="mouseOverSquare('C',2)" onMouseOut="mouseOutSquare('C',2)"></td>
      <td name="squareD2" id="squareD2" onclick="squareClicked('D',2)" onMouseOver="mouseOverSquare('D',2)" onMouseOut="mouseOutSquare('D',2)"></td>
      <td name="squareE2" id="squareE2" onclick="squareClicked('E',2)" onMouseOver="mouseOverSquare('E',2)" onMouseOut="mouseOutSquare('E',2)"></td>
      <td name="squareF2" id="squareF2" onclick="squareClicked('F',2)" onMouseOver="mouseOverSquare('F',2)" onMouseOut="mouseOutSquare('F',2)"></td>
      <td name="squareG2" id="squareG2" onclick="squareClicked('G',2)" onMouseOver="mouseOverSquare('G',2)" onMouseOut="mouseOutSquare('G',2)"></td>
      <td name="squareH2" id="squareH2" onclick="squareClicked('H',2)" onMouseOver="mouseOverSquare('H',2)" onMouseOut="mouseOutSquare('H',2)"></td>
  </tr>	
  <tr><td name="squareA1" id="squareA1" onclick="squareClicked('A',1)" onMouseOver="mouseOverSquare('A',1)" onMouseOut="mouseOutSquare('A',1)"></td>
      <td name="squareB1" id="squareB1" onclick="squareClicked('B',1)" onMouseOver="mouseOverSquare('B',1)" onMouseOut="mouseOutSquare('B',1)"></td>
      <td name="squareC1" id="squareC1" onclick="squareClicked('C',1)" onMouseOver="mouseOverSquare('C',1)" onMouseOut="mouseOutSquare('C',1)"></td>
      <td name="squareD1" id="squareD1" onclick="squareClicked('D',1)" onMouseOver="mouseOverSquare('D',1)" onMouseOut="mouseOutSquare('D',1)"></td>
      <td name="squareE1" id="squareE1" onclick="squareClicked('E',1)" onMouseOver="mouseOverSquare('E',1)" onMouseOut="mouseOutSquare('E',1)"></td>
      <td name="squareF1" id="squareF1" onclick="squareClicked('F',1)" onMouseOver="mouseOverSquare('F',1)" onMouseOut="mouseOutSquare('F',1)"></td>
      <td name="squareG1" id="squareG1" onclick="squareClicked('G',1)" onMouseOver="mouseOverSquare('G',1)" onMouseOut="mouseOutSquare('G',1)"></td>
      <td name="squareH1" id="squareH1" onclick="squareClicked('H',1)" onMouseOver="mouseOverSquare('H',1)" onMouseOut="mouseOutSquare('H',1)"></td>
  </tr>	
</table>
</fieldset>
</div>

<div id="right">
<div name="piececolor" id="piececolor"></div>
<div name="result" id="result"></div>
<div name="quitgame" id="quitgame">
<input type="button" onclick="leaveGame()" value="Leave Arena" id="quitbutton" onMouseOver="mouseOverButton()" onMouseOut="mouseOutButton()" />
</div>
<div name="divchat" id="divchat">
<fieldset class="chatfield"><legend>Chat</legend>
  <iframe id="chatframe" name="chatframe" src="chat_contents.html" style="background: white;"></iframe>
  <br />
  <input type="text" name="chatmessage" id="chatmessage" style="width: 250px" onkeydown="if (event.keyCode == 13) document.getElementById('chatsend').click()" />
  <input type="button" value="Send" class="submit" name="chatsend" id="chatsend" onclick="postChat()" />
</fieldset>
</div>
</div>

</body>
</html>