<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
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
$labelID =
    !isset($generator->labelID) ?
        $generator->getNameAttribute() :
        (empty($generator->labelID) ? $generator->getNameAttribute() : $generator->labelID);

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
use Throwable;
use yii\db\StaleObjectException;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Html;

/**
* <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
*/
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
    * @inheritdoc
    */
    public function behaviors(): array
    {
        return [
            //[
                // 'class' => 'yii\filters\AjaxFilter',
                // 'except' => ['index'],
                // 'only' => ['create','update','view','delete',],
            //],
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
    * @return Response|string
    */
    public function actionIndex(): Response|string { <?php if (!empty($generator->searchModelClass)): echo "\n" ?>
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
    * @return array | Response | string
    * @throws HttpException
    */
    public function actionView(int <?= $actionParams ?>) : array | Response | string
    {
        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "<?= $modelClass ?> #".<?= $actionParams ?>,
                'content' => $this->renderAjax('view', [
                    'model' => $this->findModel(<?= $actionParams ?>),
                ]),
                'footer' => Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                            Html::a('Edit',['update','<?= substr($actionParams, 1) ?>'=><?= $actionParams ?>],['class'=>'btn btn-primary','role'=>'modal-remote'])
            ];
        }

        return $this->render('view', [
            'model' => $this->findModel(<?= $actionParams ?>),
        ]);
    }

    /**
    * Creates a new <?= $modelClass ?> model.
    * For ajax request will return a JSON object
    * @return array | Response | string
    * @throws NotFoundHttpException
    * @throws Exception
    */
    public function actionCreate() : array | Response | string
    {
        $request = Yii::$app->request;
        $model = new <?= $modelClass ?>();

        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->isGet){
                return [
                    'title' => "Create New <?= $modelClass ?>",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]) .
                                Html::button('Save',['class'=>'btn btn-primary ','type'=>"submit"])
                ];
            } else if ($model->load($request->post()) && $model->save()){
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => '<span class="text-success">Create New <?= $modelClass ?> is Success</span>',
                    'content' => $this->renderAjax('view', [
                        'model' => $this->findModel($model->id),
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                               Html::a('Create More',['create'],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];
            } else {
                return [
                    'title' => '<span class="text-danger">Create New <?= $modelClass ?> is Failed</span>',
                    'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            'footer' => Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                       Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])];
            }
        }

        if ($model->load($request->post()) && $model->save()){
            Yii::$app->session->setFlash('success',  '<?= $modelClass ?>: '.   <?=  '$model->' . $labelID ?> . ' berhasil ditambahkan.');
            return $this->redirect(['view', <?= $urlParams ?>]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);

    }

    /**
    * Updates an existing <?= $modelClass ?> model.
    * For ajax request will return a JSON object
    * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
    * @return array | Response | string
    * @throws Exception
    * @throws NotFoundHttpException
    */
    public function actionUpdate(int <?= $actionParams ?>) : array | Response | string{
        $request = Yii::$app->request;
        $model = $this->findModel(<?= $actionParams ?>);

        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->isGet){
                return [
                    'title' => "Update <?= $modelClass ?> #".<?= $actionParams ?>,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                               Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
                ];
            } else if ($model->load($request->post()) && $model->save()){
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => '<span class="text-success">Update <?= $modelClass ?> is Success</span>' . ' # '.<?= $actionParams ?>,
                    'content' => $this->renderAjax('view', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                               Html::a('Edit',['update','<?= substr($actionParams, 1) ?>'=><?= $actionParams ?>],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];
            } else {
                return [
                    'title' => '<span class="text-danger">Update <?= $modelClass ?> is Failed</span>' . ' # '.<?= $actionParams ?>,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Close',['class'=>'btn btn-secondary me-auto','data-bs-dismiss'=>"modal"]).
                               Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
                ];
            }
        }

        if ($model->load($request->post()) && $model->save()){
            Yii::$app->session->setFlash('info',  '<?= $modelClass ?>: ' . <?=  '$model->' . $labelID ?>.  ' berhasil dirubah.');
            return $this->redirect(['view', <?= $urlParams ?>]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);

    }

    /**
    * Delete an existing <?= $modelClass ?> model.
    * For ajax request will return a JSON object
    * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
    * @return array | Response
    * @throws HttpException
    * @throws NotFoundHttpException
    * @throws Throwable
    * @throws StaleObjectException
    */
    public function actionDelete(int <?= $actionParams ?>) : array | Response
    {
        $this->findModel(<?= $actionParams ?>)->delete();
        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'forceClose'=>true,
                'forceReload'=>'#crud-datatable-pjax'
            ];
        }
        return $this->redirect(['index']);
    }

    /**
    * Delete multiple existing <?= $modelClass ?> model.
    * For ajax request will return a JSON object
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
    * @return <?= $modelClass ?> the loaded model
    * @throws NotFoundHttpException if the model cannot be found
    */
    protected function findModel(int <?= $actionParams ?>) : <?= $modelClass ?>{
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
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}