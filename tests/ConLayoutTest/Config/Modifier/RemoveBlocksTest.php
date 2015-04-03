<?php

namespace ConLayoutTest\Config\Mutator;

use ConLayout\Config\Mutator\RemoveBlocks;
use ConLayoutTest\AbstractTest;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class RemoveBlocksTest extends AbstractTest
{
    public function testRemoveBlocks()
    {
        $removeBlocks = new RemoveBlocks();
        $config = [
            'sidebar' => [
                'some.block' => []
            ],
            '_remove' => [
                'some.block' => true
            ]
        ];

        $result = $removeBlocks->mutate($config);

        $this->assertEquals(['sidebar' => []], $result);
    }

    public function testRemoveBlocksString()
    {
        $removeBlocks = new RemoveBlocks();
        $config = [
            'sidebar' => [
                'some.block' => []
            ],
            '_remove' =>  'some.block'
        ];

        $result = $removeBlocks->mutate($config);

        $this->assertEquals(['sidebar' => []], $result);
    }

    public function testDoNotRemoveBlock()
    {
        $removeBlocks = new RemoveBlocks();
        $config = [
            'sidebar' => [
                'some.block' => []
            ],
            '_remove' =>  [
                'some.block' => false
            ]
        ];

        $result = $removeBlocks->mutate($config);

        $this->assertEquals(['sidebar' => [
            'some.block' => []
        ]], $result);
    }

    public function testRemoveBlockChildren()
    {
        $removeBlocks = new RemoveBlocks();
        $config = [
            'sidebar' => [
                'some.block' => [
                    'children' => [
                        'someCapture' => [
                            'child.block' => []
                        ]
                    ]
                ]
            ],
            '_remove' =>  [
                'child.block' => true
            ]
        ];

        $result = $removeBlocks->mutate($config);

        $this->assertEquals(['sidebar' => [
            'some.block' => [
                'children' => [
                    'someCapture' => []
                ]
            ]
        ]], $result);
    }
}