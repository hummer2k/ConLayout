<?php

namespace ConLayoutTest\Controller\TestAsset;

use Laminas\Mvc\Controller\AbstractActionController;

class SampleController extends AbstractActionController
{
    public function testAction()
    {
        return array('content' => 'test');
    }
    public function testSomeStrangelySeparatedWordsAction()
    {
        return array('content' => 'Test Some Strangely Separated Words');
    }
    public function testCircularAction()
    {
        return $this->forward()->dispatch('sample', array('action' => 'test-circular'));
    }
}
