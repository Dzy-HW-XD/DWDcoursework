<h1>Upload</h1>
<form name="upload" method="POST" action="<?= $BASE ?>/upload" enctype="multipart/form-data">
	<label for='picfile'>Select image file: </label><input type="file" name="picfile" id="picfile" /><br />
	<label for='picname'>Picture title: </label><input type="text" name="picname" id="picanme" placeholder="Title for image" size="80"/><br />
	<input type="submit" name="submit" value="Submit"/>
</form>
