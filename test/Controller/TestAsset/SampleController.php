<?php

declare(strict_types=1);

namespace ConLayoutTest\Controller\TestAsset;

use Laminas\Mvc\Controller\AbstractActionController;

class SampleController extends AbstractActionController
{
    public function testAction(): array
    {
        return ['content' => 'test'];
    }

    public function testSomeStrangelySeparatedWordsAction(): array
    {
        return ['content' => 'Test Some Strangely Separated Words'];
    }

    public function testCircularAction(): \Laminas\View\Model\ModelInterface|\Laminas\Http\Response
    {
        return $this->forward()->dispatch('sample', ['action' => 'test-circular']);
    }
}
