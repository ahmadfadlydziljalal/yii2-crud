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
$serviceClassName = $generator->getServiceClassName();

if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();
$labelID = !isset($generator->labelID)
    ? $generator->getNameAttribute()
    : (empty($generator->labelID) ? $generator->getNameAttribute() : $generator->labelID);

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
<?php if (!empty($generator->modelsClassDetailDetail)): ?>
<?php $modelsDetailDetail = StringHelper::basename($generator->modelsClassDetailDetail); ?>
use <?= ltrim($generator->modelsClassDetailDetail, '\\') ?>;
<?php endif; ?>

use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use app\services\<?= $serviceClassName ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Response;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $generator->getIndefiniteArticle($modelClass) ?>
 * <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    private <?= $serviceClassName ?> $service;

    public function __construct($id, $module, <?= $serviceClassName ?> $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
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
     * Renders the index view for the resource.
     *
     * @return string The rendered content of the index view.
     */
    public function actionIndex(): string
    {
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
     * Renders the view page for a model identified by the given ID.
     *
     * @param int $id The ID of the model to be viewed.
     * @return string The rendered view page.
     * @throws NotFoundHttpException If the model cannot be found.
     */
    public function actionView(<?= $actionParams === '$id' ? "int " . $actionParams : $actionParams ?>): string
    {
        return $this->render('view', [
            'model' => $this->findModel(<?= $actionParams ?>),
        ]);
    }

    /**
     * Creates a new model and associated details.
     *
     * @return Response|string A redirect response to the index page if creation is successful,
     * otherwise the rendered 'create' view is returned.
     */
    public function actionCreate(): Response|string
    {
        $model = new <?= $modelClass ?>();
        $modelsDetail = [ new <?= $modelsDetail ?>() ];
        $modelsDetailDetail =[[new <?= $modelsDetailDetail ?>()]];

        if ($model->load(Yii::$app->request->post())) {
            [$success, $modelsDetail, $modelsDetailDetail] = $this->service->create($model, Yii::$app->request->post());
            if($success){
                Yii::$app->session->setFlash('success', '<?= $modelClass ?>: ' .Html::a(<?= '$model->' . $labelID ?>, ['view', <?= $urlParams ?>]) ." berhasil ditambahkan.");
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('danger', '<?= $modelClass ?> gagal ditambahkan.');
        }

        return $this->render('create', [
            'model' => $model,
            'modelsDetail' => $modelsDetail,
            'modelsDetailDetail' => $modelsDetailDetail,
        ]);
    }

    /**
     * Updates an existing <?= $modelClass ?> model.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return Response|string
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws Exception
     */
    public function actionUpdate(<?= $actionParams === '$id' ? "int " . $actionParams : $actionParams ?>): Response|string
    {
        $model = $this->findModel(<?= $actionParams ?>);

        // Ambil detail lama (jika ada)
        $modelsDetail = !empty($model-><?= $details = lcfirst(Inflector::camelize(Inflector::pluralize(StringHelper::basename($modelsDetail)))) ?>)? $model-><?= $details ?>: [new <?= $modelsDetail ?>()];

        // Ambil sub-detail lama (jika ada)
        $modelsDetailDetail = [];
        if (!empty($modelsDetail)) {
            foreach ($modelsDetail as $i => $detail) {
                $modelsDetailDetail[$i] = $detail-><?= lcfirst(
                    Inflector::camelize(
                        Inflector::pluralize(StringHelper::basename($modelsDetailDetail))
                    )
                ) ?> ?? [];
            }
        }

        // Proses update via service
        if ($model->load(Yii::$app->request->post())) {
            [$success, $modelsDetail, $modelsDetailDetail] = $this->service->update($model, Yii::$app->request->post(), $modelsDetail, $modelsDetailDetail);
            if($success){
                Yii::$app->session->setFlash('info',"<?= $modelClass ?>: " .Html::a(<?= '$model->' . $labelID ?>, ['view', <?= $urlParams ?>]) ." berhasil di-update.");
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('danger', '<?= $modelClass ?> gagal di-update.');
        }

        return $this->render('update', [
            'model' => $model,
            'modelsDetail' => $modelsDetail,
            'modelsDetailDetail' => $modelsDetailDetail,
        ]);
    }

    /**
    * Deletes a model identified by the given ID.
    *
    * @param int $id The ID of the model to be deleted.
    * @return Response A redirect response to the index page after the model is deleted.
    * @throws NotFoundHttpException
    * @throws StaleObjectException
    * @throws Throwable
    */
    public function actionDelete(<?= $actionParams !== '$id' ? $actionParams : 'int ' . $actionParams ?>): Response
    {
        $model = $this->findModel(<?= $actionParams ?>);
        $model->delete();
        Yii::$app->session->setFlash('danger', '<?= $modelClass ?>: ' .<?= '$model->' . $labelID ?> . ' berhasil dihapus.');
        return $this->redirect(['index']);
    }

    /**
     * Finds a model by its ID.
     *
     * @param int $id The ID of the model to be found.
     * @return Item The model corresponding to the specified ID.
     * @throws NotFoundHttpException If the model cannot be found.
     */
    protected function findModel(<?= $actionParams !== '$id' ? $actionParams : 'int ' . $actionParams ?>): <?= $modelClass . "\n" ?>
    {
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
?>        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
