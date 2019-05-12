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
	<h1><?= $this->renderUser->full_name ?></h1>
	<br>
	<?php include "app/view/blocks/timetable.tpl.php" ?>
	<br>
	<?php if ($this->renderUser->uid === $this->user->uid): ?>
		<table>
			<thead>
			<tr>
				<td>Predmet</td>
				<td>Známky</td>
				<td>Učiteľ</td>
			</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	<?php endif; ?>
</section>
<?php include "app/view/blocks/footer.tpl.php" ?>
</body>
</html>
