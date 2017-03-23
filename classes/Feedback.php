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
        ];

        new \FormValidation($data, 'FeedbackIssue');

		$query = "
			INSERT INTO feedback_issue (issue_id, member_id, helpful, date_added) VALUES (
				:issue_id, :member_id, :helpful, UTC_TIMESTAMP())
			ON DUPLICATE KEY
			UPDATE date_added = UTC_TIMESTAMP(), helpful = :helpful
			";

		$result = (bool) self::Database()->query($query, $data);

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
        ];

        new \FormValidation($data, 'FeedbackIssueComment');

		$query = "
			INSERT INTO feedback_issue_comment (comment_id, member_id, helpful, date_added) VALUES (
				:comment_id, :member_id, :helpful, UTC_TIMESTAMP())
			ON DUPLICATE KEY
			UPDATE date_added = UTC_TIMESTAMP(), helpful = :helpful
			";

		$result = (bool) self::Database()->query($query, $data);

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

        return self::Database()->query($query, [':comment_id' => $comment_id])->execute();
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

      return self::Database()->query($query, [':issue_id' => $issue_id])->execute();
    }

	//@todo
	private static function updateRating($comment_id) {

	}

	//@todo
	private static function calculateRating($data) {}

   public static function Database(): \db {
		$db = \db::connect('issue');
		$db->query('SET NAMES utf8');
		
		return $db;
	}
}