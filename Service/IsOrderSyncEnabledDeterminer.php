<?php

namespace Klevu\Metadata\Service;

use Klevu\Metadata\Api\IsEnabledConditionInterface;

class IsOrderSyncEnabledDeterminer
{
    /**
     * @var IsEnabledConditionInterface[]
     */
    private $isEnabledConditions = [];

    /**
     * @param IsEnabledConditionInterface[] $isOrderSyncConditions
     */
    public function __construct(
        array $isOrderSyncConditions = []
    ) {
        array_walk($isOrderSyncConditions, [$this, 'addIsOrderSyncEnabledCondition']);
    }

    /**
     * @param IsEnabledConditionInterface $isOrderSyncCondition
     * @param string $identifier
     */
    private function addIsOrderSyncEnabledCondition(IsEnabledConditionInterface $isOrderSyncCondition, $identifier)
    {
        $this->isEnabledConditions[$identifier] = $isOrderSyncCondition;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function execute($storeId = null)
    {
        $return = true;

        foreach ($this->isEnabledConditions as $isEnabledCondition) {
            if (!$isEnabledCondition->execute($storeId)) {
                $return = false;
                break;
            }
        }

        return $return;
    }
}
