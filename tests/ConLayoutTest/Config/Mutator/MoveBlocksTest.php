<?php

namespace ConLayoutTest\Config\Mutator;

use ConLayout\Config\Mutator\MoveBlocks;
use ConLayoutTest\AbstractTest;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class MoveBlocksTest extends AbstractTest
{
    public function testMoveBlock()
    {
        $config = [
            'sidebarLeft' => [
                'my.block' => [],
            ],
            'sidebarRight' => [
                'my.block.2' => []
            ],
            '_move' => [
                'my.block' => 'sidebarRight'
            ]
        ];
        $moveBlocks = new MoveBlocks();

        $result = $moveBlocks->mutate($config);

        $expected = [
            'sidebarLeft' => [],
            'sidebarRight' => [
                'my.block.2' => [],
                'my.block' => []
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMoveToChildBlock()
    {
        $config = [
            'sidebarLeft' => [
                'my.block' => [],
                'my.parent.block' => [
                    'children' => [
                        'childHtml' => [
                            'my.child.block' => []
                        ]
                    ]
                ]
            ],
            'sidebarRight' => [
                'my.block.2' => [],
                'my.block.to.move' => []
            ],
            '_move' => [
                'my.block.to.move' => 'my.parent.block::childHtml'
            ]
        ];
        $moveBlocks = new MoveBlocks();

        $result = $moveBlocks->mutate($config);

        $expected = [
            'sidebarLeft' => [
                'my.block' => [],
                'my.parent.block' => [
                    'children' => [
                        'childHtml' => [
                            'my.child.block' => [],
                            'my.block.to.move' => []
                        ]
                    ]
                ]
            ],
            'sidebarRight' => [
                'my.block.2' => []
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMoveToChildChildBlock()
    {
        $config = [
            'sidebarLeft' => [
                'my.block' => [],
                'my.parent.block' => [
                    'children' => [
                        'childHtml' => [
                            'my.child.block' => []
                        ]
                    ]
                ]
            ],
            'sidebarRight' => [
                'my.block.2' => [],
                'my.block.to.move' => [
                    'template' => 'tpl/for/block-to-move'
                ]
            ],
            '_move' => [
                'my.block.to.move' => 'my.child.block::childHtml'
            ]
        ];
        $moveBlocks = new MoveBlocks();

        $result = $moveBlocks->mutate($config);

        $expected = [
            'sidebarLeft' => [
                'my.block' => [],
                'my.parent.block' => [
                    'children' => [
                        'childHtml' => [
                            'my.child.block' => [
                                'children' => [
                                    'childHtml' => [
                                        'my.block.to.move' => [
                                            'template' => 'tpl/for/block-to-move'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sidebarRight' => [
                'my.block.2' => []
            ]
        ];

        $this->assertEquals($expected, $result);
    }


    public function testMoveToParentChildBlock()
    {
        $config = [
            'sidebarLeft' => [
                'my.block' => [],
                'my.parent.block' => [
                    'children' => [
                        'childHtml' => [
                            'my.block.to.move' => []
                        ]
                    ]
                ]
            ],
            'sidebarRight' => [
                'my.block.2' => []
            ],
            '_move' => [
                'my.block.to.move' => 'my.block::content'
            ]
        ];
        $moveBlocks = new MoveBlocks();

        $result = $moveBlocks->mutate($config);

        $expected = [
            'sidebarLeft' => [
                'my.block' => [
                    'children' => [
                        'content' => [
                            'my.block.to.move' => []
                        ]
                    ]
                ],
                'my.parent.block' => [
                    'children' => [
                        'childHtml' => []
                    ]
                ]
            ],
            'sidebarRight' => [
                'my.block.2' => []
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}