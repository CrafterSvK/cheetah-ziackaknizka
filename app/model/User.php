<?php

namespace janek\ziackaknizka\model;

use cheetah\Database;
use function password_verify;

class User {
	private $db;
	private $specific = ['uid', 'username'];

	public $uid;
	public $username;
	public $first_name;
	public $second_name;
	public $last_name;
	public $full_name;
	public $type;
	public $cid;

	public function __construct(int $uid = 0) {
		$this->db = new Database();

		if ($uid !== 0) {
			$this->uid = $uid;

			$this->set();
		}
	}

	public static function register($info) {
		$user = new self();

		$info['password'] = password_hash($info['password'], PASSWORD_DEFAULT);

		$uid = $user->db->insert('user')
			->values($info)
			->execute();

		$user->uid = $uid;

		$user->set();

		return $user;
	}

	public static function login($username, $password) {
		$user = new self();

		$result = $user->db->select('user')
			->items(['uid', 'password'])
			->condition('username', $username)
			->execute();

		if (empty($result)) {
			return false;
		}

		$dbUser = $result->fetch_object();

		if (!password_verify($password, $dbUser->password)) {
			return false;
		}

		$user->uid = $dbUser->uid;
		$user->set();

		return $user;
	}

	public static function getTeachers() {
		$user = new User();

		$teachers = $user->db->select('user')
			->items(['uid', 'username', 'first_name', 'second_name', 'last_name', 'type', 'cid'])
			->condition('type', 'teacher')
			->or()
			->condition('type', 'admin')
			->execute()->fetch_all(MYSQLI_ASSOC);

		foreach ($teachers as &$teacher) {
			$teacher = (object)$teacher;

			$teacher->full_name = self::assembleName($teacher);
		}

		return $teachers;
	}

	public function set() {
		$query = $this->db->select('user')
			->items(['uid', 'username', 'first_name', 'second_name', 'last_name', 'type', 'cid']);

		if (isset($this->uid)) {
			$query->condition('uid', $this->uid);
		} else {
			$conditions = $query->conditions('OR');

			foreach ($this->specific as $key) {
				$conditions->condition($key, $this->$key);
			}

			$query = $conditions->close();
		}

		$user = $query->execute()->fetch_object();

		$user->full_name = $this->assembleName($user);

		$this->merge($user);
	}

	private function merge($object) {
		foreach ($object as $key => $value) {
			$this->$key = $value;
		}
	}

	public static function assembleName($user) {
		$name = $user->first_name;
		$name .= $user->second_name . " " ?? null;
		$name .= $user->last_name;

		return $name;
	}
}