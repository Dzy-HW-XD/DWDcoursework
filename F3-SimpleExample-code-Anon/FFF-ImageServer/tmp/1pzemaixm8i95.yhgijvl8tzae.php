<h1>Uploaded</h1>
<p>Thanks: your image is uploaded.</p>
<p>Data:</p>
<ul>
<li>Name: <?= basename($filedata['name']) ?></li>
<li>Size: <?= $filedata['size'] ?></li>
<li>Type: <?= $filedata['type'] ?></li>
<li>Title: <?= $filedata['title'] ?></li>
<li>Thumbnail: <?= $filedata['thumbNail'] ?></li>
</ul>
<hr />
<ul>
<li><a href="<?= $BASE ?>/viewimages">View the images</a></li>
<li><a href="">Upload another</a></li>	<!-- The current URL actually is /upload ... -->
</ul>
