<?php


namespace janek\ziackaknizka\controller;


use janek\ziackaknizka\model\Timetable;

class AdminController extends UserController {
	public function __construct() {
		parent::__construct();

		if (false) { //check if not admin
			$this->router->relocate("Location: /");
		}
	}

	public function main() {
		$this->timetables = new Timetable();

		if (isset($_POST['addSubject'])) {
			if ($this->timetables->addSubject($_POST['name']) === false) {
				$this->error = "Nastala chyba pri pridávaní premetu";
			}
		}

		$this->subjects = $this->timetables->getSubjects();

		$this->render('app/view/admin.tpl.php');
	}
}