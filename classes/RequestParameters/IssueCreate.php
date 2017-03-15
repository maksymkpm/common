<?php
namespace RequestParameters;

class IssueCreate extends IssueRequestParameters {
	protected $member_id;
	protected $title;
	protected $description;
	protected $class_id;
	protected $category_id;
	protected $object_id;
	protected $subject_id;
	protected $priority = 3;
	protected $status = 'new';
	protected $date_added;
	protected $comments_amount = 0;
}
