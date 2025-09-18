<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\crud\generators\Generator */

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
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\StaleObjectException;
use yii\web\Response;
use yii\db\Exception;
use yii\helpers\Html;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $generator->getIndefiniteArticle($modelClass) ?>
 * <?= $modelClass ?> model.
 */

class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
    * {@inheritdoc}
    */
    public function behaviors() : array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                    'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
    * Lists all <?= $modelClass ?> models.
    * @return string
    */
    public function actionIndex() : string {
    <?php if (!empty($generator->searchModelClass)): ?>
    $searchModel = new <?= $searchModelAlias ?? $searchModelClass ?>();
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
    * @return string
    * @throws NotFoundHttpException
    */
    public function actionView(<?= $actionParams !== '$id' ? $actionParams : 'int ' . $actionParams  ?>) : string
    {
        return $this->render('view', [
            'model' => $this->findModel(<?= $actionParams ?>)
        ]);
    }

    /**
    * Creates a new <?= $modelClass ?> model.
    * If creation is successful, the browser will be redirected to the 'index' page.
    * @return Response|string
    * @throws Exception
    */
    public function actionCreate() : Response|string{
        $model = new <?= $modelClass ?>();

        if ($this->request->isPost) {
            if($model->load($this->request->post()) && $model->save()){
                Yii::$app->session->setFlash('success','<?= $modelClass ?>: ' . Html::a(<?= '$model->' . $labelID ?>,  ['view', <?= $urlParams ?>]) . ' berhasil dibuat.');
                return $this->redirect(['index']);
            } else {
                $model->loadDefaultValues();
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
    * Updates an existing <?= $modelClass ?> model.
    * If the update is successful, the browser will be redirected to the 'index' page with pagination URL
    * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
    * @return Response|string
    * @throws Exception
    * @throws NotFoundHttpException if the model cannot be found
    */
    public function actionUpdate(<?= $actionParams !== '$id' ? $actionParams : 'int ' . $actionParams  ?>) : Response|string {
        $model = $this->findModel(<?= $actionParams ?>);

        if($this->request->isPost && $model->load($this->request->post()) && $model->save()){
            Yii::$app->session->setFlash('info','<?= $modelClass ?>: ' . Html::a(<?= '$model->' . $labelID ?>,  ['view', <?= $urlParams ?>]) . ' berhasil di-update.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
    * Deletes an existing <?= $modelClass ?> model.
    * If deletion is successful, the browser will be redirected to the 'index' page.
    * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
    * @return Response
    * @throws NotFoundHttpException if the model cannot be found
    * @throws StaleObjectException
    * @throws Throwable
    */
    public function actionDelete(<?= $actionParams !== '$id' ? $actionParams : 'int ' . $actionParams  ?>) : Response {
        $model = $this->findModel(<?= $actionParams ?>);
        $model->delete();

        Yii::$app->session->setFlash('danger',  '<?= $modelClass ?>: ' . <?=  '$model->' . $labelID ?>.  ' berhasil dihapus.');
        return $this->redirect(['index']);
    }

    /**
    * Finds the <?= $modelClass ?> model based on its primary key value.
    * If the model is not found, a 404 HTTP exception will be thrown.
    * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
    * @return <?= $modelClass ?> the loaded model
    * @throws NotFoundHttpException if the model cannot be found
    */
    protected function findModel(<?= $actionParams !== '$id' ? $actionParams : 'int ' . $actionParams  ?>) : <?= $modelClass ?> {
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
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}