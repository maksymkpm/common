<?php
use \RequestParameters\MemberCreate;
use \RequestParameters\MemberEdit;

class Member {
	const STATUS_NEW = 'new';
	const STATUS_VERIFIED = 'verified';
	const STATUS_DELELED = 'deleted';
	const STATUS_ARCHIVED = 'archived';

	protected $data;

	private function __construct(array $data) {
		if (empty($data) || empty($data['member_id'])) {
			throw new \RuntimeException('Incorrect member data');
		}

		$this->data = $data;
	}

	public function returnData() {
		return $this->data;
	}
	
	public static function Get(int $memberId): ?Member {
		if ($memberId < 1) {
			throw new \RuntimeException('Wrong member ID format.');
		}

		$query = '  SELECT member_id, gender, bdate, rating
  					FROM member					
  					WHERE member_id = :memberId';

		$memberData = self::membersDatabase()
			->select($query)
			->binds('memberId', $memberId)
			->execute()
			->fetch();

		if (empty($memberData)) {
			return null;
		}

		return new self($memberData);
	}

	public static function Create(\RequestParameters\MemberCreate $property): ?Member {
		if (empty($property)) {
			throw new \RuntimeException('Member data is empty.');
		}

		$member = [
			'gender' => $property->gender,
			'bdate' => $property->bdate,
			'status' => self::STATUS_NEW,
			'last_login' => \db::expression('UTC_TIMESTAMP()'),
			'date_added' => \db::expression('UTC_TIMESTAMP()'),
		];

		$password = $property->password;
		if (empty($property->password)) {
			$password = self::CreateToken();
		}
		
		$memberDetails = [
			'origin' => $property->origin,
			'username' => $property->username,
			'password' => password_hash($password, PASSWORD_BCRYPT),
			'status' => self::STATUS_NEW,
			'token' => self::CreateToken(),
			'token_expiry' => \db::expression('DATE_ADD(UTC_TIMESTAMP(), INTERVAL 4 HOUR)'),
			'last_login' => \db::expression('UTC_TIMESTAMP()'),
			'date_added' => \db::expression('UTC_TIMESTAMP()'),
		];

		new \FormValidation($memberDetails, 'MemberCreate');

		self::membersDatabase()->begin();

		self::membersDatabase()
			->insert('member')
			->values($member)
			->execute();

		$memberDetails['member_id'] = self::membersDatabase()->last_insert_id();

		self::membersDatabase()
			->insert('member_details')
			->values($memberDetails)
			->execute();

		self::membersDatabase()->commit();

		return self::get($memberDetails['member_id']);
	}

	private static function CreateToken(): string {
		return 'M' . password_hash(uniqid() . uniqid(), PASSWORD_BCRYPT);
	}

	public static function membersDatabase(): \db {
		return \db::connect('issue');
	}
}