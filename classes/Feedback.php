<?php
use \RequestParameters\FeedbackIssueCreate;
use \RequestParameters\FeedbackCommentCreate;

class Feedback {
    public static function IssueCreate(\RequestParameters\FeedbackIssueCreate $property) {
        if (empty($property)) {
            throw new \RuntimeException('Feedback data is empty.');
        }

        $data = [
            'issue_id' => $property->issue_id,
            'member_id' => $property->member_id,
            'helpful' => $property->helpful,
            'date_added' => \db::expression('UTC_TIMESTAMP()'),
        ];

        new \FormValidation($data, 'FeedbackIssue');

        $result = self::feedbackDatabase()
            ->insert('feedback_issue')
            ->values($data)
            ->execute();

        if (!$result) {
            throw new RuntimeException('Feedback didnt added.');
        }

        $result = self::updateIssueHelpfulCount($data['issue_id'], $data['helpful']);

        if (!$result) {
            throw new RuntimeException('Feedback didnt updated.');
        }

        return $result;
    }

    public static function CommentCreate(\RequestParameters\FeedbackCommentCreate $property) {
        if (empty($property)) {
            throw new \RuntimeException('Feedback data is empty.');
        }

        $data = [
            'comment_id' => $property->comment_id,
            'member_id' => $property->member_id,
            'helpful' => $property->helpful,
            'date_added' => \db::expression('UTC_TIMESTAMP()'),
        ];

        new \FormValidation($data, 'FeedbackIssueComment');

        $result = self::feedbackDatabase()
            ->insert('feedback_issue_comment')
            ->values($data)
            ->execute();

        if (!$result) {
            throw new RuntimeException('Feedback didnt added.');
        }

        $result = self::updateCommentHelpfulCount($data['comment_id'], $data['helpful']);

        if (!$result) {
            throw new RuntimeException('Feedback didnt updated.');
        }

        return $result;
    }

    private static function updateCommentHelpfulCount($comment_id, $helpful) {
        $field = 'helpful';
        if ($helpful == 0) {
            $field = 'not_helpful';
        }

        $query = "
			UPDATE issue_comment
			SET {$field} = {$field} + 1
			WHERE comment_id = :comment_id
		";

        return self::feedbackDatabase()->query($query, [':comment_id' => $comment_id])->execute();
    }

    private static function updateIssueHelpfulCount($issue_id, $helpful) {
        $field = 'helpful';
        if ($helpful == 0) {
            $field = 'not_helpful';
        }

        $query = "
			UPDATE issue
			SET {$field} = {$field} + 1
			WHERE issue_id = :issue_id
		";

      return self::feedbackDatabase()->query($query, [':issue_id' => $issue_id])->execute();
    }
	
	//@todo
	private static function updateRating($member_id, $data) {}
	
	//@todo
	private static function calculateRating($data) {}

    public static function feedbackDatabase(): \db {
        return \db::connect('issue');
    }
}