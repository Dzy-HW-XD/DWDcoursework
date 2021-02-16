<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class SimpleController {
	private $mapper;
	private $loginMapper;	// mapper for login details table
	private $petMapper;		// mapper for virtual pet table

	public function __construct() {
		global $f3;						// needed for $f3->get() 
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"simpleModelP");	// create DB query mapper object
																			// for the "simpleModel" table
		$this->loginMapper = new DB\SQL\Mapper($f3->get('DB'), 'simpleUsers');		// mapper for login table
		$this->petMapper = new DB\SQL\Mapper($f3->get('DB'),"simplePet");	// create DB query mapper object
	}
	
	public function putIntoDatabase($data) {	
		$this->mapper->name = $data["name"];					// set value for "name" field
		$this->mapper->colour = $data["colour"];				// set value for "colour" field
		$this->mapper->save();									// save new record with these fields
	}
	
	public function getData() {
		$list = $this->mapper->find();
		return $list;
	}
	
	public function deleteHandler($idToDelete) {
		$this->mapper->load(['id=?', $idToDelete]);				// load DB record matching the given ID
		$this->mapper->erase();									// delete the DB record
	}

	public function loginUser($user, $pwd) {		// very simple login -- no use of encryption, hashing etc.
		$auth = new \Auth($this->loginMapper, array('id'=>'username', 'pw'=>'password'));	// fields in table
		return $auth->login($user, $pwd); 			// returns true on successful login
	}

	public function getVirtualPet($user) {		// Retrieve the health value of the pet of this user
		$this->petMapper->load(['username LIKE ?',$user]);		// Load one record. We assume only one record (pet) per user. This could be changed.
		return $this->petMapper->health;		// Return the value of the "health" field.
	}

	public function updateVirtualPet($user, $how) {		// $how is either "up" or "down"
		$this->petMapper->load(['username LIKE ?',$user]);		// Load one record. We assume only one record (pet) per user. This could be changed.
		//echo "updateVirtualPet: user is $user, health is $this->petMapper->health";
		if ($how == "up") $this->petMapper->health += rand(0,20);			// Add random value <20 to the health value of the pet
		elseif ($how == "down") $this->petMapper->health -= rand(0,$this->petMapper->health);	// Subtract from the health value
		$this->petMapper->save();				// This will simply save the new value
		// see https://fatfreeframework.com/3.7/databases#TheSmartSQLORM for explanation ...
	}

}

?>
