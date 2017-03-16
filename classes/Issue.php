<?php
use \RequestParameters\IssueCreate;
use \RequestParameters\IssueEdit;

class Issue {
	const STATUS_NEW = 'new';
	const STATUS_OPENED = 'opened';
	const STATUS_CLOSED = 'closed';
	const STATUS_DELETED = 'deleted';
	const STATUS_ARCHIVED = 'archived';

	public $data;

	private function __construct(array $issueData) {
		if (empty($issueData) || empty($issueData['issue_id'])) {
			throw new \RuntimeException('Incorrect issue data');
		}

		$this->data = $issueData;
	}

	public static function Get(int $issue_id): ?Issue {
		if ($issue_id < 1) {
			throw new \RuntimeException('Wrong issue ID format.');
		}

		$query = '  SELECT issue_id, member_id, title, description, class_id, category_id, object_id, 
						subject_id, priority, status, helpful, not_helpful, comments_amount, last_updated, date_added
  					FROM issue
  					WHERE issue_id = :issue_id';

		$issueData = self::issueDatabase()
			->select($query)
			->binds('issue_id', $issue_id)
			->execute()
			->fetch();

		if (empty($issueData)) {
			throw new \RuntimeException('Requested issue not found.');
		}

		return new self($issueData);
	}

	public static function Create(\RequestParameters\IssueCreate $property): ?Issue {
		if (empty($property)) {
			throw new \RuntimeException('Issue data is empty.');
		}

		$issueData = [
			'member_id' => $property->member_id,
			'title' => $property->title,
			'description' => $property->description,
			'class_id' => 1,
			'category_id' => $property->category_id,
			'object_id' => $property->object_id,
			'subject_id' => $property->subject_id,
			'priority' => 3,
			'status' => self::STATUS_NEW,
			'helpful' => 0,
			'not_helpful' => 0,
			'comments_amount' => 0,
			'comments_amount' => 0,
			'last_updated' => \db::expression('UTC_TIMESTAMP()'),
			'date_added' => \db::expression('UTC_TIMESTAMP()'),
		];

		new \FormValidation($issueData, 'Issue');

		self::issueDatabase()
			->insert('issue')
			->values($issueData)
			->execute();

		return self::Get(self::issueDatabase()->last_insert_id());
	}

	public static function Edit(\RequestParameters\IssueEdit $property): ?Issue {
		$issue = self::Get($property->issue_id);
		$issueData = $property->getModified($issue);

		$issueData['last_updated'] = \db::expression('UTC_TIMESTAMP()');

		$issue_array = [];
		foreach ($issue->data as $key => $value) {
			$issue_array[$key] = $value;
			if (isset($issueData[$key])) {
				$issue_array[$key] = $issueData[$key];
			}
		}

		new \FormValidation($issue_array, 'Issue');

		self::issueDatabase()
			->update('issue')
			->values($issueData)
			->where('issue_id = :issue_id')
			->binds('issue_id', $property->issue_id)
			->execute();

		return self::Get($property->issue_id);
	}
	
	public static function Open(\RequestParameters\IssueEdit $property): ?Issue {
		if (!($property->issue_id)) {
			throw new \RuntimeException('Invalid issue id provided.');
		}

		$result = self::issueDatabase()
						->update('issue')
						->values([
							'last_updated' => \db::expression('UTC_TIMESTAMP()'),
							'status' => self::STATUS_OPENED,
						])
						->where('issue_id = :issue_id')
						->binds('issue_id', $property->issue_id)
						->execute();

		if ($result) {
			return new self(['issue_id' => $property->issue_id]);
		}

		throw new \RuntimeException('Issue is not opened.');
	}
	
	public static function Close(\RequestParameters\IssueEdit $property): ?Issue {
		if (!($property->issue_id)) {
			throw new \RuntimeException('Invalid issue id provided.');
		}

		$result = self::issueDatabase()
						->update('issue')
						->values([
							'last_updated' => \db::expression('UTC_TIMESTAMP()'),
							'status' => self::STATUS_CLOSED,
						])
						->where('issue_id = :issue_id')
						->binds('issue_id', $property->issue_id)
						->execute();

		if ($result) {
			return new self(['issue_id' => $property->issue_id]);
		}

		throw new \RuntimeException('Issue is not closed.');
	}
	
	public static function Delete(\RequestParameters\IssueEdit $property): ?Issue {
		if (!($property->issue_id)) {
			throw new \RuntimeException('Invalid issue id provided.');
		}

		$result = self::issueDatabase()
						->update('issue')
						->values([
							'last_updated' => \db::expression('UTC_TIMESTAMP()'),
							'status' => self::STATUS_DELETED,
						])
						->where('issue_id = :issue_id')
						->binds('issue_id', $property->issue_id)
						->execute();

		if ($result) {
			return new self(['issue_id' => $property->issue_id]);
		}

		throw new \RuntimeException('Issue is not deleted.');
	}
	
	public static function Archive(\RequestParameters\IssueEdit $property): ?Issue {
		if (!($property->issue_id)) {
			throw new \RuntimeException('Invalid issue id provided.');
		}

		$result = self::issueDatabase()
						->update('issue')
						->values([
							'last_updated' => \db::expression('UTC_TIMESTAMP()'),
							'status' => self::STATUS_ARCHIVED,
						])
						->where('issue_id = :issue_id')
						->binds('issue_id', $property->issue_id)
						->execute();

		if ($result) {
			return new self(['issue_id' => $property->issue_id]);
		}

		throw new \RuntimeException('Issue is not archived.');
	}

	public static function issueDatabase(): \db {
		return \db::connect('issue');
	}
}