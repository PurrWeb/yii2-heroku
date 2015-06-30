# yii2-heroku


This extension allows you to generate environment files into your yii2 advanced application for easy deployment on Heroku. The extension may be helpful if you don't have stage or test server and you need to show somebody how your app works. Before using the extension make sure you have a Heroku account and you know how to create an app and work with it.

## Installation

    php composer.phar require --dev --prefer-dist purrweb/yii2-heroku "*"
or add in composer.json (require-dev section)

    "purrweb/yii2-heroku": "*"

## Usage

Add the following in console/config/main-local.php:

```php
<?php
return [
	...
    'controllerMap' => [
        'heroku' => [
            'class' => 'purrweb\heroku\HerokuGeneratorController',
        ],
    ],
	...
];
```

Now you can run next console command to generate files that Heroku needs:

    $ php yii heroku

You will be asked to overwrite index.php in the environment directory. If you did not change that file you can answer Yes, overwrite. If you did change that file you should merge the file from this extension and file from your application manually. The command will generate 3 files in application root folder and a directory "heroku" with environment files in your environments directory.

In your application root directory you will see new untracked files:

* "Procfile", simple Heroku's Procfile that runs nginx web-server on heroku
* "nginx.conf", simple nginx config that allows backend and fronted apps to work on one Heroku's dyno
* "post-install-cmd.sh", bash script that will start automatically after you do git push on Heroku

Next step you need to add in your composer.json following script section:

```
"scripts": {
        "post-install-cmd": [
            "sh post-install-cmd.sh"
        ]
    }
```

In your environments directory, you can open new files and edit them. After that run "git add" and "git commit" commands manually to add all generated files into your repository. Now you can push into Heroku. If everything goes well, you will be able to follow the URL given by Heroku. To run your yii2 backend app just follow the link: {your app URL}/backend.

If you use third party libraries, don't forget include it into your composer.json file. For example, if you use php5-imagick library, add next line into "require" section in the composer.json:

```
"ext-imagick": "*"
```

If you're gonna use database on Heroku (e.g. "cleardb"), make sure that it's properly described in the environments/heroku/common/config/main-local.php (by default this file is configured to connect to a "cleardb" database). If you need to run the "migrate" command, you can do that easily through Heroku's bash. Just run Heroku's bash

    $ heroku run bash

and do migrate command

    $ php yii migrate
