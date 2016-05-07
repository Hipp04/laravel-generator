<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorConfig
{
    /* Namespace variables */
    public $nsApp;
    public $nsRepository;
    public $nsModel;
    public $nsDataTables;
    public $nsModelExtend;

    public $nsApiController;
    public $nsApiRequest;

    public $nsRequest;
    public $nsRequestBase;
    public $nsController;

    /* Path variables */
    public $pathRepository;
    public $pathModel;
    public $pathDataTables;

    public $pathApiController;
    public $pathApiRequest;
    public $pathApiRoutes;
    public $pathApiTests;
    public $pathApiTestTraits;

    public $pathController;
    public $pathRequest;
    public $pathRoutes;
    public $pathResourceRoutes;
    public $pathViews;

    /* Model Names */
    public $mName;
    public $mPlural;
    public $mCamel;
    public $mCamelPlural;
    public $mSnake;
    public $mSnakePlural;

    public $forceMigrate;

    /* Generator Options */
    public $options;

    /* Command Options */
    public static $availableOptions = ['fieldsFile', 'jsonFromGUI', 'tableName', 'fromTable', 'save', 'primary', 'prefix', 'paginate', 'skipDumpOptimized'];

    public $tableName;

    /* Generator AddOns */
    public $addOns;

    public function init(CommandData &$commandData)
    {
        $this->mName = $commandData->modelName;

        $this->prepareOptions($commandData);
        $this->prepareAddOns();
        $this->prepareModelNames();
        $this->loadNamespaces($commandData);
        $this->loadPaths();
        $commandData = $this->loadDynamicVariables($commandData);
    }

    public function loadNamespaces(CommandData &$commandData)
    {
        $prefix = $this->getOption('prefix');

        if (!empty($prefix)) {
            $prefix = '\\'.Str::title($prefix);
        }

        $this->nsApp = $commandData->commandObj->getLaravel()->getNamespace();
        $this->nsRepository = config('resource_generator.namespace.repository', 'App\Repositories').$prefix;
        $this->nsModel = config('resource_generator.namespace.model', 'App\Models').$prefix;
        $this->nsDataTables = config('resource_generator.namespace.datatables', 'App\DataTables').$prefix;
        $this->nsModelExtend = config(
            'resource_generator.model_extend_class',
            'Illuminate\Database\Eloquent\Model'
        );

        $this->nsApiController = config(
            'resource_generator.namespace.api_controller',
            'App\Http\Controllers\API'
        ).$prefix;
        $this->nsApiRequest = config('resource_generator.namespace.api_request', 'App\Http\Requests\API').$prefix;

        $this->nsRequest = config('resource_generator.namespace.request', 'App\Http\Requests').$prefix;
        $this->nsRequestBase = config('resource_generator.namespace.request', 'App\Http\Requests');
        $this->nsController = config('resource_generator.namespace.controller', 'App\Http\Controllers').$prefix;
    }

    public function loadPaths()
    {
        $prefix = $this->getOption('prefix');

        if (!empty($prefix)) {
            $prefixTitle = Str::title($prefix).'/';
        } else {
            $prefixTitle = '';
        }

        $this->pathRepository = config(
            'resource_generator.path.repository',
            app_path('Repositories/')
        ).$prefixTitle;

        $this->pathModel = config('resource_generator.path.model', app_path('Models/')).$prefixTitle;

        $this->pathDataTables = config('resource_generator.path.datatables', app_path('DataTables/')).$prefixTitle;

        $this->pathApiController = config(
            'resource_generator.path.api_controller',
            app_path('Http/Controllers/API/')
        ).$prefixTitle;

        $this->pathApiRequest = config(
            'resource_generator.path.api_request',
            app_path('Http/Requests/API/')
        ).$prefixTitle;

        $this->pathApiRoutes = config('resource_generator.path.api_routes', app_path('Http/api_routes.php'));

        $this->pathApiTests = config('resource_generator.path.api_test', base_path('tests/'));

        $this->pathApiTestTraits = config('resource_generator.path.test_trait', base_path('tests/traits/'));

        $this->pathController = config(
            'resource_generator.path.controller',
            app_path('Http/Controllers/')
        ).$prefixTitle;

        $this->pathRequest = config('resource_generator.path.request', app_path('Http/Requests/')).$prefixTitle;

        $this->pathRoutes = config('resource_generator.path.routes', app_path('Http/routes.php'));
        $this->pathResourceRoutes = config('resource_generator.path.resource_routes', app_path('Http/Routes/resource.php'));

        $this->pathViews = config(
            'resource_generator.path.views',
            base_path('resources/views/')
        ).$prefix.'/'.$this->mCamelPlural.'/';
    }

    public function loadDynamicVariables(CommandData &$commandData)
    {
        $commandData->addDynamicVariable('$NAMESPACE_APP$', $this->nsApp);
        $commandData->addDynamicVariable('$NAMESPACE_REPOSITORY$', $this->nsRepository);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL$', $this->nsModel);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL$', $this->nsModel);
        $commandData->addDynamicVariable('$NAMESPACE_DATATABLES$', $this->nsDataTables);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL_EXTEND$', $this->nsModelExtend);

        $commandData->addDynamicVariable('$NAMESPACE_API_CONTROLLER$', $this->nsApiController);
        $commandData->addDynamicVariable('$NAMESPACE_API_REQUEST$', $this->nsApiRequest);

        $commandData->addDynamicVariable('$NAMESPACE_CONTROLLER$', $this->nsController);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST$', $this->nsRequest);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST_BASE$', $this->nsRequestBase);

        $this->prepareTableName();

        $commandData->addDynamicVariable('$TABLE_NAME$', $this->tableName);
        $commandData->addDynamicVariable('$TABLE_NAME_STUDLY$', Str::studly($this->tableName));

        $commandData->addDynamicVariable('$MODEL_NAME$', $this->mName);
        $commandData->addDynamicVariable('$MODEL_NAME_CAMEL$', $this->mCamel);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL$', $this->mPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_CAMEL$', $this->mCamelPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SNAKE$', $this->mSnake);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SNAKE$', $this->mSnakePlural);

        if ($this->getOption('prefix')) {
            $prefixRoutes = $this->getOption('prefix').'/';
            $prefixTitle = Str::title($this->getOption('prefix')).'\\';
            $prefixAs = $this->getOption('prefix').'.';
        } else {
            $prefixRoutes = '';
            $prefixTitle = '';
            $prefixAs = '';
        }

        $commandData->addDynamicVariable('$ROUTES_PREFIX$', $prefixRoutes);
        $commandData->addDynamicVariable('$NS_PREFIX$', $prefixTitle);
        $commandData->addDynamicVariable('$ROUTES_AS_PREFIX$', $prefixAs);

        $commandData->addDynamicVariable(
            '$API_PREFIX$',
            config('resource_generator.api_prefix', 'api')
        );

        $commandData->addDynamicVariable(
            '$API_VERSION$',
            config('resource_generator.api_version', 'v1')
        );

        return $commandData;
    }

    public function prepareTableName()
    {
        if ($this->getOption('tableName')) {
            $this->tableName = $this->getOption('tableName');
        } else {
            $this->tableName = $this->mSnakePlural;
        }
    }

    public function prepareModelNames()
    {
        $this->mPlural = Str::plural($this->mName);
        $this->mCamel = Str::camel($this->mName);
        $this->mCamelPlural = Str::camel($this->mPlural);
        $this->mSnake = Str::snake($this->mName);
        $this->mSnakePlural = Str::snake($this->mPlural);
    }

    public function prepareOptions(CommandData &$commandData, $options = null)
    {
        if (empty($options)) {
            $options = self::$availableOptions;
        }

        foreach ($options as $option) {
            $this->options[$option] = $commandData->commandObj->option($option);
        }

        if (isset($options['fromTable']) and $this->options['fromTable']) {
            if (!$this->options['tableName']) {
                $commandData->commandError('tableName required with fromTable option.');
                exit;
            }
        }

        $this->options['softDelete'] = config('resource_generator.options.softDelete', false);
    }

    public function overrideOptionsFromJsonFile($jsonData)
    {
        $options = self::$availableOptions;

        foreach ($options as $option) {
            if (isset($jsonData['options'][$option])) {
                $this->setOption($option, $jsonData['options'][$option]);
            }
        }

        $addOns = ['swagger', 'tests', 'datatables'];

        foreach ($addOns as $addOn) {
            if (isset($jsonData['addOns'][$addOn])) {
                $this->addOns[$addOn] = $jsonData['addOns'][$addOn];
            }
        }
    }

    public function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return false;
    }

    public function getAddOn($addOn)
    {
        if (isset($this->addOns[$addOn])) {
            return $this->addOns[$addOn];
        }

        return false;
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function prepareAddOns()
    {
        $this->addOns['swagger'] = config('resource_generator.add_on.swagger', false);
        $this->addOns['tests'] = config('resource_generator.add_on.tests', false);
        $this->addOns['datatables'] = config('resource_generator.add_on.datatables', false);
        $this->addOns['menu.enabled'] = config('resource_generator.add_on.menu.enabled', false);
        $this->addOns['menu.menu_file'] = config('resource_generator.add_on.menu.menu_file', 'layouts.menu');
    }
}
