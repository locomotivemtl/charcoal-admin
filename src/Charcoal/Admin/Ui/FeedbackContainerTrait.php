<?php

namespace Charcoal\Admin\Ui;

use Charcoal\Validator\ValidatableInterface;
use Charcoal\Validator\ValidatorInterface;
use Charcoal\Validator\ValidatorResult;
use InvalidArgumentException;
use UnexpectedValueException;

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
     * @param  string|array      $level   The feedback level or an dataset describing the feedback entry.
     * @param  string|array|null $message The feedback message or an dataset describing the feedback entry.
     * @return string The unique ID assigned to the feedback.
     */
    public function addFeedback($level, $message = null)
    {
        if (is_array($level) && $message === null) {
            $entry = $level;
        } elseif (is_string($level) && is_array($message)) {
            $entry = $message;
            $entry['level'] = (string)$level;
        } else {
            $entry = [
                'level'   => (string)$level,
                'message' => (string)$message,
            ];
        }

        $entry = $this->parseFeedback($entry);

        $this->feedbacks[] = $entry;

        return $entry['id'];
    }

    /**
     * Add feedback from an object's validation results.
     *
     * @param  ValidatableInterface $obj     The validatable object.
     * @param  string[]|string|null $filters Filter the levels to merge.
     * @throws InvalidArgumentException If the filters are invalid.
     * @return self
     */
    public function addFeedbackFromValidatable(ValidatableInterface $obj, $filters = null)
    {
        return $this->addFeedbackFromValidator($obj->validator(), $filters);
    }

    /**
     * Add feedback from a validator.
     *
     * @param  ValidatorInterface   $validator The validator.
     * @param  string[]|string|null $filters   Filter the levels to merge.
     * @throws InvalidArgumentException If the filters are invalid.
     * @return self
     */
    public function addFeedbackFromValidator(ValidatorInterface $validator, $filters = null)
    {
        $levels = $this->getSupportedValidatorLevelsForFeedback();

        if (is_string($filters) && in_array($filters, $levels)) {
            $results = call_user_func([ $validator, $filters.'Results' ]);
            foreach ($results as $result) {
                $this->addFeedbackFromValidatorResult($result);
            }

            return $this;
        }

        if (!is_array($filters) && $filters !== null) {
            throw new InvalidArgumentException(
                'Filters must be an array of validation levels or NULL'
            );
        }

        $validation = $validator->results();
        foreach ($validation as $level => $results) {
            if ($filters === null || in_array($level, $filters)) {
                foreach ($results as $result) {
                    $this->addFeedbackFromValidatorResult($result);
                }
            }
        }

        return $this;
    }

    /**
     * Add feedback from a validator result.
     *
     * @param  ValidatorResult $result The validation result.
     * @return string The unique ID assigned to the feedback.
     */
    public function addFeedbackFromValidatorResult(ValidatorResult $result)
    {
        $entry = [
            'key'     => $result->ident(),
            'level'   => $result->level(),
            'message' => $result->message(),
        ];

        return $this->addFeedback($entry);
    }

    /**
     * Retrieves the support validator levels for feedback.
     *
     * @return string[]
     */
    public function getSupportedValidatorLevelsForFeedback()
    {
        return [
            ValidatorInterface::ERROR,
            ValidatorInterface::WARNING,
            ValidatorInterface::NOTICE,
        ];
    }

    /**
     * Parse a feedback entry.
     *
     * @param  array $entry A dataset describing the feedback entry.
     * @throws InvalidArgumentException If the feedback entry is invalid.
     * @throws UnexpectedValueException If the feedback entry ID was altered.
     * @return array A parsed feedback entry.
     */
    final protected function parseFeedback(array $entry)
    {
        $fid = $this->generateFeedbackEntryId();
        $entry['id'] = $fid;

        $entry = $this->parseFeedbackEntry($entry);

        if (empty($entry['id']) || $entry['id'] !== $fid) {
            throw new UnexpectedValueException('The unique ID assigned to the feedback must not be changed.');
        }

        if (empty($entry['level']) || empty($entry['message'])) {
            throw new InvalidArgumentException('Feedback requires a "level" and a "message".');
        }

        $entry['type']  = $this->resolveFeedbackType($entry['level']);
        $entry['level'] = $this->resolveFeedbackLevel($entry['level']);

        return $entry;
    }

    /**
     * Parse a feedback entry (customizable).
     *
     * @param  array $entry A dataset describing the feedback entry.
     * @throws InvalidArgumentException If the feedback entry is invalid.
     * @return array A parsed feedback entry.
     */
    protected function parseFeedbackEntry(array $entry)
    {
        $entry['message'] = (string)$entry['message'];

        if (!isset($entry['dismissible'])) {
            $entry['dismissible'] = $this->isFeedbackDismissable($entry['level']);
        } else {
            $entry['dismissible'] = (bool)$entry['dismissible'];
        }

        return $entry;
    }

    /**
     * Generate a unique feedback entry ID.
     *
     * @return string A unique feedback entry ID.
     */
    protected function generateFeedbackEntryId()
    {
        return uniqid();
    }

    /**
     * Determine if the given feedback level is dismissable, by default.
     *
     * @param  string $level The feedback level.
     * @return boolean Whether the level is dismissable (TRUE) or not (FALSE).
     */
    protected function isFeedbackDismissable($level)
    {
        return in_array($level, [ 'log', 'debug', 'info', 'notice' ]);
    }

    /**
     * Resolve the given Bootstrap alert type.
     *
     * @param  string $level The feedback level.
     * @return string The Bootstrap alert type.
     */
    protected function resolveFeedbackType($level)
    {
        switch ($level) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
                return 'danger';

            case 'debug':
                return 'warning';

            case 'notice':
            case 'log':
                return 'info';

            case 'done':
                return 'success';
        }

        return $level;
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
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'danger':
                return 'error';

            case 'debug':
                return 'warning';

            case 'notice':
            case 'log':
                return 'info';

            case 'done':
                return 'success';
        }

        return $level;
    }
}
