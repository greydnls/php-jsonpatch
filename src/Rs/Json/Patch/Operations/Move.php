<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operation;
use Rs\Json\Pointer;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class Move extends Operation
{
    /**
     * @var string
     */
    protected $from;

    /**
     * @param \stdClass $operation
     */
    public function __construct(\stdClass $operation)
    {
        $this->assertMandatories($operation);
        parent::__construct('move', $operation);
        $this->from = $operation->from;
    }

    /**
     * @return string
     */
    protected function getFrom()
    {
        return $this->from;
    }

    /**
     * Guard the mandatory operation property
     *
     * @param  \stdClass $operation The operation structure.
     * @throws \Rs\Json\Patch\InvalidOperationException
     */
    protected function assertMandatories(\stdClass $operation)
    {
        if (!property_exists($operation, 'from')) {
            throw new InvalidOperationException('Mandatory from property not set');
        }
    }

    /**
     * @param  string $targetDocument
     *
     * @return string
     */
    public function perform($targetDocument)
    {
        $pointer = new Pointer($targetDocument);
        try {
            $get = $pointer->get($this->getFrom());
        } catch (NonexistentValueReferencedException $e) {
            return $targetDocument;
        }

        if ($this->getFrom() === $this->getPath()) {
            return $targetDocument;
        }

        $removeOperation = new \stdClass;
        $removeOperation->path = $this->getFrom();

        $remove = new Remove($removeOperation);
        $targetDocument = $remove->perform($targetDocument);

        $addOperation = new \stdClass;
        $addOperation->path = $this->getPath();
        $addOperation->value = $get;

        $add = new Add($addOperation);

        return $add->perform($targetDocument);
    }
}
