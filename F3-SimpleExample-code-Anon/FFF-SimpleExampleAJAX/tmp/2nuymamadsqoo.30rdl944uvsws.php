
<html>
<head>
<title>AJAX 1</title>

<script src="http://localhost:8888/fatfree/js/jquery.min.js"></script>
<!-- 
	using a local copy of jQuery ~~ could alternatively use an online one
 -->
 
<script>	
/*
This code adapted from Jules' xmlhttp code

Explanation: When the query is sent from the JavaScript to the PHP file, the following happens:

    PHP opens a connection to a MySQL server
    The correct person is found
    An HTML table is created, filled with data, and sent back to the "txtHint" placeholder

*/


// the function is called when the user changes the value of the form select below
function showUser(str)
{
// if the string == empty then the first option was selected
// so we set the HTML inside our Div to be empty, then quit the script
if (str=="")
  {
  $("#responseTable").html("");
  return;
  }
 
   	// create jQuery AJAX call -- can always use the same pattern as here
	$.ajax({
		type: 'GET',	// needs to be the http method that the PHP code is expecting
		url: "<?= $BASE ?>/ajaxEx/user/" + str,		// adding data param for F3
		success: function(response) {	// anonymous function to call if AJAX request successful
			$("#responseTable").html(response);		
		},
		failure: function() {	// anonymous function to call if AJAX request unsuccessful
			console.log("ajax failure!");
		},
//		data: "q=" + str,		// The query string for the request; don't need one with F3
	});
}


/*
This code adapted from http://www.w3schools.com/php/php_ajax_php.asp

Explanation: If there is any text sent from the JavaScript the following happens:

    Find a name matching the characters sent from the JavaScript
    If no match were found, set the response string to "no suggestion"
    If one or more matching names were found, set the response string to all these names
    The response is sent to the "txtHint" placeholder

*/

// see the annotations above for an explanation of this code, since it's mostly the same
function showHint(str)
{
	console.log("showHint(), str is", str);
if (str.length==0)
  {
  $("#txtHint").html("");;
  return;
  }
  
	var request = $.ajax({
		type: 'GET',	// needs to be the http method that the PHP code is expecting
		url: "<?= $BASE ?>/ajaxEx/hint/" + str,
		success: function(response) {
			$("#txtHint").html(response);		
		},
		failure: function() {
			console.log("ajax failure!");
		},
//		data: "q=" + str,
	});
}
</script>

</head>
<body>
<h2>Example 1</h2>
<!-- lookup in db based on select > option -->
<form>
<select name="users" onchange="showUser(this.value)">
<option value="">Select a person:</option>
<option value="1">Peter Griffin</option>
<option value="2">Lois Griffin</option>
<option value="4">Glenn Quagmire</option>
<option value="3">Joseph Swanson</option>
</select>
</form>
<br />
<div id="responseTable">Person info will be listed here.</div>

<h2>Example 2</h2>
<!-- lookup in db based on input -->
<p>Start typing a name in the input field below:</p>
<form action="<?= $BASE ?>/ajaxEx/user/" method="post">
Last name: <input type="text" name="LastName" onkeyup="showHint(this.value)" size="20" />
<input type="submit" name="submit" value="submit"/>
</form>
<p>Suggestions: <span id="txtHint"></span></p>


</body>
</html>
