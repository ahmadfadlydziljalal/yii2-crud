<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\crud\generators\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}
$serviceClassName = $generator->getServiceClassName();


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

<?php if (!empty($generator->searchModelClass)): ?>use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>

<?php if (!empty($generator->modelsClassDetail)): ?>
<?php $modelsDetail = StringHelper::basename($generator->modelsClassDetail); ?>
use <?= ltrim($generator->modelsClassDetail, '\\') ?>;
<?php endif; ?>
<?php if (!empty($generator->modelsClassDetailDetail)): ?>
<?php $modelsDetailDetail = StringHelper::basename($generator->modelsClassDetailDetail); ?>
use <?= ltrim($generator->modelsClassDetailDetail, '\\') ?>;
<?php endif; ?>
use Throwable;
use yii\db\StaleObjectException;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Html;
use app\services\<?= $serviceClassName ?>;

/**
 * <?= $controllerClass ?> implements the CRUD actions for the <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{

    private <?= $serviceClassName ?> $service;

    public function __construct($id, $module, <?= $serviceClassName ?> $service, $config = [])
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
                // 'only' => ['create','update','view','delete',],
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
    public function actionIndex() : string
    {
       <?php if (!empty($generator->searchModelClass)): ?>
 $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }


    /**
     * Displays a single <?= $modelClass ?> model.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return array | Response
     * @throws HttpException
     */
    public function actionView(int <?= $actionParams ?>): array | Response {

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
            'footer'=> Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                    Html::a('Edit',['update','<?= substr($actionParams,1) ?>'=><?= $actionParams ?>],['class'=>'btn btn-primary','role'=>'modal-remote'])
        ];
    }


    /**
     * Creates a new <?= $modelClass ?> model (with detail(s)<?= $modelsDetail ? ' and sub-detail(s)' : '' ?>).
     * Works with AJAX modal (yii2-ajaxcrud).
     *
     * @return array|Response
     * @throws NotFoundHttpException
     */
    public function actionCreate(): array|Response
    {
        if (!$this->request->isAjax) {
            Yii::$app->session->setFlash('openCreateModal');
            return $this->redirect(['index']);
        }

        $model = new <?= $modelClass ?>();
        $modelsDetail = [new <?= $modelsDetail ?>()];
        $modelsDetailDetail = [[new <?= $modelsDetailDetail ?>()]];

        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $model->load($this->request->post());

            // delegate ke service
            [$success, $modelsDetail, $modelsDetailDetail] = $this->service->create($model, $this->request->post());

            if ($success) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => '<span class="text-success">Create New <?= $modelClass ?> is Success</span>',
                    'content' => $this->renderAjax('view', [
                        'model' => $this->findModel($model->id),
                    ]),
                    'footer' =>Html::button('Close', ['class' => 'btn btn-secondary me-auto','data-bs-dismiss' => "modal"]) .
                        Html::a('Create More', ['create'], ['class' => 'btn btn-primary','role' => 'modal-remote']),
                ];
            }

            // gagal validasi → re-render form
            return [
                'title' => '<span class="text-danger">Create New <?= $modelClass ?> is Failed</span>',
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                    'modelsDetail' => $modelsDetail,
                    'modelsDetailDetail' => $modelsDetailDetail,
                ]),
                'footer' =>Html::button('Close', ['class' => 'btn btn-secondary me-auto','data-bs-dismiss' => "modal"]) .
                    Html::button('Save', ['class' => 'btn btn-primary','type' => "submit"]),
            ];
        }

        // default form
        return [
            'title' => "Create New <?= $modelClass ?>",
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'modelsDetail' => $modelsDetail,
                'modelsDetailDetail' => $modelsDetailDetail,
            ]),
            'footer' =>Html::button('Close', ['class' => 'btn btn-secondary me-auto','data-bs-dismiss' => "modal"]) .
                Html::button('Save', ['class' => 'btn btn-primary','type' => "submit"]),
        ];
    }


    /**
     * Updates an existing <?= $modelClass ?> model (with detail(s)<?= $modelsDetail ? ' and sub-detail(s)' : '' ?>).
     * Only for ajax request will return a JSON object.
     * @param int <?= $actionParams . "\n" ?>
     * @return array|Response
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    public function actionUpdate(int <?= $actionParams ?>): array|Response
    {
        if (!$this->request->isAjax) {
            Yii::$app->session->setFlash('openUpdateModal', <?= $actionParams ?>);
            return $this->redirect(['index']);
        }

        $model = $this->findModel(<?= $actionParams ?>);

        // siapkan detail + sub-detail lama
        $modelsDetail = $model-><?= $details = lcfirst(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetail)))) ?> ?: [new <?= $modelsDetail ?>()];
        $modelsDetailDetail = array_map(function ($detail) {
            return $detail-><?= $detailDetails = lcfirst(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetailDetail)))) ?> ?: [new <?= $modelsDetailDetail ?>()];
        }, $modelsDetail);

        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isGet) {
            return [
                'title' => 'Update <?= $modelClass ?> #' . <?= $actionParams ?>,
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                    'modelsDetail' => $modelsDetail,
                    'modelsDetailDetail' => $modelsDetailDetail,
                ]),
                'footer' => Html::button('Close', ['class' => 'btn btn-secondary me-auto','data-bs-dismiss' => 'modal']) .
                    Html::button('Save', ['class' => 'btn btn-primary','type' => 'submit']),
            ];
        }

        if ($model->load($this->request->post())) {
            // delegate ke service
            [$success, $modelsDetail, $modelsDetailDetail] = $this->service->update($model, Yii::$app->request->post(), $modelsDetail, $modelsDetailDetail);

            if ($success) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => '<span class="text-success">Update' . <?= $actionParams ?> . ' is Success</span>',
                    'content' => $this->renderAjax('view', [
                        'model' => $this->findModel($model->id),
                    ]),
                    'footer' => Html::button('Close', ['class' => 'btn btn-secondary me-auto','data-bs-dismiss' => 'modal']) .
                        Html::a('Edit Again', ['update', '<?= substr($actionParams,1) ?>' => <?= $actionParams ?>], ['class' => 'btn btn-primary','role' => 'modal-remote']),
                ];
            }

            // gagal validasi → re-render form
            return [
                'title' => '<span class="text-danger">Update <?= $modelClass ?> is Failed</span>',
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                    'modelsDetail' => $modelsDetail,
                    'modelsDetailDetail' => $modelsDetailDetail,
                ]),
                'footer' => Html::button('Close', ['class' => 'btn btn-secondary me-auto','data-bs-dismiss' => "modal"]) .
                    Html::button('Save', ['class' => 'btn btn-primary','type' => "submit"]),
            ];
        }

        // fallback
        return [
            'title' => '<span class="text-danger">Update <?= $modelClass ?> is Failed</span>',
            'content' => $this->renderAjax('update', [
                'model' => $model,
                'modelsDetail' => $modelsDetail,
                'modelsDetailDetail' => $modelsDetailDetail,
            ]),
            'footer' => Html::button('Close', ['class' => 'btn btn-secondary me-auto','data-bs-dismiss' => 'modal']) .
                Html::button('Save', ['class' => 'btn btn-primary','type' => 'submit']),
        ];
    }

    /**
     * Delete an existing <?= $modelClass ?> model.
     * Only for ajax request will return a JSON object
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return array
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete(int <?= $actionParams ?>): array
    {
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
        $request = Yii::$app->request;

        // Array or selected records primary keys
        $pks = explode(',', $request->post( 'pks' ));

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
        ?>if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}