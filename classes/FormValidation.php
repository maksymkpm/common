<?php
class FormValidation extends validation {
	protected $forms = [
		'Comment', 'Issue', 'FeedbackIssue', 'FeedbackIssueComment', 'MemberCreate',
		'FollowIssue', 'FollowMember'
	];

	public function __construct(array $data, string $form) {
		if (!in_array($form, $this->forms)) {
			throw new RuntimeException('Provided form not exist.');
		}

		$this->data = $data;
		$this->form = $form;

		$this->$form($data);
	}

    public static function FeedbackIssue(array $data) {
        $validation = new parent('FeedbackIssue');

        $validation->add_field('issue_id')
            ->add_rule(validation::NOT_EMPTY, null, 'Issue id cannot be empty.')
            ->add_rule(validation::IS_NUMBER, null, 'Invalid issue id.');

        $validation->add_field('member_id')
            ->add_rule(validation::NOT_EMPTY, null, 'Member id cannot be empty.')
            ->add_rule(validation::IS_NUMBER, null, 'Invalid member id.');

        $validation->add_field('helpful')
            ->add_rule(validation::NOT_EMPTY, null, 'Feedback cannot be empty.');

        if (!$validation->is_valid($data)) {
            throw new ValidationException($validation->get_errors());
        }
    }
	
	public static function FollowIssue(array $data) {
        $validation = new parent('FollowIssue');

        $validation->add_field('issue_id')
            ->add_rule(validation::NOT_EMPTY, null, 'Issue id cannot be empty.')
            ->add_rule(validation::IS_NUMBER, null, 'Invalid issue id.');

        $validation->add_field('member_id')
            ->add_rule(validation::NOT_EMPTY, null, 'Member id cannot be empty.')
            ->add_rule(validation::IS_NUMBER, null, 'Invalid member id.');

        if (!$validation->is_valid($data)) {
            throw new ValidationException($validation->get_errors());
        }
    }
	
	public static function FollowMember(array $data) {
        $validation = new parent('FollowMember');

        $validation->add_field('follower_id')
            ->add_rule(validation::NOT_EMPTY, null, 'Follower id cannot be empty.')
            ->add_rule(validation::IS_NUMBER, null, 'Invalid follower id.');

        $validation->add_field('member_id')
            ->add_rule(validation::NOT_EMPTY, null, 'Member id cannot be empty.')
            ->add_rule(validation::IS_NUMBER, null, 'Invalid member id.');

        if (!$validation->is_valid($data)) {
            throw new ValidationException($validation->get_errors());
        }
    }

    public static function FeedbackIssueComment(array $data) {
        $validation = new parent('FeedbackIssueComment');

        $validation->add_field('comment_id')
            ->add_rule(validation::NOT_EMPTY, null, 'Comment id cannot be empty.')
            ->add_rule(validation::IS_NUMBER, null, 'Invalid comment id.');

        $validation->add_field('member_id')
            ->add_rule(validation::NOT_EMPTY, null, 'Member id cannot be empty.')
            ->add_rule(validation::IS_NUMBER, null, 'Invalid member id.');

        $validation->add_field('helpful')
            ->add_rule(validation::NOT_EMPTY, null, 'Feedback cannot be empty.');

        if (!$validation->is_valid($data)) {
            throw new ValidationException($validation->get_errors());
        }
    }

	public static function Comment(array $data) {
		$validation = new parent('Comment');

		$validation->add_field('issue_id')
			->add_rule(validation::NOT_EMPTY, null, 'Issue id cannot be empty.')
			->add_rule(validation::IS_NUMBER, null, 'Invalid issue id.');

		$validation->add_field('member_id')
			->add_rule(validation::NOT_EMPTY, null, 'Member id cannot be empty.')
			->add_rule(validation::IS_NUMBER, null, 'Invalid member id.');

		$validation->add_field('message')
			->add_rule(validation::NOT_EMPTY, null, 'Message cannot be empty.')
			->add_rule(validation::MIN_LENGTH, 2, 'Message require a minimum two characters');

		$validation->add_field('status')
			->add_rule(validation::NOT_EMPTY, null, 'Issue status cannot be empty.');

		if (!$validation->is_valid($data)) {
			throw new ValidationException($validation->get_errors());
		}
	}
	
	public static function Issue(array $data) {
		$validation = new parent('Issue');

		$validation->add_field('member_id')
			->add_rule(validation::NOT_EMPTY, null, 'Member id cannot be empty.')
			->add_rule(validation::IS_NUMBER, null, 'Invalid member id.');

		$validation->add_field('title')
			->add_rule(validation::NOT_EMPTY, null, 'Title cannot be empty.')
			->add_rule(validation::MIN_LENGTH, 8, ' Title must be at least 8 valid characters.')
			->add_rule(validation::MAX_LENGTH, 255, 'The length of title cannot be more than 255 characters.');

		$validation->add_field('description')
			->add_rule(validation::NOT_EMPTY, null, 'Description cannot be empty.')
			->add_rule(validation::MIN_LENGTH, 2, 'Description require a minimum two characters');

		$validation->add_field('category_id')
			->add_rule(validation::IS_NUMBER, null, 'Invalid category.')
			->add_rule(validation::NOT_EMPTY, null, 'Category cannot be empty.');

		$validation->add_field('object_id')
			->add_rule(validation::IS_NUMBER, null, 'Invalid object.')
			->add_rule(validation::NOT_EMPTY, null, 'Object cannot be empty.');

		$validation->add_field('subject_id')
			->add_rule(validation::IS_NUMBER, null, 'Subject is not provided.')
			->add_rule(validation::NOT_EMPTY, null, 'Subject cannot be empty.');

		$validation->add_field('priority')
			->add_rule(validation::IS_NUMBER, null, 'Invalid priority.')
			->add_rule(validation::NOT_EMPTY, null, 'Priority cannot be empty.');

		$validation->add_field('status')
			->add_rule(validation::NOT_EMPTY, null, 'Issue status cannot be empty.');

		$validation->add_field('comments_amount')
			->add_rule(validation::IS_NUMBER, null, 'Invalid comments amount.');

		if (!$validation->is_valid($data)) {
			throw new ValidationException($validation->get_errors());
		}
	}
	
	public static function MemberCreate(array $data) {
		$validation = new validation('MemberCreate');

		$validation->add_field('username')
			->add_rule(validation::NOT_EMPTY, null, 'Username cannot be empty.')
			//->add_rule(validation::EMAIL, null, 'Username should be a valid email address.')
			->add_rule(validation::MAX_LENGTH, 255, 'Username\'s length cannot be more than 255 characters.');

		$validation->add_field('password')
			->add_rule(validation::NOT_EMPTY, null, 'Password cannot be empty.')
			->add_rule(validation::MIN_LENGTH, 8, 'Password must be at least 8 valid characters.')
			->add_rule(validation::MAX_LENGTH, 255, 'The length of password cannot be more than 255 characters.');

		$validation->add_field('status')
			->add_rule(validation::NOT_EMPTY, null, 'Member status cannot be empty.');

		if (!$validation->is_valid($data)) {
			throw new \ValidationException($validation->get_errors());
		}
	}
}