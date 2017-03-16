<?php
use \RequestParameters\CommentCreate;
use \RequestParameters\CommentEdit;

class Comment {
	const STATUS_NEW = 'new';
	const STATUS_PUBLISHED = 'published';
	const STATUS_DELETED = 'deleted';
	const STATUS_ARCHIVED = 'archived';

	protected $data;

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

		$query = '  SELECT comment_id, issue_id, member_id, message, status, last_updated, date_added
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
			'status' => self::STATUS_NEW,
			'last_updated' => \db::expression('UTC_TIMESTAMP()'),
			'date_added' => \db::expression('UTC_TIMESTAMP()'),
		];

		self::validateComment($commentData);

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

		self::validateComment($issue_array);

		self::commentDatabase()
			->update('issue_comment')
			->values($commentData)
			->where('comment_id = :comment_id')
			->binds('comment_id', $property->comment_id)
			->execute();

		return self::Get($property->comment_id);
	}

	public static function validateComment(array $commentData) {
		$validation = new \validation('issue_comment');
		
		$validation->add_field('issue_id')
			->add_rule(validation::NOT_EMPTY, null, 'Issue id cannot be empty.')
			->add_rule(validation::IS_NUMBER, null, 'Invalid issue id.');

		$validation->add_field('member_id')
			->add_rule(validation::NOT_EMPTY, null, 'Member id cannot be empty.')
			->add_rule(validation::MAX_LENGTH, 50, 'Member\'s length cannot be more than 50 characters.');

		$validation->add_field('message')
			->add_rule(validation::NOT_EMPTY, null, 'Message cannot be empty.')
			->add_rule(validation::MIN_LENGTH, 2, 'Message require a minimum two characters');

		$validation->add_field('status')
			->add_rule(validation::NOT_EMPTY, null, 'Issue status cannot be empty.');

		if (!$validation->is_valid($commentData)) {
			throw new ValidationException($validation->get_errors());
		}
	}

	public static function commentDatabase(): \db {
		return \db::connect('issue');
	}
	
	public function getCommentId() {
		return $this->data['comment_id'];
	}
	
	public function getIssueId() {
		return $this->data['issue_id'];
	}

	public function getMemberId() {
		return $this->data['member_id'];
	}

	public function getMessage() {
		return $this->data['message'];
	}

	public function getStatus() {
		return $this->data['status'];
	}

	public function getLastUpdated() {
		return $this->data['last_updated'];
	}
	
	public function getDateAdded() {
		return $this->data['date_added'];
	}
}