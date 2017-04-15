## Installation

```YAML
# app/config/routing.yml
RabattBundle:
    resource: "@ChuckkiRabattBundle/Resources/config/routing.yml"
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
