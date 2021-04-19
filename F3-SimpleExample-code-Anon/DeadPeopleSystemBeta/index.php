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


  /////////////////////////////////////////////
 // Simple Example URL application routings //
/////////////////////////////////////////////

//home page (index.html) -- actually just shows form entry page with a different title
$f3->route('GET /',
  function ($f3) {
    $f3->set('html_title','Simple Example Home');
    $f3->set('content','Hello.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /Home',
    function ($f3) {
        $f3->set('html_title','Simple Example Home');
        $f3->set('content','Hello.html');
        echo Template::instance()->render('layout.html');
    }
);


$f3->route('POST /Home',
    function($f3) {
        $controller = new SimpleController;
        if ($controller->loginUser($f3->get('POST.Username'), $f3->get('POST.Password'))) {	// user is recognised
            $f3->set('SESSION.userName', $f3->get('POST.Username'));
            // note that this is a global that will be available elsewhere
            header("location:/fatfree/DeadPeopleSystemBeta/Upload");
            // will always go to index-user after successful login
        }
        else{
            header("location:/fatfree/DeadPeopleSystemBeta/Home");
            echo "<script type='text/javascript'>alert('ERROR password or username')</script>";
            // return to login page with the message that there was an error in the credentials
        }
    }
);
// When using GET, provide a form for the user to upload an image via the file input type
$f3->route('GET /Search',
    function($f3) {
        $f3->set('html_title','Search Page');
        $f3->set('content','Search.html');
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
      $formdata["pet"] = $f3->get('POST.pet');		// whatever was called "colour" on the form


  	$controller = new SimpleController;
    $controller->putIntoDatabase($formdata);
  	
	$f3->set('formData',$formdata);		// set info in F3 variable for access in response template
	
    $f3->set('html_title','Simple Example Response');
	$f3->set('content','response.html');
	echo template::instance()->render('layout.html');
  }
);

$f3->route('GET /Upload',
  function($f3) {
    $f3->set('html_title','Upload Page');
    $f3->set('content','Upload.html');
    echo template::instance()->render('layout.html');
  }
);

$f3->route('GET /Signup',
    function($f3) {
        $f3->set('html_title','Sign Up Page');
        $f3->set('content','Signup.html');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('GET /editView',	// exactly the same as dataView, apart from the template used
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
    $controller->deleteFromDatabase($f3->get('POST.toDelete'));		// in this case, delete selected data record

	$f3->reroute('/editView');  }		// will show edited data (GET route)
);


  ////////////////////////
 // Run the F3 engine //
////////////////////////

$f3->run();

?>

