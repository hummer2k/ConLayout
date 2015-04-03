<?php
namespace ConLayout\Zdt\Collector;

use Closure;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use ZendDeveloperTools\Collector\AbstractCollector;
use ZendDeveloperTools\Stub\ClosureStub;

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
     * @return LayoutCollector
     */
    public function collect(\Zend\Mvc\MvcEvent $mvcEvent)
    {
        $sm = $mvcEvent->getApplication()->getServiceManager();

        $layout = $mvcEvent->getViewModel();
        $layoutService = $sm->get('ConLayout\Service\LayoutService');
        $blocksBuilder = $sm->get('ConLayout\Service\BlocksBuilder');

        $config = $sm->get('Config');
        $priorities = isset($config['con-layout']['sorter']['priorities'])
            ? $config['con-layout']['sorter']['priorities']
            : array();

        $data = array(
            'handles' => $this->prepareHandles($layoutService->getHandles(), $priorities),
            'layoutConfig' => $this->makeArraySerializable(
                $layoutService->getLayoutConfig()
            ),
            'blocks' => array(),
            'layout_template' => $layout->getTemplate()
        );

        $debugger = $sm->get('ConLayout\Debugger');
        $data['debug'] = $debugger->isEnabled();

        /* @var $instance ViewModel */
        foreach ($blocksBuilder->getBlocks() as $name => $instance) {
            if ($data['debug'] && ($captureTo = $instance->getVariable('captureTo'))) {
                $instance = $instance->getVariable('originalBlock');
                $instance->setCaptureTo($captureTo);
            }
            $data['blocks'][$name] = [
                'class' => get_class($instance),
                'template' => $instance->getTemplate(),
                'capture_to' => $instance->captureTo()
            ];
        }
        
        $this->data = $data;
        return $this;
    }

    /**
     *
     * @param array $handles
     * @param array $priorities
     */
    protected function prepareHandles(array $handles, array $priorities)
    {
        $preparedHandles = [];
        foreach ($handles as $handle) {
            $found = false;
            foreach ($priorities as $substr => $priority) {
                if (false !== strpos($handle, $substr)) {
                    if (is_callable($priority)) {
                        $preparedHandles[$handle] = call_user_func($priority, $handle, $substr);
                    } else {
                        $preparedHandles[$handle] = (int) $priority;
                    }
                    $found = true;
                }
            }
            if (!$found) {
                $preparedHandles[$handle] = 0;
            }
        }
        arsort($preparedHandles);
        return $preparedHandles;
    }

    public function isDebug()
    {
        return !empty($this->data['debug']);
    }

    /**
     *
     * @return string
     */
    public function getLayoutTemplate()
    {
        return $this->data['layout_template'];
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
     * @param array|Traversable $data
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
