<?php


namespace janek\ziackaknizka\controller;


use janek\ziackaknizka\model\Timetable;

class UserController extends LoginController {
	protected $timetables;
	protected $lessons;
	protected $subjects;

	public function __construct() {
		parent::__construct();

		if (!$this->logged) header("Location: /");
	}

	public function book() {
		$this->timetables = Timetable::fromUser($this->user);
		$this->lessons = $this->timetables->getLessons();

		require "app/view/book.tpl.php";
	}

	public function logout() {
		unset($_SESSION['id']);

		header("Location: /");
	}


}