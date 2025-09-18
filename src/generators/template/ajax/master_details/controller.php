<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\yii2_crud\generators\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}


/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
<?php if (!empty($generator->modelsClassDetail)): ?>
<?php $modelsDetail = StringHelper::basename($generator->modelsClassDetail); ?>
use <?= ltrim($generator->modelsClassDetail, '\\') ?>;
<?php endif; ?>

use Throwable;
use yii\db\StaleObjectException;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Html;
use app\services\<?= $generator->getServiceClassName() ?>;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $generator->getIndefiniteArticle($modelClass) ?> <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{

    private <?= $generator->getServiceClassName() ?> $service;

    public function __construct($id, $module, <?= $generator->getServiceClassName() ?> $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            // [
                // 'class' => 'yii\filters\AjaxFilter',
                // 'except' => ['index'],
                // 'only' => [ 'create' , 'update' , 'view' ,'delete',],
                // 'errorMessage' => "Metode tidak boleh diakses langsung, \n Timeout saat memanggil metode, \n Koneksi Internet Terputus / Lambat"
            // ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'bulkdelete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all <?= $modelClass ?> models.
     * @return string
     */
    public function actionIndex() : string{
   <?php if (!empty($generator->searchModelClass)): ?>
    $searchModel = new <?= $searchModelAlias ?? $searchModelClass ?>();
       $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

       return $this->render('index', [
           'searchModel' => $searchModel,
           'dataProvider' => $dataProvider,
       ]);
   }
   <?php else: ?>
    $dataProvider = new ActiveDataProvider([
        'query' => <?= $modelClass ?>::find(),
    ]);

    return $this->render('index', [
        'dataProvider' => $dataProvider,
    ]);
   }
   <?php endif; ?>

    /**
     * Displays a single <?= $modelClass ?> model.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return array | Response
     * @throws HttpException
     */
    public function actionView(int <?= $actionParams ?>) : array | Response {

        if(!$this->request->isAjax){
            Yii::$app->session->setFlash('openViewModal', <?= $actionParams ?> );
            return $this->redirect(['index']);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title'=> "<?= $modelClass ?> #".<?= $actionParams ?>,
            'content'=>$this->renderAjax('view', [
                'model' => $this->findModel(<?= $actionParams ?>),
            ]),
            'footer'=>
                Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                Html::a('Edit',['update','<?= substr($actionParams,1) ?>'=><?= $actionParams ?>],['class'=>'btn btn-primary','role'=>'modal-remote'])
        ];
    }

    /**
     * Creates a new <?= $modelClass ?> model.
     * Only for ajax request will return a JSON object
     * @return array | Response
     * @throws HttpException
     */
    public function actionCreate() : array | Response {

        if(!$this->request->isAjax){
            Yii::$app->session->setFlash('openCreateModal');
            return $this->redirect(['index']);
        }

        $model = new <?= $modelClass ?>();
        $modelsDetail = [ new <?= $modelsDetail ?>() ];

        Yii::$app->response->format = Response::FORMAT_JSON;
        if($model->load($this->request->post())){
            [$success, $modelsDetail] = $this->service->create($model, $this->request->post());
            if ($success) {
               return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> '<span class="text-success">Create New <?= $modelClass ?> is Success</span>',
                    'content'=> $this->renderAjax('view', [
                        'model' => $this->findModel($model->id),
                    ]),
                    'footer' => Html::button('Close',[ 'class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                        Html::a('Create More',['create'],[ 'class'=>'btn btn-primary','role'=>'modal-remote'])
                ];
            }

            return [
                'title' => '<span class="text-danger-emphasis">Create New <?= $modelClass ?> is Failed</span>',
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                    'modelsDetail' =>  $modelsDetail,
                ]),
                'footer'=> Html::button('Close',[ 'class'=>'btn btn-secondary me-auto', 'data-bs-dismiss'=>"modal"]) .
                    Html::button('Save', [ 'class'=>'btn btn-primary ', 'type'=>"submit"])
            ];
        }

        return [
            'title'=> "Create New <?= $modelClass ?>",
            'content'=> $this->renderAjax('create', [
                'model' => $model,
                'modelsDetail' => empty($modelsDetail) ? [ new <?= $modelsDetail ?>() ] : $modelsDetail,
            ]),
            'footer'=>
                Html::button('Close',[ 'class'=>'btn btn-secondary me-auto', 'data-bs-dismiss'=>"modal"]) .
                Html::button('Save', [ 'class'=>'btn btn-primary ', 'type'=>"submit"])
        ];
    }

    /**
     * Updates an existing <?= $modelClass ?> model.
     * Only for ajax request will return a JSON object,
     * and for non-ajax request, if the update is successful, the browser will be redirected to the 'index' page with open edit modal.
     *
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return array | Response
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int <?= $actionParams ?>) : array | Response {

        if(!$this->request->isAjax){
            Yii::$app->session->setFlash('openUpdateModal', <?= $actionParams ?> );
            return $this->redirect(['index']);
        }

        $model = $this->findModel(<?= $actionParams ?>);
        $modelsDetail = !empty($model-><?= $details = lcfirst(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetail)))) ?>) ? $model-><?= $details ?> : [new <?= $modelsDetail ?>()];

        Yii::$app->response->format = Response::FORMAT_JSON;
        if($model->load($this->request->post())){
            [$success, $modelsDetail] = $this->service->update($model, $this->request->post());
            if ($success) {
                return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> '<span class="text-success">Update <?= $modelClass ?> is Success</span>'  . ' # '.<?= $actionParams ?> ,
                    'content'=> $this->renderAjax('view', [
                        'model' => $this->findModel(<?= $actionParams ?>),
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                        Html::a('Edit',['update','<?= substr($actionParams,1) ?>'=><?= $actionParams ?>],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];
            }

            return [
                'title'=> '<span class="text-danger-emphasis">Update <?= $modelClass ?> #' . <?= $actionParams ?> . ' is Failed</span>',
                'content'=> $this->renderAjax('create', [
                    'model' => $model,
                    'modelsDetail' =>  $modelsDetail,
                ]),
                'footer'=> Html::button('Close',[ 'class'=>'btn btn-secondary me-auto', 'data-bs-dismiss'=>"modal"]) .
                    Html::button('Save', [ 'class'=>'btn btn-primary ', 'type'=>"submit"])
            ];
        }

        return [
            'title'=> "Update <?= $modelClass ?> #".<?= $actionParams ?> ,
            'content'=> $this->renderAjax('update', [
                'model' => $model,
                'modelsDetail' => $modelsDetail
            ]),
            'footer'=> Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
        ];
    }

    /**
     * Delete an existing <?= $modelClass ?> model.
     * Only For ajax request will return a JSON object
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return array
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete(int <?= $actionParams ?>) : array{
        $this->findModel(<?= $actionParams ?>)->delete();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'forceClose'=>true,
            'forceReload'=>'#crud-datatable-pjax'
        ];
    }

     /**
     * Delete a multiple existing <?= $modelClass ?> model.
     * Only for ajax request will return a JSON object
     * @return array
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionBulkdelete() : array
    {
        // Array or selected records primary keys
        $pks = explode(',', $this->request->post( 'pks' ));

        foreach ( $pks as $pk ) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'forceClose'=>true,
            'forceReload'=>'#crud-datatable-pjax'
        ];
    }

    /**
     * Finds the <?= $modelClass ?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return <?=                   $modelClass ?> the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int <?= $actionParams ?>): <?= $modelClass ?>{
        <?php
            if (count($pks) === 1) {
                $condition = '$id';
            } else {
                $condition = [];
                foreach ($pks as $pk) {
                    $condition[] = "'$pk' => \$$pk";
                }
                $condition = '[' . implode(', ', $condition) . ']';
            }
            echo "\n";
        ?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
