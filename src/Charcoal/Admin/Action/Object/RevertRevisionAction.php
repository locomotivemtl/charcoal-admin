<?php

namespace Charcoal\Admin\Action\Object;

use Exception;
use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-object'
use Charcoal\Object\RevisionableInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Action: Restore Object Revision
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 * - `obj_id` (_mixed_) — The object ID to revert
 * - `rev_num` (_integer_) — The object's revision to restore the object to
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the object was properly restored, FALSE in case of any error.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Revision has been restored
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Revision could not be restored
 */
class RevertRevisionAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * The revision number to restore.
     *
     * @var integer|null
     */
    protected $revNum;

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type', 'obj_id', 'rev_num'
        ], parent::validDataFromRequest());
    }

    /**
     * Set the revision number to restore.
     *
     * @param  integer $revNum The revision number to load.
     * @throws InvalidArgumentException If the given revision is invalid.
     * @return ObjectContainerInterface Chainable
     */
    protected function setRevNum($revNum)
    {
        if (!is_numeric($revNum)) {
            throw new InvalidArgumentException(sprintf(
                'Revision must be an integer, received %s.',
                (is_object($revNum) ? get_class($revNum) : gettype($revNum))
            ));
        }

        $this->revNum = (int)$revNum;

        return $this;
    }

    /**
     * Retrieve the revision number to restore.
     *
     * @return integer|null
     */
    public function revNum()
    {
        return $this->revNum;
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        try {
            $translator = $this->translator();

            $failMessage = $translator->translate('Failed to restore revision');
            $errorThrown = strtr($translator->translate('{{ errorMessage }}: {{ errorThrown }}'), [
                '{{ errorMessage }}' => $failMessage
            ]);

            $obj    = $this->obj();
            $revNum = $this['revNum'];
            $rev    = $obj->revisionNum($revNum);
            $revTs  = $rev['revTs'];
            $result = $obj->revertToRevision($revNum);

            if ($result) {
                $doneMessage = $translator->translate(
                    'Object has been successfully restored to revision from {{ revisionDate }}'
                );

                $this->addFeedback('success', strtr($doneMessage, [
                    '{{ revisionDate }}' => $revTs->format('Y-m-d @ H:i:s')
                ]));
                $this->addFeedback('success', strtr($translator->translate('Restored Revision: {{ revisionNum }}'), [
                    '{{ revisionNum }}' => $revNum
                ]));
                $this->addFeedback('success', strtr($translator->translate('Reverted Object: {{ objId }}'), [
                    '{{ objId }}' => $obj->id()
                ]));
                $this->setSuccess(true);

                return $response;
            } else {
                $this->addFeedback('error', $failMessage);
                $this->setSuccess(false);

                return $response->withStatus(500);
            }
        } catch (Exception $e) {
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }
}
