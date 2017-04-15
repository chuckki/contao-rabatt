## Installation
Add to composer.json:
```
    "repositories" : [{
        "type" : "vcs",
        "url" : "https://github.com/chuckki/contao-rabatt.git"
    }],
```
Install via composer:
```
composer require chuckki/rabattbundle
```
Add this routing _before_ contao routing! Contao has a catch-all, so our route wont work.
```YAML
# app/config/routing.yml
RabattBundle:
    resource: "@ChuckkiRabattBundle/Resources/config/routing.yml"
# here comes contao routing    
```
```YAML
# app/config/config.yml
doctrine:
    orm:
        auto_generate_proxy_classes: %kernel.debug%
        auto_mapping: true
```

```PHP
# app/AppKernel.php
    public function registerBundles()
    {
        $bundles = [
        //...
            new Chuckki\RabattBundle\ChuckkiRabattBundle(),
        ];
    }
```
