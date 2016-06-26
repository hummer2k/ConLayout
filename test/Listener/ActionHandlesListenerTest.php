<?php
namespace ConLayoutTest\Listener;

use ConLayout\Listener\ActionHandlesListener;
use ConLayoutTest\AbstractTest;
use Zend\Mvc\MvcEvent;
use Zend\Router\Http\RouteMatch;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListenerTest extends AbstractTest
{
    /**
     * @var ActionHandlesListener
     */
    protected $listener;

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var RouteMatch
     */
    protected $routeMatch;

    public function setUp()
    {
        parent::setUp();
        $this->listener = new ActionHandlesListener($this->layoutUpdater);
        $this->mvcEvent = new MvcEvent();
        $this->routeMatch = new RouteMatch([]);
        $this->mvcEvent->setRouteMatch($this->routeMatch);
    }

    public function testApplicationIndexIndex()
    {
        $routeMatch = new RouteMatch([
            'controller' => 'Application\Controller\Index',
            'action' => 'index'
        ]);
        $this->routeMatch->merge($routeMatch);
        $this->listener->injectActionHandles($this->mvcEvent);

        $expectedHandles = [
            'application',
            'application/index',
            'application/index/index'
        ];

        $handles = $this->layoutUpdater->getHandles();

        foreach ($expectedHandles as $expectedHandle) {
            $this->assertContains($expectedHandle, $handles);
        }
    }

    public function testApplicationIndexIndexWithControllerMap()
    {
        $routeMatch = new RouteMatch([
            'controller' => 'Application\Controller\Index',
            'action' => 'index'
        ]);
        $this->routeMatch->merge($routeMatch);
        $this->listener->setControllerMap([
            'Application' => 'app'
        ]);

        $this->listener->injectActionHandles($this->mvcEvent);

        $expectedHandles = [
            'app',
            'app/index',
            'app/index/index'
        ];

        $handles = $this->layoutUpdater->getHandles();

        foreach ($expectedHandles as $expectedHandle) {
            $this->assertContains($expectedHandle, $handles);
        }
    }

    public function testInjectErrorHandle()
    {
        $error = 'test-error';
        $this->mvcEvent->setError($error);
        $this->listener->injectErrorHandle($this->mvcEvent);

        $this->assertContains($error, $this->layoutUpdater->getHandles());
    }
}
