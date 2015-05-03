# ACL

An Event is fired before the block instance is injected into the layout where
you can determine whether the block is allowed to be shown.

(return true or false in your event listener)

````php
<?php
// Module.php
/**
 * @param \Zend\EventManager\EventInterface $e
 */
public function onBootstrap(EventInterface $e)
{
    $application    = $e->getApplication();
    $eventManager   = $application->getEventManager();

    // get the authorization service
    $authService = $application->getServiceLocator('My\AuthService');

    $eventManager->getSharedManager()
        ->attach('ConLayout\Layout\Layout', 'isAllowed', function($e) use ($authService) {
        $resource = $e->getParam('block_id');
        return $authService->isGranted($resource);
    });
}
````
