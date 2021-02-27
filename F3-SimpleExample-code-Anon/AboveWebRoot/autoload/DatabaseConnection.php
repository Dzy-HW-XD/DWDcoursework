<?php
// database connection class that will be kept above web root
// can just use a static method here, no need to create an object

class DatabaseConnection {
	
	static function connect() {
		  return new DB\SQL(
			'mysql:host=localhost;port=3306;dbname=ziyudued_demo',
			'ziyudued_demo',
			'Dzy980708+'
		  );
	}
	
}

?>
