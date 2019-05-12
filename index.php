<?php
require_once "vendor/autoload.php";

use cheetah\Router;
?>

<?php new Router("routes.json", "config.json"); ?>
