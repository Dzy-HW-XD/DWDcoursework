<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class SimpleController {
	private $mapper;
	private $loginMapper;	// mapper for login details table
	private $petMapper;		// mapper for virtual pet table
	private $deadinfo;

	public function __construct() {
		global $f3;						// needed for $f3->get()
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"simpleModelP");	// create DB query mapper object// for the "simpleModel" table
		$this->loginMapper = new DB\SQL\Mapper($f3->get('DB'), 'simpleUsers');// mapper for login table
		$this->petMapper = new DB\SQL\Mapper($f3->get('DB'),"simplePet");	// create DB query mapper object
		$this->deadinfo = new DB\SQL\Mapper($f3->get('DB'),"deadinfo");//create DB query mapper object
	}

	
	public function putIntoDatabase($data) {	
		$this->mapper->name = $data["name"];					// set value for "name" field
		$this->mapper->colour = $data["colour"];				// set value for "colour" field
		$this->mapper->pet = $data["pet"];						// set value for "pet" field
		$this->mapper->save();									// save new record with these fields
	}
	public function loginUser($user, $pwd) {		// very simple login -- no use of encryption, hashing etc.
		$auth = new \Auth($this->loginMapper, array('id'=>'username', 'pw'=>'password'));	// fields in table
		return $auth->login($user, $pwd); 			// returns true on successful login
	}

	public function getData() {
		$list = $this->mapper->find();
		return $list;
	}
	
	public function deleteFromDatabase($idToDelete) {
		$this->mapper->load(['id=?', $idToDelete]);				// load DB record matching the given ID
		$this->mapper->erase();									// delete the DB record
	}
	
}

?>
