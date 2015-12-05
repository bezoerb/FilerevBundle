ZoerbFilerevBundle
=====================

This bundle adds support for handling asset cachbusting rewrites based on a JSON configuration.
It enables you to drop assetic and use frontend tooling like `grunt` or `gulp` to build and rev your assets.
The only thing you need is the rev summary provided by [`gulp-rev`](https://github.com/sindresorhus/gulp-rev) or [`grunt-filerev`](https://github.com/yeoman/grunt-filerev) and this bundle will handle everything else.

The summary file should look something like this:
```json
{
  "/styles/main.css": "/styles/main.59983df7.css",
  "/scripts/main.js": "/scripts/main.c711a749.js"
}
```

## Installation
#### Step 1: Download
more to come

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

##### summary_file
Type: `string`
Default value: `'%kernel.root_dir%/config/filerev.json'`

Points to the rev summary file

##### enbled
Type: `bool`
Default value: `true`

##### debug
Type: `bool`
Default value: `%kernel.debug%`

##### root_dir

Type: `string`
Default Value: `'%kernel.root_dir%/../web'`

##### length

Type: `int`
Default: `8`

The number of characters of the file hash.


## Changelog

See [Changelog.md](Changelog.md)

## Can I contribute?

Of course. We appreciate all of our [contributors](https://github.com/bezoerb/FilerevBundle/graphs/contributors) and
welcome contributions to improve the project further. If you're uncertain whether an addition should be made, feel
free to open up an issue and we can discuss it.


## License
MIT © [Ben Zörb](http://sommerlaune.com)
