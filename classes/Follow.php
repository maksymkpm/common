<?php
use \RequestParameters\FollowMember;
use \RequestParameters\FollowIssue;

class Follow {
	public static function Issue(\RequestParameters\FollowIssue $property) {
		if (empty($property)) {
			throw new \RuntimeException('following data is empty.');
		}

		$follow = [
			'member_id' => $property->member_id,
			'issue_id' => $property->issue_id,
		];

		new \FormValidation($follow, 'FollowIssue');

		$query = "
			INSERT INTO follow_issue (member_id, issue_id, date_added) VALUES (
				:member_id, :issue_id, UTC_TIMESTAMP())
			ON DUPLICATE KEY 
			UPDATE date_finished = NULL, date_added = UTC_TIMESTAMP()
			";

		$result = (bool) self::Database()->query($query, $follow);

		if (!$result) {
            throw new RuntimeException('Couldnt follow the issue.');
        }

		return [$result];

		//@todo update rating of issue author
	}

	public static function stopFollowIssue(\RequestParameters\FollowIssue $property) {
		if (empty($property)) {
			throw new \RuntimeException('following data is empty.');
		}

		$follow = [
			'member_id' => $property->member_id,
			'issue_id' => $property->issue_id,
		];

		new \FormValidation($follow, 'FollowIssue');

		$result = (bool) self::Database()
			->update('follow_issue')
			->values([
				'date_finished' => \db::expression('UTC_TIMESTAMP()'),
			])
			->where('member_id = :member_id AND issue_id = :issue_id')
			->binds('member_id', $follow['member_id'])
			->binds('issue_id', $follow['issue_id'])
			->execute();

		if (!$result) {
            throw new RuntimeException('Couldnt stop follow the issue.');
        }

		//@todo update rating of issue author
		return [$result];
	}

	public static function Member(\RequestParameters\FollowMember $property) {
		if (empty($property)) {
			throw new \RuntimeException('following data is empty.');
		}

		$follow = [
			'member_id' => $property->member_id,
			'follower_id' => $property->follower_id,
		];

		new \FormValidation($follow, 'FollowMember');

		$query = "
			INSERT INTO follow_member (follower_id, member_id, date_added) VALUES (
				:follower_id, :member_id, UTC_TIMESTAMP())
			ON DUPLICATE KEY 
			UPDATE date_finished = NULL, date_added = UTC_TIMESTAMP()
			";

		$result = (bool) self::Database()->query($query, $follow);

		if (!$result) {
            throw new RuntimeException('Couldnt follow member.');
        }

		//@todo update rating of member
		return [$result];
	}

	public static function stopFollowMember(\RequestParameters\FollowMember $property) {
		if (empty($property)) {
			throw new \RuntimeException('Following data is empty.');
		}

		$follow = [
			'member_id' => $property->member_id,
			'follower_id' => $property->follower_id,
		];

		new \FormValidation($follow, 'FollowMember');

		$result = (bool) self::Database()
			->update('follow_member')
			->values([
				'date_finished' => \db::expression('UTC_TIMESTAMP()'),
			])
			->where('member_id = :member_id AND follower_id = :follower_id')
			->binds('member_id', $follow['member_id'])
			->binds('follower_id', $follow['follower_id'])
			->execute();

		if (!$result) {
            throw new RuntimeException('Couldnt stop follow member.');
        }

		//@todo update rating of member
		return [$result];
	}

	public static function Database(): \db {
		$db = \db::connect('issue');
		$db->query('SET NAMES utf8');
		
		return $db;
	}
}
