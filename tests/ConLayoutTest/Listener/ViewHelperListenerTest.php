<?php

namespace ConLayoutTest\Listener;

use ConLayout\AssetPreparer\BasePath;
use ConLayout\AssetPreparer\CacheBuster;
use ConLayout\Listener\ViewHelperListener;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Bootstrap;
use Zend\Config\Config;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\Doctype;
use Zend\View\Helper\HeadLink;
use Zend\View\Helper\HeadMeta;
use Zend\View\Helper\HeadScript;
use Zend\View\Helper\HeadTitle;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperListenerTest extends AbstractTest
{
    protected function getLayoutStructure()
    {
        return new Config([
            'view_helpers' => [
                'doctype' => 'HTML5',
                'headLink' => [
                    'main'   => '/css/main.css',
                    'busted' => 'busted.css',
                    'test'   => [
                        'method' => 'prependStylesheet',
                        'args' => '/css/test.css'
                    ]
                ],
                'headTitle' => [
                    'My Title',
                    'Another Title',
                    [
                        'method' => 'prepend',
                        'args' => 'First'
                    ]
                ],
                'headMeta' => [
                    'charset' => [
                        'method' => 'setCharset',
                        'args'   => 'utf8'
                    ],
                    ['description', 'My description'],
                    ['keywords', 'keyword1, keyword2, keyword3']
                ],
                'headScript' => [
                    'jquery-ui' => '/js/jquery-ui.min.js',
                    'jquery' => [
                        'method' => 'prependFile',
                        'args' => '/js/jquery.min.js'
                    ],
                    'modernizr' => [
                        'method' => 'appendFile',
                        'args' => [
                            '/js/modernizr.js',
                            'text/javascript',
                            ['conditional' => 'lt IE 9']
                        ]
                    ],
                    'funcs' => [
                        'method' => 'offsetSetFile',
                        'args' => [100, '/js/functions.js']
                    ],
                    'will-be-ignored' => false
                ]
            ]
        ]);
    }

    public function testApplyViewHelpers()
    {
        $config = Bootstrap::getServiceManager()->get('Config');

        $helperPluginManager = new HelperPluginManager();

        $listener = new ViewHelperListener(
            $this->layoutUpdater,
            $helperPluginManager,
            $config['con-layout']['view_helpers']
        );

        $mvcEvent = new MvcEvent();

        $renderer = new PhpRenderer();
        $renderer->setHelperPluginManager($helperPluginManager);

        $basePath = $helperPluginManager->get('basePath');
        $basePath->setBasePath('/assets');

        $basePathAssetPreparer = new BasePath($basePath);
        $listener->addAssetPreparer('headLink', $basePathAssetPreparer);

        $cacheBusterAssetPreparer = new CacheBuster(__DIR__ . '/_files');
        $listener->addAssetPreparer('headLink', $cacheBusterAssetPreparer);

        /* @var $headLink HeadLink */
        $headLink = $helperPluginManager->get('headLink');
        /* @var $headScript HeadScript */
        $headScript = $helperPluginManager->get('headScript');
        /* @var $doctype Doctype */
        $doctype = $helperPluginManager->get('doctype');
        /* @var $headTitle HeadTitle */
        $headTitle = $helperPluginManager->get('headTitle');
        /* @var $headMeta HeadMeta */
        $headMeta = $helperPluginManager->get('headMeta');
        $headMeta->setView($renderer);

        $listener->applyViewHelpers($mvcEvent);



        $expected = '<link href="/assets/css/test.css" media="screen" rel="stylesheet" type="text/css">' . PHP_EOL
                  . '<link href="/assets/css/main.css" media="screen" rel="stylesheet" type="text/css">' . PHP_EOL
                  . '<link href="/assets/busted.css?9222f23a3b009428d59c29aa4283b18d" media="screen" rel="stylesheet" '
                  . 'type="text/css">';

        $this->assertEquals($expected, $headLink->toString());

        $expected = '<script type="text/javascript" src="/js/jquery.min.js"></script>' . PHP_EOL
                  . '<script type="text/javascript" src="/js/jquery-ui.min.js"></script>' . PHP_EOL
                  . '<!--[if lt IE 9]><script type="text/javascript" src="/js/modernizr.js"></script>'
                  . '<![endif]-->' . PHP_EOL
                  . '<script type="text/javascript" src="/js/functions.js"></script>';

        $this->assertEquals($expected, $headScript->toString());

        $this->assertEquals('<!DOCTYPE html>', (string) $doctype);

        $headTitle->setSeparator(' | ');
        $expected = '<title>First | My Title | Another Title</title>';

        $this->assertEquals($expected, $headTitle->toString());

        $expected = '<meta charset="utf8">' . PHP_EOL
                  . '<meta name="description" content="My description">' . PHP_EOL
                  . '<meta name="keywords" content="keyword1, keyword2, keyword3">';

        $this->assertEquals($expected, $headMeta->toString());

    }
}
