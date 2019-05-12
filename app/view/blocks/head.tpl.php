<header>
	<a href="<?= $this->router->url('home') ?>">SPŠ Jozefa Murgaša</a>
</header>

<div id="login">
	<?php if (!$this->logged): ?>
		<p>Nie ste prihlásený</p>
		<form method="post">
            <label>
                Užívateľské meno
                <input type="text" name="username" placeholder="Užívateľské meno" required>
            </label>
            <label>Heslo
                <input type="password" name="password" placeholder="Heslo" required>
            </label>
            <button type="submit" name="login">Prihlásiť</button>
		</form>
	<?php else: ?>
		<div id="logged">
			<p><?= $this->user->full_name ?></p>
			<p><a href="<?= $this->router->url('user', $this->user->uid) ?>">Profil</a></p>
			<p><a href="<?= $this->router->url('logout') ?>">Odhlásiť sa</a></p>
		</div>
	<?php endif; ?>
</div>

<nav>
    <?php /*
	<a href="javascript:void(0)" id="menu"><img style="filter: invert(1)" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGhlaWdodD0iMzJweCIgaWQ9IkxheWVyXzEiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDMyIDMyOyIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgMzIgMzIiIHdpZHRoPSIzMnB4IiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48cGF0aCBkPSJNNCwxMGgyNGMxLjEwNCwwLDItMC44OTYsMi0ycy0wLjg5Ni0yLTItMkg0QzIuODk2LDYsMiw2Ljg5NiwyLDhTMi44OTYsMTAsNCwxMHogTTI4LDE0SDRjLTEuMTA0LDAtMiwwLjg5Ni0yLDIgIHMwLjg5NiwyLDIsMmgyNGMxLjEwNCwwLDItMC44OTYsMi0yUzI5LjEwNCwxNCwyOCwxNHogTTI4LDIySDRjLTEuMTA0LDAtMiwwLjg5Ni0yLDJzMC44OTYsMiwyLDJoMjRjMS4xMDQsMCwyLTAuODk2LDItMiAgUzI5LjEwNCwyMiwyOCwyMnoiLz48L3N2Zz4="></a>
	<a href="<?= $this->router->url('index') ?>">Domov</a>
	<a href="<?= $this->router->url('classbook') ?>">Triedna kniha</a>
	<a href="<?= $this->router->url('classes') ?>">Triedy</a>
	<a href="<?= $this->router->url('teachers') ?>">Učitelia</a>
	<a href="<?= $this->router->url('timetables') ?>">Rozvrhy</a>
	<?php // if (!$this->checkAdmin() && isset($this->params['user'])): ?>
		<a href="<?= $this->router->url('grades', ['{username}' => $this->params['user']['username']]) ?>">Známky</a>
	<?php // endif; ?>
	<a href="<?= $this->router->url('gallery') ?>">Galéria</a>
	<a href="<?= $this->router->url('substitution') ?>">Suplovanie</a> */ ?>
</nav>

<script>
    /*document.querySelector('#menu').onclick = () => {
        document.querySelectorAll('nav a:not(#menu)').forEach(element => {
            element.style.display = element.style.display !== 'block' ? 'block' : 'none';
        });
    }*/
</script>