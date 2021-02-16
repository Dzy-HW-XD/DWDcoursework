<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.
// NB this is the version for the AJAX examples, hence getUserTable() etc.

class SimpleController {
	private $mapper;
	
	public function __construct() {
		global $f3;						// needed for $f3->get() 
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"simpleModel");	// create DB query mapper object
																			// for the "simpleModel" table
		$this->ajax_mapper = new DB\SQL\Mapper($f3->get('DB'),"jr_quahog");	// create DB query mapper object
																			// for the "jr_quahog" table
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
		
	public function search($field, $term) {
		$list = $this->mapper->find([$field . " LIKE ?", "%" . $term . "%"]);
		return $list;
	}
	
	public function deleteHandler($idToDelete) {
		$this->mapper->load(['id=?', $idToDelete]);				// load DB record matching the given ID
		$this->mapper->erase();									// delete the DB record
	}
	
// ======  Functions below here defined for AJAX example  =========================================

	public function getUserTable($id) {
		$list = $this->ajax_mapper->find(["id = ?", $id]);
		return $list;
	}	

	public function getUserTableFromStr($str) {
		$list = $this->ajax_mapper->find(["LastName LIKE ?", "%" . $str . "%"]);
		return $list;
	}
	
	public function getUserHint($str) {
		$list = $this->ajax_mapper->find(["LastName LIKE ?", "%" . $str . "%"]);		
		$hint = "";
		foreach ($list as $obj) {
			$arr = $this->ajax_mapper->cast($obj);		// turn mapper object into an associative array
			// for the first result, we simply add the result
			if ($hint=="") $hint = $arr["FirstName"] . " " . $arr["LastName"];
			// for subsequent results we add in a comma
			else $hint .= ", " . $arr["FirstName"] . " " . $arr["LastName"];
		}
		return $hint;
	}	

}

?>
