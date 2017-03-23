<?php
class Log {
	public static function MemberLogin(array $data) {
		if (empty($data)) {
			throw new \RuntimeException('Log data for member login is empty.');
		}

		$data['date_added'] = \db::expression('UTC_TIMESTAMP()');

		self::logDatabase()
			->insert('member_login')
			->values($data)
			->execute();
	}

	public static function logDatabase(): \db {
		return \db::connect('issue');
	}
}
