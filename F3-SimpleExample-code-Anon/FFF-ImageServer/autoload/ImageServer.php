<?php
// Class that provides methods for working with the images.  The constructor is empty, because
// initialisation isn't needed; in fact it probably never really needs to be instanced and all
// could be done with static methods.
class ImageServer {
	private $filedata;
	private $uploadResult = "Upload failed! (unknown reason) <a href=''>Return</a>";
	private $pictable = "picdata_fff";
	private $thumbsize = 200;			// max width/height of thumbnail images
// 	private $acceptedTypes = ["image/jpeg", "image/png", "image/gif", "image/tiff", "image/svg+xml"];
	private $acceptedTypes = ["image/jpeg", "image/png", "image/gif"];	// tiff and svg removed: image processing code can't handle them

	public function __construct() {}
	
	// abandoned experiment with a ::instance() method similar to the ones used elsewhere in F3
	// -- it works, but seems to have no advantages
	// 	public static function instance() {
	// 		return new self;
	// 	}

	// Puts the file data into the DB
	public function store() {
		global $f3;			// because we need f3->get()
		$pic = new DB\SQL\Mapper($f3->get('DB'),$this->pictable);	// create DB query mapper object
		$pic->picname = $this->filedata["title"];
		$pic->picfile = $this->filedata["name"];
		$pic->pictype = $this->filedata["type"];
		$pic->save();
	}

	// Upload file, using callback to get data, then copy data into local array.  
	// Call store() to store data, call createThumbnail(), add thumb name to the
	// array then return the array
	public function upload() {
		global $f3;		// so that we can call functions like $f3->set() from inside here

		$overwrite = false; // set to true, to overwrite an existing file; Default: false
		$slug = true; // rename file to filesystem-friendly version

		Web::instance()->receive(function($file,$anything){
				/* looks like:
				  array(5) {
					  ["name"] =>     string(19) "csshat_quittung.png"
					  ["type"] =>     string(9) "image/png"
					  ["tmp_name"] => string(14) "/tmp/php2YS85Q"
					  ["error"] =>    int(0)
					  ["size"] =>     int(172245)
					}
				*/
				// $file['name'] already contains the slugged name now

				$this->filedata = $file;		// export file data to outside this function

				// maybe you want to check the file size
				if($this->filedata['size'] > (2 * 1024 * 1024)) {		// if bigger than 2 MB
					$this->uploadResult = "Upload failed! (File > 2MB)  <a href=''>Return</a>";
					return false; // this file is not valid, return false will skip moving it
				}
				if(!in_array($this->filedata['type'], $this->acceptedTypes)) {		// if not an approved type 
					$this->uploadResult = "Upload failed! (File type not accepted)  <a href=''>Return</a>";
					return false; // this file is not valid, return false will skip moving it
				}
				// everything went fine, hurray!
				$this->uploadResult = "success";
				return true; // allows the file to be moved from php tmp dir to your defined upload dir
			},
			$overwrite,
			$slug
		);
	// 	var_dump($this->filedata);
 
 		if ($this->uploadResult != "success") {
 			echo $this->uploadResult;				// ideally this might be output from index.php
 			return null;
 		}
 
		$picname = $f3->get('POST.picname');
		$this->filedata["title"] = $picname;		// add the title to filedata for later use
		$this->store();
		$this->createThumbnail($this->filedata["name"], $f3->get("UPLOADS") . "/" .$this->thumbFile($this->filedata["name"]), basename($this->filedata["type"]));
		$this->filedata["thumbNail"] = $this->thumbFile($this->filedata["name"]);		// add the thumbnail to filedata for later use

		return $this->filedata;
	}


