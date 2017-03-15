<?php
use \RequestParameters\IssueCreate;
use \RequestParameters\IssueEdit;

class Issue {
	const STATUS_NEW = 'new';
	const STATUS_OPENED = 'opened';
	const STATUS_CLOSED = 'closed';
	const STATUS_DELETED = 'deleted';
	const STATUS_ARCHIVED = 'archived';

	protected $data;

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

		$query = '  SELECT issue_id, member_id, title, description, class_id,
						category_id, object_id, subject_id, priority, status, comments_amount, date_added
  					FROM issue
  					WHERE issue_id = :issue_id';

		$issueData = self::issueDatabase()
			->select($query)
			->binds('issue_id', $issue_id)
			->execute()
			->fetch();

		if (empty($issueData)) {
			return null;
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
			'class_id' => $property->class_id,
			'category_id' => $property->category_id,
			'object_id' => $property->object_id,
			'subject_id' => $property->subject_id,
			'priority' => 3,
			'status' => self::STATUS_NEW,
			'comments_amount' => 0,
			'date_added' => \db::expression('UTC_TIMESTAMP()'),
		];

		self::validateIssue($issueData);

		self::issueDatabase()
			->insert('issue')
			->values($issueData)
			->execute();

		return self::Get(self::issueDatabase()->last_insert_id());
	}

	public static function Edit(\RequestParameters\IssueEdit $property): ?Issue {
		$issue = self::Get($property->issue_id);
		$issueData = $property->getModified($issue);

		$issue_array = [];
		foreach ($issue->data as $key => $value) {
			$issue_array[$key] = $value;
			if (isset($issueData[$key])) {
				$issue_array[$key] = $issueData[$key];
			}
		}

		self::validateIssue($issue_array);

		self::issueDatabase()
			->update('issue')
			->values($issueData)
			->where('issue_id = :issue_id')
			->binds('issue_id', $property->issue_id)
			->execute();

		return self::Get($property->issue_id);
	}

	public static function validateIssue(array $issueData) {
		$validation = new \validation('issue');

		$validation->add_field('member_id')
			->add_rule(validation::NOT_EMPTY, null, 'member id cannot be empty.')
			->add_rule(validation::MAX_LENGTH, 50, '\'s length cannot be more than 255 characters.');

		$validation->add_field('title')
			->add_rule(validation::NOT_EMPTY, null, ' cannot be empty.')
			->add_rule(validation::MIN_LENGTH, 2, ' must be at least 8 valid characters.')
			->add_rule(validation::MAX_LENGTH, 255, 'The length of  cannot be more than 255 characters.');

		$validation->add_field('description')
			->add_rule(validation::NOT_EMPTY, null, 'description cannot be empty.')
			->add_rule(validation::MIN_LENGTH, 2, 'description require a minimum two characters');

		$validation->add_field('class_id')
			->add_rule(validation::NOT_EMPTY, null, 'class_id cannot be empty.');

		$validation->add_field('category_id')
			->add_rule(validation::NOT_EMPTY, null, 'category_id cannot be empty.');

		$validation->add_field('object_id')
			->add_rule(validation::NOT_EMPTY, null, 'object_id cannot be empty.');

		$validation->add_field('subject_id')
			->add_rule(validation::NOT_EMPTY, null, 'subject_id cannot be empty.');

		$validation->add_field('priority')
			->add_rule(validation::NOT_EMPTY, null, 'priority cannot be empty.');

		$validation->add_field('status')
			->add_rule(validation::NOT_EMPTY, null, 'Member status cannot be empty.');

		$validation->add_field('comments_amount')
			->add_rule(validation::IS_NUMBER, null, 'Invalid comments_amount.');

		if (!$validation->is_valid($issueData)) {
			throw new ValidationException($validation->get_errors());
		}
	}

	public function Delete($issueId = 0) {
		return true;
	}

	public static function issueDatabase(): \db {
		return \db::connect('issue');
	}

	public static function ReturnIssueClassifications($language = 'ru'): array {
		return [
			'classes' => self::ReturnIssueClasses($language),
			'objects' => self::ReturnIssueObjects($language),
			'subjects' => self::ReturnIssueSubjects($language),
			'categories' => self::ReturnIssueCategories($language),
		];
	}

	public static function ReturnIssueClasses($language = 'ru'): array {
		$classes = [
			'ru' =>
				[
					'1' => 'Взаимоотношения с семьей',
					'2' => 'Коллектив (работа, учеба)',
					'3' => 'Друзья и приятели',
					'4' => 'Сексуальные отношения',
					'5' => 'Зависимости и состояния',
					'6' => 'Комплексы',
					'7' => 'Другое',
				]
		];

		return $classes[$language];
	}

	public static function ReturnIssueObjects($language = 'ru'): array {
		$objects = [
			'ru' =>
				[
					'1' => 'Object1',
				]
		];

		return $objects[$language];
	}

	public static function ReturnIssueSubjects($language = 'ru'): array {
		$subjects = [
			'ru' =>
				[
					'1' => 'Subject1',
				]
		];

		return $subjects[$language];
	}

	public static function ReturnIssueCategories($language = 'ru'): array {
		$categories = [
			'ru' =>
				[
					'1' => 'Category1',
				]
		];

		return $categories[$language];
	}

	public function getIssueId() {
		return $this->data['issue_id'];
	}

	public function getMemberId() {
		return $this->data['member_id'];
	}

	public function getTitle() {
		return $this->data['title'];
	}

	public function getDescription() {
		return $this->data['description'];
	}

	public function getClassId() {
		return $this->data['class_id'];
	}

	public function getCategoryId() {
		return $this->data['category_id'];
	}

	public function getObjectId() {
		return $this->data['object_id'];
	}

	public function getSubjectId() {
		return $this->data['subject_id'];
	}

	public function getPriorityId() {
		return $this->data['priority'];
	}

	public function getStatus() {
		return $this->data['status'];
	}

	public function getCommentsAmount() {
		return $this->data['comments_amount'];
	}

	public function getDateAdded() {
		return $this->data['date_added'];
	}
}