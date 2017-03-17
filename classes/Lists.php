<?php
use \RequestParameters\ListGet;

class Lists {
	private static $binding = [
		'Issues' => ' ORDER BY i.last_updated DESC LIMIT 3',
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

	public static function issueDatabase(): \db {
		return \db::connect('issue');
	}
}