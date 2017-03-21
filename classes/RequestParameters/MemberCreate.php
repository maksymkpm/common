<?php
namespace RequestParameters;

class MemberCreate extends MemberRequestParameters {
	//member
	protected $gender = 'unknown';
	protected $bdate = '1800-01-01';

	//member_details
	protected $origin;
	protected $username;
	protected $password;
	protected $token;
}
