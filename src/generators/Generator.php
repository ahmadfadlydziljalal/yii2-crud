<?php

namespace dzil\yii2_crud\generators;

use dzil\yii2_crud\Behavior;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\TableSchema;
use yii\gii\CodeFile;

/**
 * Generates CRUD
 *
 * @property array $columnNames Model column names. This property is read-only.
 * @property string $controllerID The controller ID (without the module ID prefix). This property is read-only.
 * @property array $searchAttributes Searchable attributes. This property is read-only.
 * @property boolean|TableSchema $tableSchema This property is read-only.
 * @property string $viewPath The controller view path. This property is read-only.
 * @mixin Behavior
 *
 * @author Ahmad Fadly Dzil Jalal <ahmadfadlydziljalal@gmail.com>
 * @since 1.0
 */
class Generator extends \yii\gii\Generator
{

    const DEFAULT_TEMPLATE = 'default';
    const MASTER_DETAIL_TEMPLATE = 'master-details';
    const MASTER_DETAIL_DETAIL_TEMPLATE = 'master-details-details';
    const AJAX_DEFAULT_TEMPLATE = 'ajax-default';
    const AJAX_MASTER_DETAIL_TEMPLATE = 'ajax-master-details';
    const AJAX_MASTER_DETAIL_DETAIL_TEMPLATE = 'ajax-master-details-details';

    public ?string $modelClass = '';
    public ?string $controllerClass = '';
    public ?string $viewPath = '';
    public ?string $baseControllerClass = 'yii\web\Controller';
    public ?string $searchModelClass = '';
    public ?string $modelsClassDetail = '';
    public ?string $modelsClassDetailDetail = '';
    public ?string $labelID = '';

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(),[
            Behavior::class,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Dzil CRUD Generator';
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function generate(): array
    {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        $files = [
            new CodeFile($controllerFile, $this->render('controller.php')),
        ];

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            $files[] = new CodeFile($searchModel, $this->render('search.php'));
        }

        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';
        foreach (scandir($templatePath) as $file) {

            if (empty($this->searchModelClass) && $file === '_search.php') {
                continue;
            }

            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
            }
        }

        // hanya untuk master-detail
        $canCreateService = [
            Generator::MASTER_DETAIL_TEMPLATE,
            Generator::MASTER_DETAIL_DETAIL_TEMPLATE,
            Generator::AJAX_MASTER_DETAIL_TEMPLATE,
            Generator::AJAX_MASTER_DETAIL_DETAIL_TEMPLATE,
        ];

        if (in_array($this->template, $canCreateService)) {

            $serviceFile = '';

            if($this->template == Generator::MASTER_DETAIL_TEMPLATE || $this->template == Generator::AJAX_MASTER_DETAIL_TEMPLATE){
                $serviceFile = 'master_detail_service.php';
            }

            if($this->template == Generator::MASTER_DETAIL_DETAIL_TEMPLATE || $this->template == Generator::AJAX_MASTER_DETAIL_DETAIL_TEMPLATE){
                $serviceFile = 'master_detail_detail_service.php';
            }

            $files[] = new CodeFile(
                Yii::getAlias('@app/services/' . $this->getServiceClassName() . '.php'),
                $this->renderShared($serviceFile)
            );
        }

        return $files;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return 'This generator generates a controller and views that implement CRUD (Create, Read, Update, Delete) operations for the specified data model.
            Support for master detail input like Tabular detail 
                using <a href="https://github.com/ahmadfadlydziljalal/yii2-dynamicform"><code>dynamic form</code></a>
                and  <a href="https://github.com/johnitvn/yii2-ajaxcrud"><code>AJAX CRUD</code></a>. <br/>
                <strong>You need to use bootstrap 5 and bootstrap icon.</strong>
        ';
    }

    /**
     * @inheritdoc
     */
    public function rules() : array
    {
        return array_merge(parent::rules(), require(__DIR__ . '/_rules.php'));}

    /**
     * @inheritdoc
     */
    public function hints(): array
    {
        return array_merge(parent::hints(), [
            'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
                You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
                and class name should be in CamelCase with an uppercase-first letter. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/basic/controllers/views/post</code>, <code>@app/views/post</code>. If not set, it will default
                to <code>@app/views/ControllerID</code>',
            'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
                You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
            'searchModelClass' => 'This is the name of the search model class to be generated. You should provide a fully
                qualified namespaced class name, e.g., <code>app\models\PostSearch</code>.',
            'modelsClassDetail' => 'Form Master -> Detail.',
            'modelsClassDetailDetail' => 'Form Master -> Detail -> Detail',
            'labelID' => 'The property that will be used as the information for <code>Alert::widget()</code>; by default, it is <code>id</code>.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => 'Model Class',
            'controllerClass' => 'Controller Class',
            'viewPath' => 'View Path',
            'baseControllerClass' => 'Base Controller Class',
            'searchModelClass' => 'Search Model Class',
            'modelsClassDetail' => 'Model class for detail (1st) level',
            'modelsClassDetailDetail' => 'Model class for detail => detail (2nd) level',
            'labelID' => 'Model`s label',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates(): array
    {
        return ['controller.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes(): array
    {
        return array_merge(parent::stickyAttributes(), ['baseControllerClass']);
    }

    /**
     * Renders a shared template with the provided parameters.
     *
     * @param string $template The name of the shared template to render.
     * @param array $params Optional parameters to be passed to the template.
     * @return string The rendered content of the template.
     * @throws InvalidConfigException
     */
    protected function renderShared(string $template, array $params = []): string
    {
        $view = new yii\web\View();
        $params['generator'] = $this;

        $sharedPath = dirname($this->getTemplatePath(), 2) . '/_shared/' . ltrim($template, '/');
        return $view->renderFile($sharedPath, $params, $this);
    }

}