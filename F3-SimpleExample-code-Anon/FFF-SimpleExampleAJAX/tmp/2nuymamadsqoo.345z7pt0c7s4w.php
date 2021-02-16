<h2>Current DB listing:</h2>

<form action="<?= $BASE ?>/dataview" method="post">
	<input type="submit" name="submit" value="Filter:">
	<select name="field">
		<option value="name">name</option>
		<option value="colour">colour</option>
	</select> 
	with 
	<input type="text" name="term">
</form>

<table>
	<tr>
		<th>Name</th><th>Colour</th>
	</tr>
	<?php foreach (($dbData?:[]) as $record): ?>
		<tr>
			<td><?= trim($record['name']) ?></td>
			<td><?= trim($record['colour']) ?></td>
		</tr>
	<?php endforeach; ?>
</table>

<p><a href="<?= $BASE ?>/simpleform">Add another record</a></p>
<p><a href="<?= $BASE ?>/editView">Delete records</a></p>
