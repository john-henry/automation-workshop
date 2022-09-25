<?php
/**
 * Yii Application Config
 *
 * Edit this file at your own risk!
 *
 * The array returned by this file will get merged with
 * vendor/craftcms/cms/src/config/app.php and app.[web|console].php, when
 * Craft's bootstrap script is defining the configuration for the entire
 * application.
 *
 * You can define custom modules and system components, and even override the
 * built-in system components.
 *
 * If you want to modify the application config for *only* web requests or
 * *only* console requests, create an app.web.php or app.console.php file in
 * your config/ folder, alongside this one.
 */

use Bugsnag\Shutdown\PhpShutdownStrategy;
use craft\helpers\App;
use craft\log\Dispatcher;
use craft\log\MonologTarget;
use Illuminate\Support\Collection;
use Logtail\Monolog\LogtailHandler;
use MeadSteve\MonoSnag\BugsnagHandler;

return [
    'id' => App::env('CRAFT_APP_ID') ?: 'CraftCMS',
    'modules' => [
        'my-module' => \modules\Module::class,
    ],
    //'bootstrap' => ['my-module'],
    'components' => [
        'log' => static function() {
            $dispatcher = new Dispatcher();
            $bugsnagApiKey = App::env('BUGSNAG_API_KEY');
            $logtailToken = App::env('LOGTAIL_TOKEN');
            $handlers = [];

            if ($bugsnagApiKey) {
                $bugsnagClient = Bugsnag\Client::make($bugsnagApiKey);
                $bugsnagClient->setReleaseStage(App::env('CRAFT_ENVIRONMENT'));
                $shutdownStrategy = new PhpShutdownStrategy();
                $shutdownStrategy->registerShutdownStrategy($bugsnagClient);
                $handlers[] = new BugsnagHandler($bugsnagClient);
            }

            if ($logtailToken) {
                $handlers[] = new LogtailHandler($logtailToken);
            }

            $monologTarget = new MonologTarget([
                'name' => 'monolog',
                'allowLineBreaks' => false,
            ]);
            $monologTarget->getLogger()->setHandlers($handlers);
            $dispatcher->targets = [$monologTarget];

            return $dispatcher;
        },
    ],
];
