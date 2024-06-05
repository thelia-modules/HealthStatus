# Health Status

Find out information about the health of your Thelia installation.

## en_US

### Description

This module allows you to check the health of your Thelia installation. It provides a page where you can see the status
of your modules and more information about the health of your website.

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is
  HealthStatus.
* Activate it in your Thelia administration panel

### Composer

```bash
composer require thelia/health-status-module:~1.0
```

## Usage

Once activated, you can access the health status page by going to the following
URL: ```/admin/module/HealthStatus/show```
The module allows you to view the latest versions of your modules, and to see if your modules are up-to-date. You can
also see whether your Thelia installation is up-to-date, and whether your server is correctly configured.
At the same time, you can keep an eye on the configuration of your modules, and see if everything is set up correctly.

## Extending the module

The module lets you add your own checks by listening to the "module.config" event. It works through a GenericEvent that
is listened to by an EventListener.
This EventListener must be created in the module you wish to add the verification(s) to.
Here's an example of an EventListener that listens to the "module.config" event.

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
> If you're running Thelia version 2.4 or lower, you need to add the following code to config.xml for the EventListener
> to work, but also on slightly older modules. ( In Thelia 2.5 and newer modules, the EventListener is automatically
> detected with autowiring. )

### Example

```xml

<service id="mymodule.config.listener" class="MyModule\EventListener\ConfigListener">
    <tag name="kernel.event_listener" event="module.config" method="onModuleConfig"/>
</service>
```

## fr_FR

### Description

Ce module vous permet de vérifier l'état de votre installation Thelia. Il fournit une page où vous pouvez voir l'état de
vos modules et plus d'informations sur la santé de votre site web.

## Installation

### Manuellement

* Copiez le module dans le répertoire ```<thelia_root>/local/modules/``` et assurez-vous que le nom du module est bien
  HealthStatus.
* Activez-le dans votre panneau d'administration Thelia

### Composer

```bash
composer require thelia/health-status-module:~1.0
```

## Utilisation

Une fois activé, vous pouvez accéder à la page de statut de santé en allant à l'URL
suivante: ```/admin/module/HealthStatus/show```
Le module vous permet les dernières versions de vos modules, et de voir si vos modules sont à jour. De plus, vous pouvez
voir si votre installation Thelia est à jour, mais aussi si votre serveur est configuré correctement.
Parrallèlement, vous pouvez garder un oeil sur la configuration de vos modules, et voir si tout est correctement
configuré.

## Étendre le module

Le module vous permet d'ajouter vos propres vérifications en écoutant l'événement "module.config". Il fonctionne grâce à
un GenericEvent qui est écouter par un EventListener.
Cet EventListener doit être créé dans le module que vous souhaitez ajouter la ou les vérification(s).
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
> Si vous exécutez une version 2.4 de Thelia ou inférieure, vous devez ajouter le code suivant au config.xml pour que
> l'EventListener fonctionne, mais aussi sur les modules qui sont un peu plus anciens. ( Dans Thelia 2.5 et pour les
> modules plus récents, l'EventListener est automatiquement détecté avec l'autowiring. )

### Exemple

```xml

<service id="mymodule.config.listener" class="MyModule\EventListener\ConfigListener">
    <tag name="kernel.event_listener" event="module.config" method="onModuleConfig"/>
</service>
```




