<?php
namespace RequestParameters;

class CommentEdit extends CommentRequestParameters {
	protected $comment_id;
	protected $issue_id;
	protected $member_id;
	protected $message;
	protected $status;
	protected $helpful;
	protected $not_helpful;
	protected $last_updated;
	protected $date_added;
}
