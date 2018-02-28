<?php

namespace Charcoal\Admin\Ui;

use InvalidArgumentException;

/**
 * Provides methods for collecting feedback messages.
 */
trait FeedbackContainerTrait
{
    /**
     * Collection of feedback.
     *
     * @var array
     */
    protected $feedbacks = [];

    /**
     * Determine if there's feedback.
     *
     * @return boolean
     */
    public function hasFeedbacks()
    {
        return ($this->numFeedbacks() > 0);
    }

    /**
     * Count feedback.
     *
     * @return integer
     */
    public function numFeedbacks()
    {
        return count($this->feedbacks());
    }

    /**
     * Retrieve the feedback collection.
     *
     * Optionally retrieve only the feedback for the given level.
     *
     * @param  string|null $level Optional level to filter collection.
     * @throws InvalidArgumentException If the feedback level is invalid.
     * @return array
     */
    public function feedbacks($level = null)
    {
        if ($level !== null) {
            if (!is_string($level)) {
                throw new InvalidArgumentException('The feedback level must be a string');
            }

            $level = $this->resolveFeedbackLevel($level);

            $subset = [];
            foreach ($this->feedbacks as $item) {
                if ($item['level'] === $level) {
                    $subset[] = $item;
                }
            }

            return $subset;
        }

        return $this->feedbacks;
    }

    /**
     * Add feedback.
     *
     * @param  string $level   The feedback level.
     * @param  mixed  $message The feedback message.
     * @return string The unique ID assigned to the feedback.
     */
    public function addFeedback($level, $message)
    {
        $fid = uniqid();
        $this->feedbacks[] = [
            'id'          => $fid,
            'level'       => $this->resolveFeedbackLevel($level),
            'message'     => (string)$message,
            'dismissible' => true,
        ];

        return $fid;
    }

    /**
     * Remove all feedback from collection.
     *
     * @return self
     */
    public function clearFeedback()
    {
        $this->feedbacks = [];

        return $this;
    }

    /**
     * Resolve the given feedback level.
     *
     * @param  string $level The feedback level.
     * @return string The level.
     */
    protected function resolveFeedbackLevel($level)
    {
        switch ($level) {
            case 'notice':
                return 'info';

            default:
                return $level;
        }
    }
}
