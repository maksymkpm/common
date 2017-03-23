<?php
use \RequestParameters\ListGet;
use \RequestParameters\ListMemberIssues;

class Lists {
	private static $binding = [
		'Issues' => ' ORDER BY i.last_updated DESC LIMIT 10',
		'MyIssues' => [],
	];

	public static function Issues(\RequestParameters\ListGet $properties) {
		$bind= [
			'where' => []
		];

		$properties = (object) array_filter((array) $properties, function ($val) {
			return (!is_null($val) && !empty($val));
		});

		$query = '
			SELECT i.issue_id, i.member_id, i.title, i.description, i.class_id, i.category_id, i.object_id,
				i.subject_id, i.priority, i.status, i.helpful, i.not_helpful, i.comments_amount, i.last_updated, i.date_added
  			FROM issue i
			';

		$where = 'WHERE ';
		foreach ($properties as $key => $value) {
			$where .= "i.{$key} = :{$key} AND ";

			$bind['where'][":{$key}"] = $value;
		}

		$where = substr($where, 0, -4);
		if (empty($bind['where'])) {
			$where = substr($where, 0, -2);
		}

		$query .= $where . self::$binding['Issues'];

		return self::issueDatabase()
			->select($query)
			->binds($bind['where'])
			->execute()
			->fetch_all();
	}

	public static function MemberIssues(\RequestParameters\ListMemberIssues $properties) {
		$bind = [];

		$where = 'WHERE ';
		foreach (self::ParseObject($properties) as $key => $value) {
			$where .= "{$key} = :{$key} AND ";
			$bind[$key] = $value;
		}

		$where = substr($where, 0, -4);

		$query = "
			SELECT issue_id, title, status, helpful, not_helpful, last_updated, date_added
  			FROM issue
			{$where}
			ORDER BY last_updated DESC";

		return self::issueDatabase()
			->select($query, $bind)
			->execute()
			->fetch_all();
	}

	private static function ParseObject($object) {
		return (object) array_filter((array) $object, function ($val) {
			return (!is_null($val) && !empty($val));
		});
	}

	public static function issueDatabase(): \db {
		return \db::connect('issue');
	}
}