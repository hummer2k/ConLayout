# Embed blocks or view helpers in other applications

## Plain php example

````php
<?php
// put this file to your public directory
chdir(dirname(__DIR__));
require 'vendor/autoload.php';
// initialize zf2 application
$application = Zend\Mvc\Application::init(require 'config/application.config.php');
// retrieve service manager
$sm = $application->getServiceManager();

/* @var $layout ConLayout\Layout\LayoutInterface */
$layout = $sm->get('Layout');
$layout->load();

/* @var $view \Zend\View\View */
$view = $sm->get('View');

/* @var $renderer Zend\View\Renderer\PhpRenderer */
$renderer = $sm->get(Zend\View\Renderer\PhpRenderer::class);
?>
<!doctype html>
<html>
    <head>
        <?= $renderer->headMeta() ?>
        <?= $renderer->headTitle() ?>
        <?= $renderer->headLink() ?>
        <?= $renderer->headScript() ?>
    </head>
    <body>
        <?= $view->render($layout->getBlock('header')) ?>
        <section class="content container">
            <div class="jumbotron">
                <h1><?= sprintf($renderer->translate('Welcome to %sMy Application!%s'), '<span class="zf-green">', '</span>') ?></h1>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
            </div>
            <?= $view->render($layout->getBlock('footer')) ?>
        </section>
        <?= $renderer->inlineScript() ?>
    </body>
</html>
````