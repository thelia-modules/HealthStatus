# Health Status

Find out information about the health of your Thelia installation.

## en_US

### Description

This module allows you to check the health of your Thelia installation. It provides a page where you can see the status of your modules and more information about the health of your website.

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is HealthStatus.
* Activate it in your Thelia administration panel

### Composer

```bash
composer require thelia/health-status-module:~1.0
```

## Usage

Once activated, you can access the health status page by going to the following URL: ```/admin/module/HealthStatus/show```
The module works on a system of event. 
It sends a GenericEvent with the name "module.config", all the modules can listen to this event and add their own health checks.

## Extending the module

You can add your own health checks by listening to the "module.config" event.
You have to create a new EventListener on the module you want to add the health check to.

Here is an example of a an EventListener that listens to the "module.config" event.

### Example

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
> If you're running Thelia version 2.4 or lower, you need to add the following code to services.xml for the EventListener to work, but also on slightly older modules. ( In Thelia 2.5 and newer modules, the EventListener is automatically detected with autowiring. )

### Example
```xml
<service id="mymodule.config.listener" class="MyModule\EventListener\ConfigListener">
    <tag name="kernel.event_listener" event="module.config" method="onModuleConfig"/>
</service>
```

## fr_FR

### Description

Ce module vous permet de vérifier l'état de votre installation Thelia. Il fournit une page où vous pouvez voir l'état de vos modules et plus d'informations sur la santé de votre site web.

## Installation

### Manuellement

* Copiez le module dans le répertoire ```<thelia_root>/local/modules/``` et assurez-vous que le nom du module est bien HealthStatus.
* Activez-le dans votre panneau d'administration Thelia

### Composer

```bash
composer require thelia/health-status-module:~1.0
```

## Utilisation

Une fois activé, vous pouvez accéder à la page de statut de santé en allant à l'URL suivante: ```/admin/module/HealthStatus/show```
Le module fonctionne sur un système d'événements.
Il envoie un GenericEvent avec le nom "module.config", tous les modules peuvent écouter cet événement et ajouter leurs propres vérifications de santé.

## Étendre le module

Vous pouvez ajouter vos propres vérifications en écoutant l'événement "module.config".
Vous devez créer un nouvel EventListener sur le module auquel vous souhaitez ajouter la vérification.

Voici un exemple d'un EventListener qui écoute l'événement "module.config".

### Exemple

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
> Si vous exécutez une version 2.4 de Thelia ou inférieure, vous devez ajouter le code suivant au services.xml pour que l'EventListener fonctionne, mais aussi sur les modules qui sont un peu plus anciens. ( Dans Thelia 2.5 et pour les modules plus récents, l'EventListener est automatiquement détecté avec l'autowiring. )

### Exemple
```xml
<service id="mymodule.config.listener" class="MyModule\EventListener\ConfigListener">
    <tag name="kernel.event_listener" event="module.config" method="onModuleConfig"/>
</service>
```





