<?php
class FormValidation extends validation {
	protected $forms = [
		'Comment', 'Issue'
	];

	public function __construct(array $data, string $form) {
		if (!in_array($form, $this->forms)) {
			throw new RuntimeException('Provided form not exist.');
		}

		$this->data = $data;
		$this->form = $form;

		$this->$form($data);
	}

	public static function Comment(array $data) {
		$validation = new parent('Comment');

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

		if (!$validation->is_valid($data)) {
			throw new ValidationException($validation->get_errors());
		}
	}
}