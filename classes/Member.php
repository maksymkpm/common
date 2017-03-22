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

	public static function Auth(array $data = []): ?Member {
		if (!isset($data['profile'])) {
			throw new \RuntimeException('Unknown profile type.');
		}

		switch ($data['profile']) {
			case 'vk':
				$result = self::AuthVK($data['vk_member_id']);
				break;
		}

		return $result;
	}

	private static function AuthVK($vk_member_id) {

		$query = '	SELECT member_id, IF(token_expiry < NOW(), "", token) token
					FROM member_profile
					WHERE username = :vk_member_id';

		$memberData = self::membersDatabase()
			->select($query)
			->binds('vk_member_id', $vk_member_id)
			->execute()
			->fetch();

		if (empty($memberData)) {
			//create member
			$params = new MemberCreate([
				'username' => $vk_member_id,
				'profile' => 'vk',
			]);

			return self::Create($params);
		}

		//if token expired, create new token
		if (empty($memberData['token'])) {
			$memberData['token'] = self::tokenCreate();
		}

		self::tokenUpdate($vk_member_id, 'vk', $memberData['token']);

		return new self($memberData);
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
			$password = self::tokenCreate();
		}

		$memberDetails = [
			'profile' => $property->profile,
			'username' => $property->username,
			'password' => password_hash($password, PASSWORD_BCRYPT),
			'status' => self::STATUS_NEW,
			'token' => self::tokenCreate(),
			'token_expiry' => self::tokenExpiry(),
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
			->insert('member_profile')
			->values($memberDetails)
			->execute();

		self::membersDatabase()->commit();

		return new self([
			'member_id' => $memberDetails['member_id'],
			'token' => $memberDetails['token'],
		]);
	}

	private static function tokenCreate(): string {
		return 'M' . password_hash(uniqid() . uniqid(), PASSWORD_BCRYPT);
	}

	private static function tokenExpiry() {
		return \db::expression('DATE_ADD(UTC_TIMESTAMP(), INTERVAL 4 HOUR)');
	}

	private static function tokenUpdate($username, $profile, $newToken) {
		$result = self::membersDatabase()
			->update('member_profile')
			->values([
				'token' => $newToken,
				'token_expiry' => self::tokenExpiry(),
			])
			->where('username = :username AND profile = :profile')
			->binds('username', $username)
			->binds('profile', $profile)
			->execute();
	}
	
	public static function tokenExpiryUpdate($member_id, $token) {
		return 
			self::membersDatabase()
				->update('member_profile')
				->values([
					'token_expiry' => self::tokenExpiry(),
				])
				->where('token = :token AND member_id = :member_id')
				->binds('token', $token)
				->binds('member_id', $member_id)
				->execute();
	}

	public static function getMemberByToken($token) {
		$query = '	SELECT IF(token_expiry < NOW(), 0, member_id) AS member_id
					FROM member_profile
					WHERE token = :token';

		$result = self::membersDatabase()
			->select($query)
			->binds('token', $token)
			->execute()
			->fetch();

		if (empty($result)) {
			throw new \AuthenticationException('TOKEN_NOT_EXIST', 410);
		}
		
		if ($result['member_id'] == 0) {
			throw new \AuthenticationException('TOKEN_EXPIRED', 411);
		}

		return $result['member_id'];
	}

	public static function membersDatabase(): \db {
		return \db::connect('issue');
	}
}