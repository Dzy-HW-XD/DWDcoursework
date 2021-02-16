<?php

  /////////////////////////////////////
 // index.php for SimpleExample app //
/////////////////////////////////////

// Create f3 object then set various global properties of it
// These are available to the routing code below, but also to any 
// classes defined in autoloaded definitions

$f3 = require('/Users/jlee/AboveWebRoot/fatfree-master-3.7/lib/base.php');

// autoload Controller class(es) and anything hidden above web root, e.g. DB stuff
$f3->set('AUTOLOAD','autoload/;/Users/jlee/AboveWebRoot/autoload/');		

$db = DatabaseConnection::connect();		// defined as autoloaded class in AboveWebRoot/autoload/
$f3->set('DB', $db);

$f3->set('DEBUG',3);		// set maximum debug level
$f3->set('UI','ui/');		// folder for View templates


  /////////////////////////////////////////////
 // Simple Example URL application routings //
/////////////////////////////////////////////

$f3->route('GET /apitest/@tags',
  function ($f3) {
		$url = 'https://api.flickr.com/services/rest';
		$params = array(
			'method' => 'flickr.photos.search',
			'api_key' => '7d579383f7109bf28221618a43210197',
			'tags' => $f3->get('PARAMS.tags'),
			'per_page' => 5
		);
		$options = array('method' => 'GET');
		$url .= '?'.http_build_query($params);

		$result = \Web::instance()->request($url, $options);
		
		$xml_to_parse = $result["body"];
		$parsed_xml = simplexml_load_string($xml_to_parse);
		$items = $parsed_xml->photos->photo; // traversing the xml nodes to count how many photos were retrieved
		$numOfItems = count($items);
	
		if($numOfItems>0){ // yes, some items were retrieved
	
			foreach($items as $current){ 	

				echo "<div><img src=\"http://farm".$current['farm'].".static.flickr.com/".$current['server']."/".$current['id']."_".$current['secret'].".jpg\"/></div>";
				echo "\n<br/>\n";

			} 
		}
  }
);



$f3->route('GET /urltest',
  function ($f3) {
// 	 echo "The first message given on the URL was <font color=red>" . ($f3->exists("GET.message1")?$f3->get("GET.message1"):"not specified") . "</font> ...<br />";
// 	 echo "The second message was <font color=red>" . ($f3->exists("GET.message2")?$f3->get("GET.message2"):"not specified") . "</font>\n";	
	echo "The first message given on the URL was <font color=red>" . (isset($_GET["message1"])?$_GET["message1"]:"not specified") . "</font> ...<br />";
	echo "The second message was <font color=red>" . ($_GET["message2"]?$_GET["message2"]:"not specified") . "</font>\n";
  }
);


//home page (index.html) -- actually just shows form entry page with a different title

$f3->route('GET /',
  function ($f3) {
    $f3->set('html_title','Simple Example Home');
    $f3->set('content','simpleform.html');
    echo Template::instance()->render('layout.html');
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


  ////////////////////////
 // Run the FFF engine //
////////////////////////

$f3->run();

?>

