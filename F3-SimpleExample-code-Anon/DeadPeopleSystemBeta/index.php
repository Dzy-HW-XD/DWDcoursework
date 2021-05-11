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

$f3->route('GET /UserWelcome',
    function ($f3) {
        $f3->set('html_title','Simple Example Home');
        $f3->set('content','Hello_user.html');
        echo Template::instance()->render('Hello_user.html');
    }
);


$f3->route('POST /Home',
    function($f3) {
        $controller = new SimpleController;
        if ($controller->loginUser($f3->get('POST.Username'), $f3->get('POST.Password'))) {	// user is recognised
            $f3->set('SESSION.userName', $f3->get('POST.Username'));
            // note that this is a global that will be available elsewhere
            header("location:/fatfree/DeadPeopleSystemBeta/UserWelcome");
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
        $controller = new SimpleController;
        $list = $controller->getData();
        $f3->set('result',$list);
        $f3->set('length',sizeof($list));
        echo template::instance()->render('layout.html');
    }
);
$f3->route('GET /UserSearch',
    function ($f3) {
        $f3->set('html_title','Simple Example Home');
        $f3->set('content','Search_user.html');
        $controller = new SimpleController;
        $list = $controller->getData();
        $f3->set('result',$list);
        $f3->set('length',sizeof($list));
        echo Template::instance()->render('layout.html');
    }
);
$f3->route('GET|POST /thumb/@id',
    function($f3) {
        $is = new ImageServer;
        $is->showImage($f3->get('PARAMS.id'), true);
    }
);
$f3->route('POST /UserSearch',
    function($f3) {
        $json_array= array("tattoo"=>$f3->get('POST.tattoo'),
            "birthmark"=>$f3->get('POST.birthmark'),
            "age"=>(int)$f3->get('POST.age'),
            "height"=>(int)$f3->get('POST.height'),
            "gender"=>$f3->get('POST.gender'),
            "skincolor"=>$f3->get('POST.skincolor'),
            "placeofdeath"=>$f3->get('POST.placeofdeath'));
        //echo json_encode($json_array);


        if(!empty($_POST['Search_filiter'])){
            $formdata = array();            // array to pass on the entered data in
            $formdata["age"] = $f3->get('POST.age');            // whatever was called "age" on the form
            $formdata['gender'] = $f3->get('POST.gender');              // whatever was called "gender" on the form
            $formdata['height'] = $f3->get('POST.height');        // whatever was called "height" on the form
            $formdata['tattoo'] = $f3->get('POST.tattoo');        // whatever was called "tattoos" on the form
            $formdata['birthmark'] = $f3->get('POST.birthmark');    // whatever was called "birthmark" on the form
            $formdata['timeofdeath'] = $f3->get('POST.timeofdeath');// whatever was called "timeofdeadth" on the form
            $formdata['skincolor'] = $f3->get('POST.skincolor');// whatever was called "timeofdeadth" on the form
            $formdata['placeofdeath'] = $f3->get('POST.placeofdeath');// whatever was called "timeofdeadth" on the form
            if ($formdata["tattoo"] == "yes") {
                $tattoo = "yes";
            } else {
                $tattoo = "no";
            }

            if ($formdata["birthmark"] == "yes") {
                $birthmark = "yes";
            } else {
                $birthmark = "no";
            }
            $age = (int)$formdata["age"];
            $sex = $formdata["gender"];
            $height = (int)$formdata["height"];

            $timeofdeath = $formdata['timeofdeath'];
            $skincolor = $formdata['skincolor'];
            $placeofdeath = $formdata['placeofdeath'];
            $list = $f3->get('DB')->exec("SELECT * FROM deadinfo WHERE (age < '$age') AND (sex = '$sex') 
                                                                        AND (height < $height)  AND (tattoos='$tattoo') 
                                                                        AND (birthmark='$birthmark') AND (skincolor='$skincolor') 
                                                                        AND (placeofdeath = '$placeofdeath')");
            //
            $f3->set('result', $list); //set $list into variable 'result'
            $f3->set('length', sizeof($list)); //set $list into variable 'result'
            //print_r(gettype($age));
            //print_r($tattoo);
            //print_r($birthmark);
            //print_r($list);
            //print_r($sex);
            //print_r($skincolor);
            //print_r($placeofdeath);

            echo template::instance()->render('Search_user.html'); //return to search.html page
            //$list = $f3->get('DB')->exec("SELECT * FROM deadinfo WHERE (age BETWEEN'$agelow'and'$agehigh') AND (sex='$s') AND (height BETWEEN'$heightlow'and'$heighthigh')  AND (tattoos='$t') AND (birthmark='$b') AND (timeofdeadth='$tofdeadth')");
        }
        elseif(!empty($_POST['Search_text'])){

            //print_r($f3->get('POST.textSearch'));
            $formdata_text = array();            // array to pass on the entered data in
            $formdata_text["textSearch"] = $f3->get('POST.textSearch');            // whatever was called "age" on the form
            $t = $formdata_text["textSearch"];
            $list = $f3->get('DB')->exec("SELECT * FROM deadinfo  WHERE (name LIKE '%$t%') 
                               OR (causeofdeath LIKE '%$t%') 
                               OR (otherinformation LIKE '%$t%') 
                               OR (authority LIKE '%$t%') 
                               OR (skincolor LIKE '%$t%')");
            //print_r(sizeof($list));
            $f3->set('result', $list); //set $list into variable 'result'
            $f3->set('length', sizeof($list)); //set $list into variable 'result'
            echo template::instance()->render('Search_user.html');
            //print_r(gettype($age));
            //print_r($tattoo);
            //print_r($birthmark);
            //print_r($list);
            //print_r($sex);
            //print_r($skincolor);
        }
    }
);
$f3->route('POST /Search',
    function($f3) {
        $json_array= array("tattoo"=>$f3->get('POST.tattoo'),
            "birthmark"=>$f3->get('POST.birthmark'),
            "age"=>(int)$f3->get('POST.age'),
            "height"=>(int)$f3->get('POST.height'),
            "gender"=>$f3->get('POST.gender'),
            "skincolor"=>$f3->get('POST.skincolor'),
            "placeofdeath"=>$f3->get('POST.placeofdeath'));
        //echo json_encode($json_array);
        //$list = $f3->get('DB')->exec("SELECT * FROM picdata_fff WHERE ");


        if(!empty($_POST['Search_filiter'])){
            $formdata = array();            // array to pass on the entered data in
            $formdata["age"] = $f3->get('POST.age');            // whatever was called "age" on the form
            $formdata['gender'] = $f3->get('POST.gender');              // whatever was called "gender" on the form
            $formdata['height'] = $f3->get('POST.height');        // whatever was called "height" on the form
            $formdata['tattoo'] = $f3->get('POST.tattoo');        // whatever was called "tattoos" on the form
            $formdata['birthmark'] = $f3->get('POST.birthmark');    // whatever was called "birthmark" on the form
            $formdata['timeofdeath'] = $f3->get('POST.timeofdeath');// whatever was called "timeofdeadth" on the form
            $formdata['skincolor'] = $f3->get('POST.skincolor');// whatever was called "timeofdeadth" on the form
            $formdata['placeofdeath'] = $f3->get('POST.placeofdeath');// whatever was called "timeofdeadth" on the form
            if ($formdata["tattoo"] == 1) {
                $tattoo = "yes";
            } else {
                $tattoo = "no";
            }

            if ($formdata["birthmark"] == 1) {
                $birthmark = "yes";
            } else {
                $birthmark = "no";
            }
            $age = (int)$formdata["age"];
            $sex = $formdata["gender"];
            $height = (int)$formdata["height"];

            $timeofdeath = $formdata['timeofdeath'];
            $skincolor = $formdata['skincolor'];
            $placeofdeath = $formdata['placeofdeath'];
            $list = $f3->get('DB')->exec("SELECT * FROM deadinfo WHERE (age < '$age') AND (sex = '$sex') 
                                                                        AND (height < $height)  AND (tattoos='$tattoo') 
                                                                        AND (birthmark='$birthmark') AND (skincolor='$skincolor') 
                                                                        AND (placeofdeath = '$placeofdeath')");
            //
            $f3->set('result', $list); //set $list into variable 'result'
            $f3->set('length', sizeof($list)); //set $list into variable 'result'
            //print_r(gettype($age));
            //print_r($tattoo);
            //print_r($birthmark);
            //print_r($list);
            //print_r($sex);
            //print_r($skincolor);
            //print_r($placeofdeath);

            echo template::instance()->render('Search.html'); //return to search.html page
            //$list = $f3->get('DB')->exec("SELECT * FROM deadinfo WHERE (age BETWEEN'$agelow'and'$agehigh') AND (sex='$s') AND (height BETWEEN'$heightlow'and'$heighthigh')  AND (tattoos='$t') AND (birthmark='$b') AND (timeofdeadth='$tofdeadth')");
        }
        elseif(!empty($_POST['Search_text'])){

            //print_r($f3->get('POST.textSearch'));
            $formdata_text = array();            // array to pass on the entered data in
            $formdata_text["textSearch"] = $f3->get('POST.textSearch');            // whatever was called "age" on the form
            $t = $formdata_text["textSearch"];
            $list = $f3->get('DB')->exec("SELECT * FROM deadinfo  WHERE (name LIKE '%$t%') 
                               OR (causeofdeath LIKE '%$t%') 
                               OR (otherinformation LIKE '%$t%') 
                               OR (skincolor LIKE '%$t%')");
            //print_r(sizeof($list));
            $f3->set('result', $list); //set $list into variable 'result'
            $f3->set('length', sizeof($list)); //set $list into variable 'result'
            echo template::instance()->render('Search.html');
            //print_r(gettype($age));
            //print_r($tattoo);
            //print_r($birthmark);
            //print_r($list);
            //print_r($sex);
            //print_r($skincolor);
        }
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
$f3->route('GET /Login',
    function($f3) {
        $f3->set('html_title','Upload Page');
        $f3->set('content','login.html');
        //print_r(json_encode($list));
        echo template::instance()->render('login.html');
    }
);

$f3->route('POST /Login',
    function($f3) {
        $controller = new SimpleController;
        if ($controller->loginUser($f3->get('POST.Username'), $f3->get('POST.Password'))) {	// user is recognised
            $f3->set('SESSION.userName', $f3->get('POST.Username'));
            // note that this is a global that will be available elsewhere
            header("location:/fatfree/DeadPeopleSystemBeta/UserWelcome");
            // will always go to index-user after successful login
        }
        else{
            echo "<script type='text/javascript'>alert('ERROR password or username')</script>";
            header("location:/fatfree/DeadPeopleSystemBeta/Login");
            // return to login page with the message that there was an error in the credentials
        }
    }
);
$f3->route('GET /Upload',
    function($f3) {
        $f3->set('html_title','Upload Page');
        $f3->set('content','Upload.html');
        $list = $f3->get('DB')->exec("SELECT name FROM deadinfo");
        //echo (json_encode($list));
        $f3->set('json_data',json_encode($list));
        //print_r(json_encode($list));
        echo template::instance()->render('layout.html');
    }
);
$f3->route('POST /Upload',
    function($f3) {
        $formdata = array();
        $formdata["name"] = $f3->get('POST.name');
        $formdata["skincolor"] = $f3->get('POST.skincolor');
        $formdata["age"] = $f3->get('POST.age');
        $formdata["height"] = $f3->get('POST.height');
        $formdata["skincolor"] = $f3->get('POST.skincolor');
        $formdata["timeOfdeath"] = $f3->get('POST.timeOfdeath');
        $formdata["placeOfdeath"] = $f3->get('POST.placeOfdeath');
        $formdata["causeOfdeath"] = $f3->get('POST.causeOfdeath');
        $formdata["tattoo"] = $f3->get('POST.tattoo');
        $formdata["birthmark"] = $f3->get('POST.birthmark');
        $formdata["authority"] = $f3->get('POST.authority');
        $formdata["contactnumber"] = $f3->get('POST.contactnumber');
        $formdata["gender"] = $f3->get('POST.gender');
        $formdata["otherinformation"] = $f3->get('POST.otherinformation');
        //$formdata["pictitle"] = $f3->get('POST.pictitle');

        $controller = new SimpleController;
        $controller->putIntoinfoDatabase($formdata);

        $is = new ImageServer;
        if ($filedata = $is->upload()) {						// if this is null, upload failed
            $f3->set('filedata', $filedata);
            echo '<script type="text/javascript">alert("Upload Successfully.")</script>';
            echo template::instance()->render('Hello_user.html');
        }
        $f3->set('formData',$formdata);


    }
);

$f3->route('GET /Signup',
    function($f3) {
        $f3->set('html_title','Upload Page');
        $f3->set('content','Signup.html');
        echo template::instance()->render('layout.html');
    }
);
$f3->route('POST /Signup',
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
                $f3->set('flag','success');		// set info in F3 variable for access in response template
                echo '<script type="text/javascript">alert("Sign Up Successfully.")</script>';
                echo template::instance()->render('Hello.html');
            }
            //if password1 and password2 are different, then alert and refresh page
            else{
                $f3->set('flag','errorpass');		// set info in F3 variable for access in response template
                echo '<script type="text/javascript">alert("Password confirmation Error ")</script>';
                echo template::instance()->render('Hello.html');
            }
        }
        //if checkpolicy is False, then alert and refresh page
        else {
            $f3->set('flag','policy');		// set info in F3 variable for access in response template
            echo '<script type="text/javascript">alert("Please check Service and Privacy Policy.")</script>';
            echo template::instance()->render('Hello.html');
        }
    }
);

////////////////////////
// Run the F3 engine //
////////////////////////

$f3->run();

?>

