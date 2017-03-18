<?php
use \RequestParameters\FeedbackIssueCreate;
use \RequestParameters\FeedbackCommentCreate;

class Feedback {
    public static function IssueCreate(\RequestParameters\FeedbackIssueCreate $property) {
        if (empty($property)) {
            throw new \RuntimeException('Feedback data is empty.');
        }

        $FeedbackIssue = [
            'issue_id' => $property->issue_id,
            'member_id' => $property->member_id,
            'helpful' => $property->helpful,
            'date_added' => \db::expression('UTC_TIMESTAMP()'),
        ];

        new \FormValidation($FeedbackIssue, 'FeedbackIssue');

        return self::feedbackDatabase()
            ->insert('feedback_issue')
            ->values($FeedbackIssue)
            ->execute();
    }

    public static function CommentCreate(\RequestParameters\FeedbackCommentCreate $property) {
        if (empty($property)) {
            throw new \RuntimeException('Feedback data is empty.');
        }

        $FeedbackIssueComment = [
            'comment_id' => $property->comment_id,
            'member_id' => $property->member_id,
            'helpful' => $property->helpful,
            'date_added' => \db::expression('UTC_TIMESTAMP()'),
        ];

        new \FormValidation($FeedbackIssueComment, 'FeedbackIssueComment');

        return self::feedbackDatabase()
            ->insert('feedback_issue_comment')
            ->values($FeedbackIssueComment)
            ->execute();
    }

    public static function feedbackDatabase(): \db {
        return \db::connect('issue');
    }
}