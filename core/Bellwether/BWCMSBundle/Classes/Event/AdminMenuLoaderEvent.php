<?php

namespace Bellwether\BWCMSBundle\Classes\Event;

use Symfony\Component\EventDispatcher\Event;
use Knp\Menu\MenuItem;

class AdminMenuLoaderEvent extends Event
{
    /**
     * @var MenuItem
     */
    private $menuItem = null;

    /**
     * @param string $itemKey
     * @param string $referenceItemKey
     */
    public function moveItem($itemKey, $referenceItemKey, $isAfter = true)
    {
        if (is_null($this->menuItem)) {
            return;
        }
        $itemKeys = array_keys($this->menuItem->getChildren());
        if (!in_array($itemKey, $itemKeys)) {
            return;
        }
        if (!in_array($referenceItemKey, $itemKeys)) {
            return;
        }
        $itemKeyIndex = array_search($itemKey, $itemKeys);
        unset($itemKeys[$itemKeyIndex]);

        $referenceItemKeyIndex = array_search($referenceItemKey, $itemKeys);
        if($isAfter){
            $referenceItemKeyIndex = $referenceItemKeyIndex + 1;
        }
        array_splice($itemKeys, $referenceItemKeyIndex, 0, array($itemKey));
        $this->menuItem->reorderChildren($itemKeys);
    }


    /**
     * @return MenuItem
     */
    public function getMenuItem()
    {
        return $this->menuItem;
    }

    /**
     * @param MenuItem $menuItem
     */
    public function setMenuItem($menuItem)
    {
        $this->menuItem = $menuItem;
    }

}