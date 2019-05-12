<footer>
	<div id="left">
		Nultá hodina 23:70 do konca
	</div>
	<div id="right">
		<?php // if ($this->checkAdmin()): ?>
		<a href="<?= $this->router->url('admin') ?>">Admin</a>
		<?php // endif; ?>
		Jakub Janek 2018/2019
	</div>
</footer>

<div id="message">
<div id="error">
	<?= empty($this->error) ? null : $this->error ?>
	<noscript>
		Táto stránka bude fungovať veľmi obmedzene bez jazyka javascript. <br>
		Je ale stále možné ju prezerať s určitými limitáciami.
	</noscript>
</div>

<div id="success"><?= empty($this->success) ? null : $this->success ?></div>
</div>