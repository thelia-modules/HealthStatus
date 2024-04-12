# Health Status

Find out information about the health of your Thelia installation.

## Installation

---
### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is HealthStatus.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```bash
composer require thelia/health-status-module:~1.0
```

## Usage

---
Once activated, you can access the health status page by going to the following URL: ```/admin/module/HealthStatus/show```
The module works on a system of event. 
It sends a GenericEvent with the name "module.config", all the modules can listen to this event and add their own health checks.

## Extending the module

---

You can add your own health checks by listening to the "module.config" event.
You have to create a new EventListener on the module you want to add the health check to. 
Don't forget to add the tag "kernel.event_listener" with an event name in the services.xml file.

Here is an example of a an EventListener that listens to the "module.config" event.

```php

namespace MyModule\EventListener;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MyModuleHealthCheckListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'module.config' => [
                'onModuleConfig', 128
                ],
        ];
    }
    
    public function onModuleConfig(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if ($subject !== "HealthStatus") {
            throw new \RuntimeException('Event subject does not match expected value');
        }
           // Add your code here, for example check if a configuration is set
    }
}
```

> [!CAUTION]
> If you are running a 2.4 version of Thelia or lower, you have to add the following code to the services.xml to make the EventListener work.

```xml
<service id="mymodule.config.listener" class="MyModule\EventListener\ConfigListener">
    <tag name="kernel.event_listener" event="module.config" method="onModuleConfig"/>
</service>
```







