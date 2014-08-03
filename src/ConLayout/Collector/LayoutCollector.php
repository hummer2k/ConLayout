<?php
namespace ConLayout\Collector;

use ZendDeveloperTools\Collector\AbstractCollector,
    Zend\Stdlib\ArrayUtils,
    Closure,
    ZendDeveloperTools\Stub\ClosureStub;

/**
 * Collector for ZendDeveloperToolbar
 *
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollector
    extends AbstractCollector
{
    const NAME = 'con-layout';
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
    
    /**
     * 
     * @return int
     */
    public function getPriority()
    {
        return 600;
    }
    
    /**
     * collect data for zdt
     * 
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     * @return \ConLayout\Collector\LayoutCollector
     */
    public function collect(\Zend\Mvc\MvcEvent $mvcEvent)
    {
        $sm = $mvcEvent->getApplication()->getServiceManager();
        $layoutService = $sm->get('ConLayout\Service\LayoutService');
        $blocksBuilder = $sm->get('ConLayout\Service\BlocksBuilder');
        $data = array(
            'handles' => $layoutService->getHandles(),
            'layoutConfig' => $this->makeArraySerializable(
                $layoutService->getLayoutConfig()
            ),
            'blocks' => array()
        );
        foreach ($blocksBuilder->getBlocks() as $name => $instance) {
            $data['blocks'][$name] = get_class($instance);
        }
        $this->data = $data;
        return $this;
    }
    
    /**
     * 
     * @return array
     */
    public function getHandles()
    {
        return $this->data['handles'];
    }
    
    /**
     * 
     * @return array
     */
    public function getLayoutConfig()
    {
        return $this->data['layoutConfig'];
    }
    
    /**
     * 
     * @return array
     */
    public function getBlocks()
    {
        return $this->data['blocks'];
    }
    
    /**
     * Replaces the un-serializable items in an array with stubs
     *
     * @param array|\Traversable $data
     *
     * @return array
     */
    private function makeArraySerializable($data)
    {
        $serializable = array();

        foreach (ArrayUtils::iteratorToArray($data) as $key => $value) {
            if ($value instanceof Traversable || is_array($value)) {
                $serializable[$key] = $this->makeArraySerializable($value);

                continue;
            }

            if ($value instanceof Closure) {
                $serializable[$key] = new ClosureStub();

                continue;
            }

            $serializable[$key] = $value;
        }

        return $serializable;
    }

    /**
     * Opposite of {@see makeArraySerializable} - replaces stubs in an array with actual un-serializable objects
     *
     * @param array $data
     *
     * @return array
     */
    private function unserializeArray(array $data)
    {
        $unserialized = array();

        foreach (ArrayUtils::iteratorToArray($data) as $key => $value) {
            if ($value instanceof Traversable || is_array($value)) {
                $unserialized[$key] = $this->unserializeArray($value);

                continue;
            }

            if ($value instanceof ClosureStub) {
                $unserialized[$key] = function () {};

                continue;
            }

            $unserialized[$key] = $value;
        }

        return $unserialized;
    }
}
