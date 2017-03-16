<?php
namespace RequestParameters;

class CommentCreate extends CommentRequestParameters {
	protected $comment_id;
	protected $issue_id;
	protected $member_id;
	protected $message;
	protected $status = 'new';
	protected $last_updated;
	protected $date_added;
}
