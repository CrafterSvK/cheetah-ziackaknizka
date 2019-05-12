<?php

namespace janek\ziackaknizka\model;

use cheetah\Database;

class Timetable {
	private $db;

	public $tables;

	public function __construct() {
		$this->db = new Database();
	}

	public static function fromUser($user) {
		$timetable = new self();
		/*
				$query = $timetable->db->select(['timetable', 'userGroup'])
					->item(['timetable' => '*'])
					->item(['group' => 'name'])
					->item(['class' => 'name'])
					->condition(['timetable' => 'cid'], $user->cid)
					->or();
				$group = $query->conditions('AND');

				$query = $group->condition(['userGroup' => 'uid'], $user->uid)
					->condition(['timetable' => 'gid'], ['userGroup' => 'gid'])
					->close()->execute();

				if ($query === false) return false;

				$timetable->tables = $query->fetch_all(MYSQLI_ASSOC);

				//fixme very wrong way to do this
				foreach ($timetable->tables as &$table) {
					if (!is_null($table['gid'])) {
						$query = $timetable->db->select('group')
							->condition('gid', $table['gid']);
					} else if (!is_null($table['cid'])) {
						$query = $timetable->db->select('class')
							->condition('cid', $table['cid']);
					}

					$query->item('name')
						->execute()->fetch_array();

					$table['name'] = $query['name'];
				}*/

		/*$query = $timetable->db->select('timetable')
			->item(['timetable' => '*'])
			->item(['group' => 'name'], 'groupName')
			->item(['class' => 'name'], 'className')
			->join('user')->condition(['user' => 'uid'], $user->uid)->close()
			->join('userGroup')->condition(['userGroup' => 'uid'], ['user' => 'uid'])->close()
			->join('group', 'LEFT', null, 'AND')
			->condition(['userGroup' => 'gid'], ['group' => 'gid'])
			->condition(['timetable' => 'cid'], null, 'IS')
			->close()
			->join('class', 'LEFT', null, 'AND')
			->condition(['class' => 'cid'], ['user' => 'cid'])
			->condition(['timetable' => 'gid'], null, 'IS')
			->close()
			->condition(['timetable' => 'gid'], ['userGroup' => 'gid'])
			->or()
			->condition(['timetable' => 'cid'], ['user' => 'cid']);*/

		$query = $timetable->db->select('timetable', 'DISTINCT')
			->item(['timetable' => '*'])
			->item(['group' => 'name'], 'groupName')
			->item(['class' => 'name'], 'className')
			->join('user')->condition(['user' => 'uid'], $user->uid)->close()
			->join('userGroup')->condition(['userGroup' => 'uid'], ['user' => 'uid'])->close()
			->join('group', 'LEFT', null, 'AND')
			->condition(['userGroup' => 'gid'], ['group' => 'gid'])
			->condition(['timetable' => 'cid'], null, 'IS')
			->close()
			->join('class', 'LEFT', null, 'AND')
			->condition(['class' => 'cid'], ['user' => 'cid'])
			->condition(['timetable' => 'gid'], null, 'IS')
			->close()
			->condition(['timetable' => 'gid'], ['userGroup' => 'gid'])
			->or()
			->condition(['timetable' => 'cid'], ['user' => 'cid']);

		if (($result = $query->execute()) === false) return false;

		$timetable->tables = $result->fetch_all(MYSQLI_ASSOC);

		foreach ($timetable->tables as &$table)
			$table['name'] = $table['groupName'] ?? $table['className'];

		return $timetable;
	}

	public static function fromClassId($cid) {
		$timetable = new self();

		$query = $timetable->db->select('timetable', 'DISTINCT')
			->item(['timetable' => '*'])
			->item(['group' => 'name'], 'groupName')
			->item(['class' => 'name'], 'className')
			->join('user')->condition(['user' => 'cid'], $cid)->close()
			->join('userGroup')->condition(['userGroup' => 'uid'], ['user' => 'uid'])->close()
			->join('group', 'LEFT', null, 'AND')
			->condition(['userGroup' => 'gid'], ['group' => 'gid'])
			->condition(['timetable' => 'cid'], null, 'IS')
			->close()
			->join('class', 'LEFT', null, 'AND')
			->condition(['class' => 'cid'], $cid)
			->condition(['timetable' => 'gid'], null, 'IS')
			->close()
			->condition(['timetable' => 'gid'], ['userGroup' => 'gid'])
			->or()
			->condition(['timetable' => 'cid'], $cid);

		if (($result = $query->execute()) === false) return false;

		$timetable->tables = $result->fetch_all(MYSQLI_ASSOC);

		foreach ($timetable->tables as &$table)
			$table['name'] = $table['groupName'] ?? $table['className'];

		return $timetable;
	}

