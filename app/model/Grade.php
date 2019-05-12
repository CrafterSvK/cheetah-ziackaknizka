<?php
namespace janek\ziackaknizka\model;

use cheetah\Database;
use stdClass;

class Grade {
	public $grades = [];

	public function __construct() {
		$this->db = new Database();
	}

	public static function fromUser($user) {
			$grade = new Grade();

			$query = $grade->db->select('grade')
				->join('user')->condition(['grade' => 'tuid'], ['user' => 'uid'])->close()
				->item(['grade' => '*'])
				->item(['user' => ['first_name', 'second_name', 'last_name']])
				->condition(['grade' => 'uid'], $user->uid);

			if ($result = $query->execute() === false) return false;

			$grades = $result->fetch_all(MYSQLI_ASSOC);

			foreach ($grades as &$currentGrade) {
				$user = new stdClass();

				$user->first_name = $currentGrade['first_name'];
				$user->second_name = $currentGrade['second_name'];
				$user->last_name = $currentGrade['last_name'];

				$currentGrade['teacher'] = User::assembleName($user);
			}

			return $grade;
	}

	public function add($uid, $grade, $tuid, $ggid): int {
		return $this->db->insert('grade')
			->values([
				'uid' => $uid,
				'grade' => $grade,
				'tuid' => $tuid,
				'ggid' => $ggid
			])->execute();
	}

	public function edit($gid, $grade, $tuid, $ggid) {
		$this->db->update('grade')
			->values([
				'grade' => $grade,
				'tuid' => $tuid,
				'ggid' => $ggid
			])
			->condition('gid', $gid);
	}

	public function addGroup() {
		return;
	}
}