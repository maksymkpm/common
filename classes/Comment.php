<?php
use \RequestParameters\CommentCreate;
use \RequestParameters\CommentEdit;

class Comment {
	const STATUS_NEW = 'new';
	const STATUS_PUBLISHED = 'published';
	const STATUS_DELETED = 'deleted';
	const STATUS_ARCHIVED = 'archived';

	public $data;

	private function __construct(array $commentData) {
		if (empty($commentData) || empty($commentData['comment_id'])) {
			throw new \RuntimeException('Incorrect comment data');
		}

		$this->data = $commentData;
	}

	public static function Get(int $comment_id): ?Comment {
		if ($comment_id < 1) {
			throw new \RuntimeException('Wrong comment ID format.');
		}

		$query = '  SELECT comment_id, issue_id, member_id, message, status, helpful, not_helpful, last_updated, date_added
  					FROM issue_comment
  					WHERE comment_id = :comment_id';

		$commentData = self::commentDatabase()
			->select($query)
			->binds('comment_id', $comment_id)
			->execute()
			->fetch();

		if (empty($commentData)) {
			throw new \RuntimeException('Requested comment not found.');
		}

		return new self($commentData);
	}

	public static function Create(\RequestParameters\CommentCreate $property): ?Comment {
		if (empty($property)) {
			throw new \RuntimeException('Comment data is empty.');
		}

		$commentData = [
			'issue_id' => $property->issue_id,
			'member_id' => $property->member_id,
			'message' => $property->message,
			'helpful' => 0,
			'not_helpful' => 0,
			'status' => self::STATUS_NEW,
			'last_updated' => \db::expression('UTC_TIMESTAMP()'),
			'date_added' => \db::expression('UTC_TIMESTAMP()'),
		];

		new \FormValidation($commentData, 'Comment');

		self::commentDatabase()
			->insert('issue_comment')
			->values($commentData)
			->execute();

		return self::Get(self::commentDatabase()->last_insert_id());
	}

	public static function Edit(\RequestParameters\CommentEdit $property): ?Comment {
		$comment = self::Get($property->comment_id);
		$commentData = $property->getModified($comment);

		$commentData['last_updated'] = \db::expression('UTC_TIMESTAMP()');

		$issue_array = [];
		foreach ($comment->data as $key => $value) {
			$issue_array[$key] = $value;
			if (isset($commentData[$key])) {
				$issue_array[$key] = $commentData[$key];
			}
		}

		new \FormValidation($issue_array, 'Comment');

		self::commentDatabase()
			->update('issue_comment')
			->values($commentData)
			->where('comment_id = :comment_id')
			->binds('comment_id', $property->comment_id)
			->execute();

		return self::Get($property->comment_id);
	}

	public static function Publish(\RequestParameters\CommentEdit $property): ?Comment {
		if (!($property->issue_id)) {
			throw new \RuntimeException('Comment cannot be published without issue id.');
		}

		$result = self::commentDatabase()
						->update('issue_comment')
						->values([
							'last_updated' => \db::expression('UTC_TIMESTAMP()'),
							'status' => self::STATUS_PUBLISHED,
						])
						->where('comment_id = :comment_id')
						->binds('comment_id', $property->comment_id)
						->execute();

		if ($result) {
			self::updateIssueCount($property->issue_id, true);

			return new self(['comment_id' => $property->comment_id]);
		}

		throw new \RuntimeException('Comment not published.');
	}

	public static function Delete(\RequestParameters\CommentEdit $property): ?Comment {
		if (!($property->issue_id)) {
			throw new \RuntimeException('Comment cannot be deleted without issue id.');
		}

		$result = self::commentDatabase()
						->update('issue_comment')
						->values([
							'last_updated' => \db::expression('UTC_TIMESTAMP()'),
							'status' => self::STATUS_DELETED,
						])
						->where('comment_id = :comment_id')
						->binds('comment_id', $property->comment_id)
						->execute();

		if ($result) {
			self::updateIssueCount($property->issue_id, false);

			return new self(['comment_id' => $property->comment_id]);
		}

		throw new \RuntimeException('Comment not deleted.');
	}

	public static function Archive(\RequestParameters\CommentEdit $property): ?Comment {
		if (!($property->issue_id)) {
			throw new \RuntimeException('Comment cannot be archived without issue id.');
		}

		$result = self::commentDatabase()
						->update('issue_comment')
						->values([
							'last_updated' => \db::expression('UTC_TIMESTAMP()'),
							'status' => self::STATUS_ARCHIVED,
						])
						->where('comment_id = :comment_id')
						->binds('comment_id', $property->comment_id)
						->execute();

		if ($result) {
			self::updateIssueCount($property->issue_id, false);

			return new self(['comment_id' => $property->comment_id]);
		}

		throw new \RuntimeException('Comment not archived.');
	}

	public static function commentDatabase(): \db {
		return \db::connect('issue');
	}

	private static function updateIssueCount($issue_id, $add) {
		$comments_amount = 'comments_amount+1';
		if ($add !== true) {
			$comments_amount = 'comments_amount-1';
		}

		$query = "
			UPDATE issue
			SET comments_amount = {$comments_amount}
			WHERE issue_id = :issue_id
		";

		self::commentDatabase()->query($query, [':issue_id' => $issue_id]);
	}
}
