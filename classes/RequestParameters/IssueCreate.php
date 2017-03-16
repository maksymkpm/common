<?php
namespace RequestParameters;

class IssueCreate extends IssueRequestParameters {
	protected $member_id;
	protected $title;
	protected $description;
	protected $class_id = 1;
	protected $category_id;
	protected $object_id;
	protected $subject_id;
	protected $priority = 3;
	protected $status = 'new';
	protected $helpful = 0;
	protected $not_helpful = 0;
	protected $date_added;
	protected $comments_amount = 0;
}
