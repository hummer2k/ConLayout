<?php

declare(strict_types=1);

namespace ConLayoutTest\Controller\TestAsset;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ModelInterface;

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

    public function testCircularAction(): ModelInterface|Response
    {
        return $this->forward()->dispatch('sample', ['action' => 'test-circular']);
    }
}
