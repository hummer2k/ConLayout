<?php
namespace ConLayout\Config;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Sorter implements SorterInterface
{
    /**
     *
     * @var array
     */
    protected $priorities = array();
    
    /**
     * 
     * @param array $priorities
     */
    public function __construct(array $priorities)
    {
        $this->priorities = $priorities;
    }
    
    /**
     * 
     * @param array $data
     * @return array
     */
    public function sort(array &$data)
    {
        uksort($data, function($a, $b) {            
            $orderA = -10;
            $orderB = -10;
            foreach($this->priorities as $substr => $priority) {
                foreach (array('a', 'b') as $var) {
                    $handle = $$var;
                    if (false !== strpos($handle, $substr)) {
                        ${'order' . strtoupper($var)} = is_callable($priority)
                            ? call_user_func($priority, $handle, $substr)
                            : $priority;
                    }
                }
            }
            if ($orderA === $orderB) {
                return 0;
            }
            return ($orderA < $orderB) ? -1 : 1;
        });
    }
}
