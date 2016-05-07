<?php

namespace InfyOm\Generator\Commands\Publish;

use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class GeneratorPublishCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes & init api routes, base controller, base test cases traits.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->publishRoutes();
        $this->initAPIRoutes();
        $this->publishTestCases();
        $this->publishBaseController();
    }

    /**
     * Publishes API and Resource Routes
     */
    public function publishRoutes()
    {
        // create Http/Routes if not present
        if (!is_dir(app_path('Http/Routes'))) {
            try {
                mkdir(app_path('Http/Routes'));
            } catch (\Exception $e) {
                $this->error('Failed to create Routes directory');
            }
        }

        // publish the routes
        $this->publishAPIRoutes();
        $this->publishResourceRoutes();
    }

    /**
     * Publishes Routes/resource.php.
     */
    public function publishResourceRoutes()
    {
        $routesPath = __DIR__.'/../../../templates/api/routes/resource_routes.stub';

        $apiRoutesPath = config('resource_generator.path.resource_routes', app_path('Http/Routes/resource.php'));

        $this->publishFile($routesPath, $apiRoutesPath, 'resource.php');
    }

    /**
     * Publishes Routes/api.php.
     */
    public function publishAPIRoutes()
    {
        $routesPath = __DIR__.'/../../../templates/api/routes/api_routes.stub';

        $apiRoutesPath = config('resource_generator.path.api_routes', app_path('Http/Routes/api.php'));

        $this->publishFile($routesPath, $apiRoutesPath, 'api.php');
    }

    /**
     * Initialize routes group based on route integration.
     */
    private function initAPIRoutes()
    {
        $path = config('resource_generator.path.routes', app_path('Http/routes.php'));

        $routeContents = file_get_contents($path);

        $template = 'api.routes.api_routes_group';

        $templateData = TemplateUtil::getTemplate($template, 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        file_put_contents($path, $routeContents.PHP_EOL.$templateData);
        $this->comment(PHP_EOL."API and resource groups added to routes.php");
    }

    private function publishTestCases()
    {
        $traitPath = __DIR__.'/../../../templates/test/api_test_trait.stub';

        $testsPath = config('resource_generator.path.api_test', base_path('tests/'));

        $this->publishFile($traitPath, $testsPath.'ApiTestTrait.php', 'ApiTestTrait.php');

        if (!file_exists($testsPath.'traits/')) {
            mkdir($testsPath.'traits/');
            $this->info('traits directory created');
        }
    }

    private function publishBaseController()
    {
        $templateData = TemplateUtil::getTemplate('app_base_controller', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $controllerPath = config('resource_generator.path.controller', app_path('Http/Controllers/'));

        $fileName = 'AppBaseController.php';

        if (file_exists($controllerPath.$fileName)) {
            $answer = $this->ask('Do you want to overwrite '.$fileName.'? (y|N) :', false);

            if (strtolower($answer) != 'y' and strtolower($answer) != 'yes') {
                return;
            }
        }

        FileUtil::createFile($controllerPath, $fileName, $templateData);

        $this->info('AppBaseController created');
    }

    /**
     * Replaces dynamic variables of template.
     *
     * @param string $templateData
     *
     * @return string
     */
    private function fillTemplate($templateData)
    {
        $apiVersion = config('resource_generator.api_version', 'v1');
        $apiPrefix = config('resource_generator.api_prefix', 'api');
        $apiNamespace = config(
            'resource_generator.namespace.api_controller',
            'App\Http\Controllers\API'
        );

        $templateData = str_replace('$API_VERSION$', $apiVersion, $templateData);
        $templateData = str_replace('$NAMESPACE_API_CONTROLLER$', $apiNamespace, $templateData);
        $templateData = str_replace('$API_PREFIX$', $apiPrefix, $templateData);
        $templateData = str_replace(
            '$NAMESPACE_CONTROLLER$',
            config('resource_generator.namespace.controller'),
            $templateData
        );

        return $templateData;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}
