<?php
namespace ConLayout\Config;

use ConLayout\Handle\HandleInterface;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Sorter implements SorterInterface
{
    /**
     *
     * @var HandleInterface[]
     */
    protected $handles = [];

    /**
     *
     * @param array $handles
     */
    public function __construct(array $handles)
    {
        $this->handles = $handles;
    }
        
    /**
     * 
     * @param array $data
     * @return array
     */
    public function sort(array &$data)
    {
        uksort($data, function($a, $b) {            
            $orderA = 0;
            $orderB = 0;
            
            foreach ($this->handles as $handle) {
                if ($handle->getName() === $a) {
                    $orderA = $handle->getPriority();
                }
                if ($handle->getName() === $b) {
                    $orderB = $handle->getPriority();
                }
            }

            if ($orderA === $orderB) {
                return 0;
            }
            return ($orderA < $orderB) ? -1 : 1;
        });
    }
}
