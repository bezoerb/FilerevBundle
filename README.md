ZoerbFilerevBundle
=====================

This bundle adds support for handling asset cachbusting rewrites based on a JSON configuration.
It enables you to drop assetic and use frontend tooling like `grunt` or `gulp` to build and rev your assets.
The only thing you need is the rev summary provided by [`gulp-rev`](https://github.com/sindresorhus/gulp-rev) or [`grunt-filerev`](https://github.com/yeoman/grunt-filerev) and this bundle will handle everything else.

## Installation
#### Step 1: Download
 to come

#### Step 2: Enable the bundle
Finally, enable the bundle in the kernel:

```php
<?php
// app/appKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Zoerb\Bundle\ZoerbFilerevBundle(),
    );
}
```
## Configuration

Add the following configuration to your `app/config/config.yml`:

    zoerb_filerev: ~

## Options

to come

## Changelog

See [Changelog.md](Changelog.md)

## Can I contribute?

Of course. We appreciate all of our [contributors](https://github.com/bezoerb/FilerevBundle/graphs/contributors) and
welcome contributions to improve the project further. If you're uncertain whether an addition should be made, feel
free to open up an issue and we can discuss it.


## License
MIT © [Ben Zörb](http://sommerlaune.com)
