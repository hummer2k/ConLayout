<?php

namespace ConLayoutTest\Listener;

use ConLayout\Filter\BasePathFilter;
use ConLayout\Filter\CacheBusterFilter;
use ConLayout\Listener\ViewHelperListener;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\View\Helper\Proxy\HeadLinkProxy;
use ConLayout\View\Helper\Proxy\HeadMetaProxy;
use ConLayout\View\Helper\Proxy\HeadScriptProxy;
use ConLayout\View\Helper\Proxy\HeadTitleProxy;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Bootstrap;
use Zend\Config\Config;
use Zend\Filter\FilterPluginManager;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
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
            LayoutUpdaterInterface::INSTRUCTION_VIEW_HELPERS => [
                'doctype' => 'HTML5',
                'headLink' => [
                    'main'   => '/css/main.css',
                    'busted' => 'busted.css',
                    'test'   => [
                        'method' => 'prependStylesheet',
                        'href' => '/css/test.css'
                    ]
                ],
                'headTitle' => [
                    'default' => 'My Title',
                    'another' => [
                        'value' => 'Another Title',
                        'depends' => 'default'
                    ],
                    'first' => [
                        'method' => 'prepend',
                        'value' => 'First'
                    ]
                ],
                'headMeta' => [
                    'charset' => [
                        'method' => 'setCharset',
                        'charset' => 'utf8'
                    ],
                    'desc' => [
                        'name' => 'description',
                        'content' => 'My description'
                    ],
                    'keywords' => [
                        'name' => 'keywords',
                        'content' => 'keyword1, keyword2, keyword3'
                    ]
                ],
                'headScript' => [
                    'jquery-ui' => '/js/jquery-ui.min.js',
                    'jquery' => [
                        'method' => 'prependFile',
                        'src' => '/js/jquery.min.js'
                    ],
                    'modernizr' => [
                        'method' => 'appendFile',
                        'src' => '/js/modernizr.js',
                        'type' => 'text/javascript',
                        'attrs' => ['conditional' => 'lt IE 9']
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
        $helperPluginManager->setServiceLocator(new ServiceManager);

        $filterManager = new FilterPluginManager();

        $basePath = $helperPluginManager->get('basePath');
        $basePath->setBasePath('/assets');

        $basePathFilter = new BasePathFilter($basePath);
        $filterManager->setService('basePath', $basePathFilter);
        $cacheBusterFilter = new CacheBusterFilter(__DIR__ . '/_files');
        $filterManager->setService('cacheBuster', $cacheBusterFilter);

        $listener = new ViewHelperListener(
            $this->layoutUpdater,
            $helperPluginManager,
            $filterManager,
            $config['con-layout']['view_helpers']
        );

        $mvcEvent = new MvcEvent();

        $renderer = new PhpRenderer();
        $renderer->setHelperPluginManager($helperPluginManager);

        /* @var $headLink HeadLink */
        $headLink = $helperPluginManager->get('headLink');
        $headLinkProxy = new HeadLinkProxy($headLink);
        $helperPluginManager->setService(get_class($headLinkProxy), $headLinkProxy);
        /* @var $headScript HeadScript */
        $headScript = $helperPluginManager->get('headScript');
        $headScriptProxy = new HeadScriptProxy($headScript);
        $helperPluginManager->setService(get_class($headScriptProxy), $headScriptProxy);
        /* @var $doctype Doctype */
        $doctype = $helperPluginManager->get('doctype');
        /* @var $headTitle HeadTitle */
        $headTitle = $helperPluginManager->get('headTitle');
        $headTitleProxy = new HeadTitleProxy($headTitle);
        $helperPluginManager->setService(get_class($headTitleProxy), $headTitleProxy);
        /* @var $headMeta HeadMeta */
        $headMeta = $helperPluginManager->get('headMeta');
        $headMetaProxy = new HeadMetaProxy($headMeta);
        $helperPluginManager->setService(get_class($headMetaProxy), $headMetaProxy);
        $headMeta->setView($renderer);

        $listener->applyViewHelpers($mvcEvent);

        foreach (['test.css', 'main.css', 'busted.css?v='] as $expected) {
            $this->assertContains($expected, $headLink->toString());
        }

        foreach (['jquery.min.js', 'jquery-ui.min.js'] as $expected) {
            $this->assertContains($expected, $headScript->toString());
        }

        $this->assertEquals('<!DOCTYPE html>', (string) $doctype);

        $headTitle->setSeparator(' | ');
        $expected = 'First | My Title | Another Title';
        $this->assertContains($expected, $headTitle->toString());

        $contains = [
            'charset="utf8"',
            'name="description" content="My description"',
            'name="keywords" content="keyword1, keyword2, keyword3"',
        ];

        foreach ($contains as $expected) {
            $this->assertContains($expected, $headMeta->toString());
        }
    }
}
