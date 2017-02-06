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
     * @return FeedbackContainerTrait Chainable
     */
    public function addFeedback($level, $message)
    {
        $this->feedbacks[] = [
            /** @deprecated "msg" */
            'msg'     => (string)$message,
            'message' => (string)$message,
            'level'   => $level
        ];

        return $this;
    }

    /**
     * Remove all feedback from collection.
     *
     * @return FeedbackContainerTrait Chainable
     */
    public function clearFeedback()
    {
        $this->feedbacks = [];

        return $this;
    }
}
