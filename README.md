## Installation

```YAML
# app/config/routing.yml
RabattBundle:
    resource: "@ProriRabattBundle/Controller/"
    type:     annotation
    prefix:   /rabatt/
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
            new Prori\RabattBundle\ProriRabattBundle(),
        ];
    }
```
