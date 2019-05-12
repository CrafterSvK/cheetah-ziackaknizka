<!doctype html>
<html lang="sk">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
				content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" href="/cdn/style.css">
	<title>SPŠ Jozefa Murgaša</title>
</head>
<body>
<?php include "app/view/blocks/head.tpl.php" ?>
<section id="main">
	<h1>Pridať predmet</h1>

	<form method="post">
		<label>
			Meno predmetu
			<input type="text" name="name">
		</label>
		<label>
			Pridať
			<input type="submit" name="addSubject">
		</label>
	</form>
	<table>
		<thead>
		<tr>
			<td>lid</td>
			<td>name</td>
			<td>type</td>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->subjects as $subject): ?>
			<tr>
				<td><?= $subject['lid'] ?></td>
				<td><?= $subject['name'] ?></td>
				<td><?= $subject['type'] ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</section>
<?php include "app/view/blocks/footer.tpl.php" ?>
</body>
</html>
