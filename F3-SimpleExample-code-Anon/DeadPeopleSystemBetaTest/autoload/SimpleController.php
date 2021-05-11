<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class SimpleController {
	private $mapper;
	private $loginMapper;	// mapper for login details table
	private $petMapper;		// mapper for virtual pet table
	private $deadinfo;


	private $filedata;
	private $uploadResult = "Upload failed! (unknown reason) <a href=''>Return</a>";
	private $pictable = "picdata_fff";
	private $thumbsize = 200;			// max width/height of thumbnail images
// 	private $acceptedTypes = ["image/jpeg", "image/png", "image/gif", "image/tiff", "image/svg+xml"];
	private $acceptedTypes = ["image/jpeg", "image/png", "image/gif"];	// tiff and svg removed: image processing code can't handle them

	public function __construct() {
		global $f3;						// needed for $f3->get()
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"simpleModelP");	// create DB query mapper object// for the "simpleModel" table
		$this->loginMapper = new DB\SQL\Mapper($f3->get('DB'), 'simpleUsers');// mapper for login table
		$this->petMapper = new DB\SQL\Mapper($f3->get('DB'),"simplePet");	// create DB query mapper object
		$this->deadinfo = new DB\SQL\Mapper($f3->get('DB'),"deadinfo");//create DB query mapper object
	}
	public function putIntoinfoDatabase($data) {
		$this->deadinfo->name = $data["name"];					// set value for "name" field
		$this->deadinfo->age = $data["age"];				// set value for "age" field
		$this->deadinfo->sex = $data["gender"];// set value for "gender" field
		$this->deadinfo->height = $data["height"];// set value for "height" field
		$this->deadinfo->tattoos = $data["tattoo"];// set value for "tattoo" field
		$this->deadinfo->birthmark = $data["birthmark"];// set value for "birthmark" field
		$this->deadinfo->timeofdeath = $data["timeOfdeath"];// set value for "timeOfdeath" field

		$this->deadinfo->skincolor = $data["skincolor"];// set value for "skincolor" field
		$this->deadinfo->placeofdeath = $data["placeOfdeath"];// set value for "placeOfdeath" field
		$this->deadinfo->causeofdeath = $data["causeOfdeath"];// set value for "causeOfdeath" field
		$this->deadinfo->authority = $data["authority"];// set value for "authority" field
		$this->deadinfo->contactnumber = $data["contactnumber"];// set value for "contactnumber" field
		$this->deadinfo->otherinformation = $data["otherinformation"];// set value for "otherinformation" field
		//$this->deadinfo->pictitle = $data["pictitle"];
		$this->deadinfo->save();									// save new record with these fields
	}

	public function putIntoAdmDatabase($data) {
		$this->loginMapper->username = $data["username"];					// set value for "username" field
		$this->loginMapper->password = $data["password"];				// set value for "password" field
		$this->loginMapper->save();									// save new record with these fields
	}
	public function loginUser($user, $pwd) {		// very simple login -- no use of encryption, hashing etc.
		$auth = new \Auth($this->loginMapper, array('id'=>'username', 'pw'=>'password'));	// fields in table
		return $auth->login($user, $pwd); 			// returns true on successful login
	}

	public function getData() {
		$list = $this->deadinfo->find();
		return $list;
	}
	
	public function deleteFromDatabase($idToDelete) {
		$this->mapper->load(['id=?', $idToDelete]);				// load DB record matching the given ID
		$this->mapper->erase();									// delete the DB record
	}
}

?>
