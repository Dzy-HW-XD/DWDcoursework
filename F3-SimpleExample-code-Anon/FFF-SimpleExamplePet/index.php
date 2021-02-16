<?php

  /////////////////////////////////////
 // index.php for SimpleExample app //
/////////////////////////////////////

// Create f3 object then set various global properties of it
// These are available to the routing code below, but also to any 
// classes defined in autoloaded definitions

$f3 = require('../../../AboveWebRoot/fatfree-master-3.7/lib/base.php');

// autoload Controller class(es) and anything hidden above web root, e.g. DB stuff
$f3->set('AUTOLOAD','autoload/;../../../AboveWebRoot/autoload/');

$db = DatabaseConnection::connect();		// defined as autoloaded class in AboveWebRoot/autoload/
$f3->set('DB', $db);

$f3->set('DEBUG',3);		// set maximum debug level
$f3->set('UI','ui/');		// folder for View templates

// Create a session, using the SQL session storage option (for details see https://fatfreeframework.com/3.6/session#SQL)
new \DB\SQL\Session($f3->get('DB'));
// if the SESSION.username variable is not set, set it to 'UNSET'
if (!$f3->exists('SESSION.userName')) $f3->set('SESSION.userName', 'UNSET');

// If a session timeout is needed, see https://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes
// and see https://fatfreeframework.com/3.6/session#stamp for the F3 session method stamp()

  /////////////////////////////////////////////
 // Simple Example URL application routings //
/////////////////////////////////////////////


$f3->route('GET /',
  function ($f3) {
    $f3->set('html_title','Simple Example Home');
    $f3->set('content','simpleform.html');
    echo Template::instance()->render('layout.html');
  }
);

// When using GET, provide a form for the user to log in to a simple F3-managed session
$f3->route('GET /login/@msg',				// @msg is a parameter that tells us which message to give the user
  function($f3) {
    switch ($f3->get('PARAMS.msg')) {		// PARAMS.msg is whatever was the last element of the URL
    	case "err":
    		$msg = "Wrong user name and/or password; please try again.";
    		break;
    	case "lo":
    		$msg = "You have been logged out.";
    		break;
    	default:						// this is the case if neither of the above cases is matched
    		$msg = "Login here";
    }
    $f3->set('html_title', 'Simple Login Form');
    $f3->set('message', $msg);				// set message that will be shown to user in the login.html template
	$f3->set('thisIsLoginPage', 'true');	// set flag that will be tested in layout.html, to say this is login page
    $f3->set('content', 'login.html');		// the login form that will be shown to the user
    echo template::instance()->render('layout.html');
  }
);

// When using POST, do the login and session management
$f3->route('POST /login',
  function($f3) {
    $controller = new SimpleController;
    if ($controller->loginUser($f3->get('POST.uname'), $f3->get('POST.password'))) {		// user is recognised
		$f3->set('SESSION.userName', $f3->get('POST.uname'));			// note that this is a global that will be available elsewhere
//		$f3->set('html_title','Simple Virtual Pet');
//		$f3->set('content','simplepet.html');							// will always go to simplepet after successful login
//		echo template::instance()->render('layout.html');
        header("location:/fatfree/FFF-SimpleExamplePet/simplepet");                  // will always go to simplepet after successful login
    }
    else
    	$f3->reroute('/login/err');		// return to login page with the message that there was an error in the credentials
  }
);

$f3->route('POST /logout',
  function($f3) {
		$f3->set('SESSION.userName', 'UNSET');
    	$f3->reroute('/login/lo');		// return to login page with the message that the user has been logged out
  }
);

// When using GET, provide a form for the user to upload an image via the file input type
$f3->route('GET /simpleform',
  function($f3) {
    $f3->set('html_title','Simple Input Form');
    $f3->set('content','simpleform.html');
    echo template::instance()->render('layout.html');
  }
);

// When using POST (e.g.  form is submitted), invoke the controller, which will process
// any data then return info we want to display. We display
// the info here via the response.html template
$f3->route('POST /simpleform',
  function($f3) {
	$formdata = array();			// array to pass on the entered data in
	$formdata["name"] = $f3->get('POST.name');			// whatever was called "name" on the form
	$formdata["colour"] = $f3->get('POST.colour');		// whatever was called "colour" on the form
		
  	$controller = new SimpleController;
    $controller->putIntoDatabase($formdata);
  	
	$f3->set('formData',$formdata);		// set info in F3 variable for access in response template
	
    $f3->set('html_title','Simple Example Response');
	$f3->set('content','response.html');
	echo template::instance()->render('layout.html');
  }
);

$f3->route('GET /dataView',
  function($f3) {
  	$controller = new SimpleController;
    $alldata = $controller->getData();
    
    $f3->set("dbData", $alldata);
    $f3->set('html_title','Viewing the data');
    $f3->set('content','dataView.html');
    echo template::instance()->render('layout.html');
  }
);

$f3->route('GET /editView',				// exactly the same as dataView, apart from the template used
  function($f3) {
  	$controller = new SimpleController;
    $alldata = $controller->getData();
    
    $f3->set("dbData", $alldata);
    $f3->set('html_title','Viewing the data');
    $f3->set('content','editView.html');
    echo template::instance()->render('layout.html');
  }
);

$f3->route('POST /editView',		// this is used when the form is submitted, i.e. method is POST
  function($f3) {
  	$controller = new SimpleController;
    $controller->deleteHandler($f3->get('POST.toDelete'));		// in this case, delete selected data record

	$f3->reroute('/editView');  }		// will show edited data (GET route)
);

// This is an adaptation of the SimpleForm to implement a very simple kind of virtual pet ...
// First thing we do here is retrieve the pet's health and respond to that.
$f3->route('GET /simplepet',
    function($f3) {
        $user = $f3->get('SESSION.userName');       // This should not be UNSET

        $controller = new SimpleController;
        $controller->updateVirtualPet($user, "down");       // Immediately reduce pet health

        $health = $controller->getVirtualPet($user);
        if ($health >0) $message = "Your pet is alive &#128572;! Health is $health.";       // &#128572; is emoji 😼
        else $message = "Your pet has died &#128575;.";           // &#128575; is 😿 - see https://www.w3schools.com/charsets/ref_emoji.asp
        $f3->set('petMessage', $message);

        $f3->set('html_title','Simple Virtual Pet');
        $f3->set('content','simplepet.html');
        echo template::instance()->render('layout.html');
    }
);

// So this is the POST rule for simplepet.
// We will only be here if the submit button on the form has been clicked, so we
// don't actually need to retrieve any data from the form!
// All we need to do is call the SimpleController method that increments the pet's health.
// So this is much simpler than the /simpleform POST rule.
// Then afterwards we can go straight back to /simplepet
$f3->route('POST /simplepet',
    function($f3) {
        $user = $f3->get('SESSION.userName');       // This should not be UNSET

        $controller = new SimpleController;
        $health = $controller->getVirtualPet($user);
        if ($health >= 0) {
            $controller->updateVirtualPet($user, "up");
        }

        $f3->set('html_title','Simple Pet Response');
        $f3->reroute('/simplepet');
    }
);


  ////////////////////////
 // Run the FFF engine //
////////////////////////

$f3->run();

?>

