<?php

namespace janek\ziackaknizka\controller;

use cheetah\Controller;
use cheetah\Database;
use janek\ziackaknizka\model\Timetable;
use janek\ziackaknizka\model\User;

class LoginController extends Controller {
	protected $logged = false;
	protected $user;
	protected $lessons;
	protected $timetable;
	protected $classroom;
	protected $subjects;

	public $error = '';
	public $success = '';

	public function __construct() {
		$this->logged = isset($_SESSION['id']);

		if (isset($_POST['login']) && !$this->logged) {
			$login = User::login($_POST['username'], $_POST['password']);

			if ($login === false) {
				$this->error = "Neplatné meno alebo heslo";
			} else {
				$this->user = $login;

				$_SESSION['id'] = $this->user->uid;

				$this->logged = true;
			}
		}

		if ($this->logged) {
			$this->user = new User($_SESSION['id']);
		}

		/*for ($i = 0; $i < 5; $i++) {
			for ($j = 0; $j < 10; $j++) {
				$db = new Database();

				$db->insert('hour')
					->values(['day' => $i, 'hour' => $j])
					->execute();
			}
		}*/

		/*User::register([
			'username' => 'jjanek',
			'first_name' => 'Jakub',
			'last_name' => 'Janek',
			'type' => 'teacher',
			'password' => 'kernel1234',
			'cid' => 1
		]);*/
	}

	public function index() {
		$this->render("app/view/index.tpl.php");
	}

	public function classView($cid) {
		$db = new Database();

		if ($this->isAdmin()) {
			if (isset($_POST['change'])) $this->changeLessonSubmit($db);
			else if (isset($_POST['remove'])) $this->removeLessonSubmit();
		}

		//Classroom information
		{
			$query = $db->select(['class', 'user'])
				->items([
					['class' => 'name'],
					['user' => ['first_name', 'second_name', 'last_name']]
				])
				->condition(['user' => 'uid'], ['class' => 'tuid'])
				->condition(['class' => 'cid'], $cid)
				->execute()->fetch_object();

			if (is_null($query)) {
				$this->error = "Nastala chyba pri ziskavaní triedy";

				$this->render("app/view/class.tpl.php");
				exit();
			}

			$this->classroom = $query;
		}

		$this->timetable = Timetable::fromClassId($cid);
		$this->lessons = $this->timetable->getLessons();
		$this->subjects = $this->timetable->getSubjects();

		$this->teachers = User::getTeachers();

		$this->render("app/view/class.tpl.php");
	}

	//change or add lesson with POST
	private function changeLessonSubmit(Database $db) {
		if (!is_numeric($_POST['hour']) || !($_POST['hour'] <= 8 && $_POST['hour'] >= 0)) {
			$this->error .= 'Nastala neočakávaná chyba. Skúste znova.';

			return;
		} else if (!is_numeric($_POST['day']) || !($_POST['day'] <= 4 && $_POST['day'] >= 0)) {
			$this->error .= 'Nastala neočakávaná chyba. Skúste znova.';

			return;
		} else if (!is_numeric($_POST['lid'])) {
			$this->error .= 'Zadali ste neplatnú hodinu. Skúste znova.';

			return;
		} else if (!is_numeric($_POST['tuid'])) {
			$this->error .= 'Zadali ste neplatneho učiteľa. Skúste znova.';

			return;
		} else if (!is_numeric($_POST['tlid']) && !($_POST['tlid'] >= 0)) {
			$this->error .= 'Nastala neočakávaná chyba. Skúste znova.';

			return;
		} else if (!is_numeric($_POST['ttid'])) {
			$this->error .= 'Zadali ste neplatnú skupinu alebo triedu.';

			return;
		}

		$hid = (10 * $_POST['day'] + 1) + $_POST['hour']; //calculate HID from hour and day

		if ($hid < 0 || $hid > 51) {
			$this->error .= 'Nastala neočakávaná chyba. Skúste znova.';

			return;
		}

		$occupatedHours = Timetable::hoursTeacher($_POST['tuid']);

		if (isset($occupatedHours[$hid]) && $occupatedHours[$hid]['tlid'] !== $_POST['tlid'] ) {
			$this->error = "Učiteľ má už zadanú hodinu obsadenú.";

			return;
		}

		$timetable = new Timetable();

		if (intval($_POST['tlid']) === 0)
			$timetable->addLesson($_POST['ttid'], $_POST['lid'], $_POST['tuid'], $hid)
				? $this->success = "Hodina bola úspešne pridaná" : $this->error = "Nastala chyba pri pridávaní hodiny";
		else
			$timetable->editLesson($_POST['tlid'], $_POST['ttid'], $_POST['lid'], $_POST['tuid'], $hid)
				? $this->success = "Hodina bola úspešne upravená" : $this->error = "Nastala chyba pri upravovaní hodiny";

		return;
	}

	private function removeLessonSubmit() {
		$timetable = new Timetable();
		$timetable->removeLesson($_POST['tlid']);
	}

	public function userView($uid) {
		if ($uid == $this->user->uid) $user = $this->user;
		else $user = new User($uid);

		$this->timetable = Timetable::fromUser($user);
		$this->lessons = $this->timetable->getLessons();

		$this->renderUser = $user;

		$this->render("app/view/user.tpl.php");
	}

	public function apiTeacherLessons($tuid) {
		$this->json(Timetable::hoursTeacher($tuid));
	}

	public function apiTeacherAvailability($hid) {
		$this->json(Timetable::unAvailableTeachers($hid));
	}

	public function isAdmin() {
		return isset($this->user) ? $this->user->type === 'admin' : false;
	}
}