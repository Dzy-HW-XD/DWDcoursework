<h1>Images</h1>
<hr />
<a href="<?= $BASE ?>/upload">Upload another</a>
<hr />
<?php foreach (($datalist?:[]) as $item): ?>
	<div id="imgdisplay">
		<p><a href="<?= $BASE ?>/image/<?= $item['picID'] ?>"><img src="<?= $BASE ?>/thumb/<?= $item['picID'] ?>" /></a></p>
		<p><?= $item['picname'] ?>  (<a href="<?= $BASE ?>/delete/<?= $item['picID'] ?>">Delete?</a>)</p>
	</div>
<?php endforeach; ?>
