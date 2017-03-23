<?php
class Log {
	public static function MemberLogin(array $data) {
		if (empty($data)) {
			throw new \RuntimeException('Log data for member login is empty.');
		}

		$data['date_added'] = \db::expression('UTC_TIMESTAMP()');

		self::Database()
			->insert('member_login')
			->values($data)
			->execute();
	}

	public static function Database(): \db {
		$db = \db::connect('issue');
		$db->query('SET NAMES utf8');
		
		return $db;
	}
}
