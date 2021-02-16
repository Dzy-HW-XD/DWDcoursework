		<table border='1'>
			<tr>
			<th>Firstname</th>
			<th>Lastname</th>
			<th>Age</th>
			<th>Hometown</th>
			<th>Job</th>
			</tr>
			
	<?php foreach (($userTable?:[]) as $record): ?>
          <tr>
		  <td><?= $record['FirstName'] ?></td>
		  <td><?= $record['LastName'] ?></td>
		  <td><?= $record['Age'] ?></td>
		  <td><?= $record['Hometown'] ?></td>
		  <td><?= $record['Job'] ?></td>
		  </tr>	
	<?php endforeach; ?>
		</table>