	public function getLessons() {
		$query = $this->db->select(['lesson', 'subject', 'hour', 'user'])
			->items([
				['subject' => ['name', 'lid']],
				['lesson' => ['tlid', 'ttid', 'tuid']],
				['user' => ['first_name', 'second_name', 'last_name']],
				['hour' => ['day', 'hour']]
			])
			->condition(['lesson' => 'lid'], ['subject' => 'lid'])
			->condition(['user' => 'uid'], ['lesson' => 'tuid'])
			->condition(['hour' => 'hid'], ['lesson' => 'hid']);

		$tables = $query->conditions('OR');

		//fixme user doesn't exist
		foreach ($this->tables as $table) {
			$tables->condition(['lesson' => 'ttid'], $table['ttid']);
		}

		$query = $tables->close();

		$result = $query->order('`subject`.lid ASC')
			->execute();

		if ($result === false) return false;

		$lessons = $result->fetch_all(MYSQLI_ASSOC);

		$sortedLessons = [];

		//fixme not very elegant way to sort. Don't ask me.
		foreach ($lessons as $lesson) {
			$sortedLessons[$lesson['day']][$lesson['hour']][] = (object)$lesson;
		}

		return $sortedLessons;
	}

	public function addLesson($ttid, $lid, $tuid, $hid) {
		$tlid = $this->db->insert('lesson')
			->values([
				'ttid' => $ttid,
				'lid' => $lid,
				'tuid' => $tuid,
				'hid' => $hid
			])
			->execute();

		return $tlid !== 0;
	}

	public function editLesson($tlid, $ttid, $lid, $tuid, $hid) {
		return $this->db->update('lesson')
			->values([
				'ttid' => $ttid,
				'lid' => $lid,
				'tuid' => $tuid,
				'hid' => $hid
			])
			->condition('tlid', $tlid)
			->execute();
	}

	public function removeLesson($tlid) {
		$this->db->delete('lesson')
			->condition('tlid', $tlid)
			->execute();
	}

	// SUBJECT HANDLING
	public function getSubjects() {
		$result = $this->db->select('subject')
			->item('*')
			->execute();

		return $result !== false ? $result->fetch_all(MYSQLI_ASSOC) : false;
	}

	public function addSubject($name, $type = null) {
		return $this->db->insert('subject')
			->values([
				'name' => $name,
				'type' => $type
			])
			->execute();
	}

	public function editSubject($lid, $name, $type = null) {
		$query = $this->db->update('subject')
			->value('name', $name);

		if (!is_null($type)) $query->value('type', $type);

		$query->condition('lid', $lid)
			->execute();
	}

	// API HANDLING

	public static function hoursTeacher($tuid) {
		$timetable = new Timetable();

		$lessons = $timetable->db->select(['lesson', 'subject'])
			->items([['lesson' => ['hid', 'tlid']], ['subject' => 'name']])
			->condition(['lesson' => 'tuid'], $tuid)
			->condition(['subject' => 'lid'], ['lesson' => 'lid'])
			->execute()->fetch_all(MYSQLI_ASSOC);

		$sortedLessons = [];

		foreach ($lessons as $lesson) {
			$sortedLessons[$lesson['hid']] = $lesson;
		}

		return $sortedLessons;
	}

	public static function unAvailableTeachers($hid) {
		$timetable = new Timetable();

		$teachers = $timetable->db->select(['user', 'lesson'])
			->item(['lesson' => ['tuid', 'tlid']])
			->condition(['lesson' => 'hid'], $hid)
			->condition(['user' => 'uid'], ['lesson' => 'tuid'])
			->execute()->fetch_all(MYSQLI_ASSOC);

		$sortedTeachers = [];

		foreach ($teachers as $teacher) {
			$sortedTeachers[$teacher['tlid']] = $teacher['tuid'];
		}

		return $sortedTeachers;
	}
}