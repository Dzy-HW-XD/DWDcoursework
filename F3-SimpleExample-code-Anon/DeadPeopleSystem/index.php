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
    $f3->set('content','UserSelect.html');
    echo Template::instance()->render('index.html');
  }
);

$f3->route('POST /index',
    function($f3) {
        $controller = new SimpleController;
        if ($controller->loginUser($f3->get('POST.Username'), $f3->get('POST.Password'))) {	// user is recognised
            $f3->set('SESSION.userName', $f3->get('POST.Username'));
            // note that this is a global that will be available elsewhere
            header("location:/fatfree/DeadPeopleSystem/index-user");
            // will always go to index-user after successful login
        }
        else{
            echo "<script type='text/javascript'>alert('ERROR password or username')</script>";
            // return to login page with the message that there was an error in the credentials
            echo Template::instance()->render('index.html');
        }
    }
);
$f3->route('GET /index-user',
    function ($f3) {
        echo Template::instance()->render('index_user.html');
    }
);

$f3->route('GET /user-search',
    function ($f3) {
        $controller = new SimpleController;
        $list = $controller->getData();

        $f3->set('result',$list);
        echo Template::instance()->render('index_usersearch.html');
    }
);
$f3->route('GET /index-register',
    function ($f3) {
        echo Template::instance()->render('index_register.html');
    }
);
$f3->route('POST /index-register',
    function($f3) {
            if($f3->get('POST.checkpolicy')){ // Check whether checkpolicy is true
                if($f3->get('POST.password1')==$f3->get('POST.password2')){//Check whether pd1 and pd2 are same
                    //if checkpolicy is true and pd1,pd2 are same
                    //then insert Username and password into database
                    $formdata = array();			                    // array to pass on the entered data in
                    $formdata["username"] = $f3->get('POST.Username');	// whatever was called "Username" on the form
                    $formdata["password"] = $f3->get('POST.password1');// whatever was called "password1" on the for

                    $controller = new SimpleController;

                    $controller->putIntoAdmDatabase($formdata);//insert formadate into database

                    $f3->set('formData',$formdata);		// set info in F3 variable for access in response template

                    echo template::instance()->render('index_regsuccess.html');
                }
                //if password1 and password2 are different, then alert and refresh page
                else{
                    echo '<script type="text/javascript">alert("ERROR PASSWORD")</script>';
                    echo template::instance()->render('index_register.html');
                }
            }
            //if checkpolicy is False, then alert and refresh page
            else {
                echo '<script type="text/javascript">alert("Please check Service and Privacy Policy.")</script>';
                echo template::instance()->render('index_register.html');
            }
    }
);
$f3->route('GET /index-regsuccess',
    function ($f3) {
        echo Template::instance()->render('index_regsuccess.html');
    }
);
$f3->route('GET /search',
    function ($f3) {
        $f3->set('html_title','Home Page');
        $f3->set('content','search.html');
        $controller = new SimpleController;
        $list = $controller->getData();

        $f3->set('result',$list);
        echo Template::instance()->render('search.html');
    }
);
$f3->route('POST /search',
    function($f3) {

        $formdata = array();			// array to pass on the entered data in
        $formdata["name"] = $f3->get('POST.name');			// whatever was called "name" on the form
        $formdata["age"] = $f3->get('POST.age');            // whatever was called "age" on the form
        $formdata['sex']=$f3->get('POST.sex');              // whatever was called "sex" on the form
        $formdata['height']=$f3->get('POST.height');        // whatever was called "height" on the form
        $formdata['tattoos'] = $f3->get('POST.tattoos');        // whatever was called "tattoos" on the form
        $formdata['birthmark'] = $f3->get('POST.birthmark');    // whatever was called "birthmark" on the form
        $formdata['timeofdeadth'] = $f3->get('POST.timeofdeadth');// whatever was called "timeofdeadth" on the form

        $s = $formdata['sex'];
        $t = $formdata['tattoos'];
        $b = $formdata['birthmark'];
        $tofdeadth = $formdata['timeofdeadth'];

        switch ($formdata['age'])   //Initialize $agelow and $agehigh which will be used in sql query according to the condition $formdata['age']
        {
            case "0～20":
                $agelow = 0;
                $agehigh = 20;
                break;
            case "20～50":
                $agelow = 20;
                $agehigh = 50;
                break;
            case "50～70":
                $agelow = 50;
                $agehigh = 70;
                break;
            case "70+":
                $agelow = 70;
                $agehigh = 100;
                break;
        }
        switch ($formdata['height'])//Initialize $heightlow and $heighthigh which will be used in sql query according to the condition $formdata['height']
        {
            case "000～160cm":
                $heightlow = 0;
                $heighthigh = 160;
                break;
            case "160～190cm":
                $heightlow = 160;
                $heighthigh = 190;
                break;
            case "190+cm":
                $heightlow = 190;
                $heighthigh = 220;
                break;
        }
        // execute SQL query using $f3->get('DB')->exec() function,constrained by variables $agelow,$agehigh,$heightlow,$heighthigh,$s,$t,$b
        $list = $f3->get('DB')->exec("SELECT * FROM deadinfo WHERE (age BETWEEN'$agelow'and'$agehigh') AND (sex='$s') AND (height BETWEEN'$heightlow'and'$heighthigh')  AND (tattoos='$t') AND (birthmark='$b') AND (timeofdeadth='$tofdeadth')");

        $f3->set('result',$list); //set $list into variable 'result'
        echo template::instance()->render('search.html'); //return to search.html page
    }

);

$f3->route('GET /index',
    function ($f3) {
        $f3->set('html_title','Home Page');
        $f3->set('content','index.html');
        $f3->set('icofont','./icofont/icofont.min.css');
        $f3->set('bootstrap','./plugins/css/bootstrap.min.css');
        $f3->set('owl','./plugins/css/owl.css');
        $f3->set('fancybox','./plugins/css/jquery.fancybox.min.css');
        $f3->set('revealer','./plugins/css/revealer.css');
        $f3->set('animate','./plugins/css/animate.css');
        $f3->set('index_style','./css/styles.css');
        $f3->set('index_responsive','./css/responsive.css');


        echo Template::instance()->render('index.html');
    }
);






  ////////////////////////
 // Run the FFF engine //
////////////////////////

$f3->run();

?>