	// This just returns all the data we have about images in the DB, just as an array.
	// If given no argument, it uses the default argument, 0, and in this case it returns data about all images.
	// If given an image ID as argument (there can be no image with ID 0), it returns data only about that image.
	public function infoService($picID=0) {
		global $f3;
		$returnData = array();
		$pic=new DB\SQL\Mapper($f3->get('DB'),$this->pictable);	// create DB query mapper object
		$list = $pic->find();
		if ($picID == 0) {
			foreach ($list as $record) {
				$recordData = array();
				$recordData["picfile"] = $record["picfile"];
				$recordData["pictype"] = $record["pictype"];
				$recordData["picname"] = $record["picname"];
				$recordData["picID"] = $record["id"];
				$recordData["thumbNail"] = $f3->get('UPLOADS').$this->thumbFile($pic["picfile"]);
				array_push(	$returnData, $recordData);
			}
			return $returnData;
		}
		$pic->load(['id=?',$picID]);
		$recordData = array();
		$recordData["picfile"] = $pic["picfile"];
		$recordData["pictype"] = $pic["pictype"];
		$recordData["picname"] = $pic["picname"];
		$recordData["picID"] = $pic["id"];
		return $recordData;
	}

	// Delete data record about the image, and remove its file and thumbnail file
	public function deleteService($picID) {
		global $f3;
		$pic=new DB\SQL\Mapper($f3->get('DB'),$this->pictable);	// create DB query mapper object
		$pic->load(['id=?',$picID]);							// load DB record matching the given ID
		unlink($pic["picfile"]);										// remove the image file
		unlink($f3->get('UPLOADS').$this->thumbFile($pic["picfile"]));	// remove the thumbnail file
		$pic->erase();													// delete the DB record
	}


	// A method that finds the file for a given image ID, and ouputs the raw content of it with a 
	// suitable header, e.g. so that <img src="/image/ID" /> will work.
	// This is necessary because image files are stored above the web root, so have no direct URL.
	public function showImage($picID, $thumb) {
		global $f3;
		$pic=new DB\SQL\Mapper($f3->get('DB'),$this->pictable);	// create DB query mapper object
		$pic->load(['id=?',$picID]);							// load DB record matching the given ID
		$fileToShow = ($thumb?$f3->get('UPLOADS').$this->thumbFile($pic["picfile"]):$pic["picfile"]);
		$fileType = ($thumb?"image/jpeg":$pic["pictype"]);		// thumb is always jpeg
		header("Content-type: " . $fileType);		// write out the image file http header
		readfile($fileToShow);						// write out raw file contents (image data)
	}
	
	
	// Create the name of the thumbnail file for the given image file
	// -- just by adding "thumb-" to the start, but bearing in mind that it
	// will always be a .jpg file.
	private function thumbFile($picfile) {
			return "thumb-".pathinfo($picfile,PATHINFO_FILENAME).".jpg";
	}
	
	// This creates the actual thumbnail by resampling the image file to the size given by the thumbsize variable.
	// We can easily change this.  PHP has very rich image processing functionality; this is a simple example.
    // Based on code from PHP manual for imagecopyresampled()
    // NB this is old code: most of these functions also have F3 wrappers, which might be neater here ...
	private function createThumbnail($filename, $thumbfile, $type) {
	  // Set a maximum height and width
	  $width = $this->thumbsize;
	  $height = $this->thumbsize;
	  
	  // Get new dimensions
	  list($width_orig, $height_orig) = getimagesize($filename);
	  
	  $ratio_orig = $width_orig/$height_orig;
	  
	  if ($width/$height > $ratio_orig) {
		 $width = $height*$ratio_orig;
	  } else {
		 $height = $width/$ratio_orig;
	  }
	  
	  // Resample
	  $image_p = imagecreatetruecolor($width, $height);
	  switch ($type) {
		  case "jpeg":
		  	$image = imagecreatefromjpeg($filename);
		  	break;
		  case "png":
		  	$image = imagecreatefrompng($filename);
		  	break;
		  case "gif":
		  	$image = imagecreatefromgif($filename);
		  	break;
		  default:
		  	$data = file_get_contents($filename);
		  	$image = imagecreatefromstring($data);
	  }
	  imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	  
	  // Output
	  // Notice this is always a jpeg image.  We could also have made others, but this seems OK.
	  imagejpeg($image_p, $thumbfile);	
	}	
}
?>
