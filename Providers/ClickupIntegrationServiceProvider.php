<?php

namespace Modules\ClickupIntegration\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ClickupIntegration\Extensions\Bootstrap as BootstrapExtension;
use Modules\ClickupIntegration\Providers\Traits\IntegrationFields;
use Modules\ClickupIntegration\Services\ClickupService;
use Config;
use Eventy;
use Option;
use View;

class ClickupIntegrationServiceProvider extends ServiceProvider
{
    use IntegrationFields;

    public const URL = 'https://app.clickup.com';
    public const MODULE_NAME = 'clickupintegration';
    public const SECTION_NAME = 'clickup';

    /**
     * Thread action types
     */
    const ACTION_TYPE_TASK_LINKED = 100;

    private $action_types = [
        self::ACTION_TYPE_TASK_LINKED => 'clickup-task-linked'
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * Register bindings and the bundled library autoloader.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLibraryAutoloader();
    }

    /**
     * Autoloads the bundled html-to-markdown library (lib/html-to-markdown/src)
     * under the League\HTMLToMarkdown\ prefix. A class is only loaded from here
     * if it is not already defined, so a FreeScout version that starts shipping
     * the same library globally takes priority and nothing collides.
     *
     * @return void
     */
    protected function registerLibraryAutoloader()
    {
        $prefix = 'League\\HTMLToMarkdown\\';
        $baseDir = __DIR__.'/../lib/html-to-markdown/src/';

        spl_autoload_register(function ($class) use ($prefix, $baseDir) {
            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                return;
            }

            if (class_exists($class, false)) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $file = $baseDir.str_replace('\\', '/', $relative).'.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->hooks();
        $this->extensions();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        /**
         * Filter to allow a new sidebar section (ClickUp) to be added inside Manage -> Settings page.
         */
        Eventy::addFilter('settings.sections', function($sections) {
            $sections[self::SECTION_NAME] = ['title' => __('ClickUp'), 'icon' => 'chevron-up', 'order' => 400];
            return $sections;
        }, 10);

        /**
         * Filter that returns settings used within the ClickUp page,
         * All options or configurations to interact with our settings page
         */
        Eventy::addFilter('settings.section_settings', function($settings, $section) {
            if ($section === self::SECTION_NAME) {
                $clickupService = new ClickupService();

                $options = [
                    // General
                    self::MODULE_FIELDS[self::FIELD_API_TOKEN],
                    self::MODULE_FIELDS[self::FIELD_ENABLED],
                    self::MODULE_FIELDS[self::FIELD_ENVIRONMENT],
                    'integration_status',
                    'environments',
                    // Linking configuration
                    self::MODULE_FIELDS[self::FIELD_TEAM_ID],
                    self::MODULE_FIELDS[self::FIELD_SPACE_ID],
                    self::MODULE_FIELDS[self::FIELD_LIST_ID],
                    // Custom Fields configuration
                    self::MODULE_FIELDS[self::FIELD_FREESCOUT_ID],
                    self::MODULE_FIELDS[self::FIELD_FREESCOUT_URL],
                    self::MODULE_FIELDS[self::FIELD_SUBMITTER_NAME],
                    self::MODULE_FIELDS[self::FIELD_SUBMITTER_EMAIL],
                    self::MODULE_FIELDS[self::FIELD_DEFAULT_CUSTOM_FIELDS],
                ];

                $defaults = [
                    'integration_status' => $clickupService->isAuthorized(),
                    'environments' => Config::get(self::MODULE_NAME . '.options.environments')
                ];

                # Filters to extend options values and defaults
                $options = Eventy::filter('settings.clickupintegration.options', $options);
                $defaults = Eventy::filter('settings.clickupintegration.defaults', $defaults);

                # Getting final options
                $settings = Option::getOptions($options, $defaults);
            }
            return $settings;
        }, 10, 2);

        /**
         * Filter that returns the view path for the page that is being visualized
         */
        Eventy::addFilter('settings.view', function($viewPath, $section) {
            if ($section === self::SECTION_NAME) {
                $viewPath = self::MODULE_NAME . '::' . $viewPath;
            }
            return $viewPath;
        }, 10, 2);

        /**
         * Filter that registers new action types (Notes that are added to thread conversations)
         */
        Eventy::addFilter('thread.action_types', function(array $action_types) {
            $action_types[self::ACTION_TYPE_TASK_LINKED] = $this->action_types[self::ACTION_TYPE_TASK_LINKED];
            return $action_types;
        }, 10);

        /**
         * Filter that translated a note based on the custom types added
         */
        Eventy::addFilter('thread.action_text', function($actionText, $thread) {
            if ($thread->action_type === self::ACTION_TYPE_TASK_LINKED) {
                $actionText = $thread->body;
            }
            return $actionText;
        }, 10, 2);

        /**
         * Action that is executed once a conversation is open (Not draft) to show the Linking integration
         */
        Eventy::addAction('conversation.after_prev_convs', function($customer, $conversation) {
            // Skip if no customer (e.g. a draft email)
            if (empty($customer)) {
                return;
            }

            if (! self::isEnabled()) {
                return;
            }

            echo View::make(self::MODULE_NAME . '::conversation/sidebar', [
                'conversation' => $conversation
            ])->render();
        }, -1, 3);
    }

    /**
     * Register Extensions
     *
     * @return void
     */
    protected function extensions()
    {
        BootstrapExtension::load();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('clickupintegration.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'clickupintegration'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/clickupintegration');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/clickupintegration';
        }, Config::get('view.paths')), [$sourcePath]), 'clickupintegration');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
