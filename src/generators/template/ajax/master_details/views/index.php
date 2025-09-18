<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\yii2_crud\generators\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$controllerName = $generator->getControllerID();
$plusIcon = '<i class="bi bi-plus-circle-dotted"></i>';
echo "<?php\n";

?>
    use yii\helpers\Url;
    use yii\helpers\Html;
    use yii\bootstrap5\Modal;
    use kartik\grid\GridView;
    use dzil\yii2_crud\CrudAsset;
    use dzil\yii2_crud\BulkButtonWidget;


    /* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
    /* @var $dataProvider yii\data\ActiveDataProvider */
    /* @see <?= $generator->controllerClass ?>::actionIndex() */

    $this->title = <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>;
    $this->params['breadcrumbs'][] = $this->title;

    CrudAsset::register($this);

    ?>
    <div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
        <div id="ajaxCrudDatatable" class="d-flex flex-column gap-2">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-4 gap-md-2">
                <h1 class="my-0"><?= "<?= " ?>Html::encode($this->title) ?></h1>
                <div class="ms-md-auto ms-lg-auto">
                    <?= "<?= " ?>Html::a('<?= $plusIcon ?>'.<?= $generator->generateString(' Tambah') ?>, ['create'], ['class' => 'btn btn-success', 'id' => 'btnCreate' ,'role' => 'modal-remote']) ?>
                </div>
            </div>

           <?= "<?php try { 
            echo " ?>GridView::widget([
                        'id'=>'crud-datatable',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pjax' => true,
                        'columns' => require(__DIR__ . '/_columns.php'),
                        'panel' =>  false,
                        'bordered' => true,
                        'striped' => true,
                      ]);
                }catch(Exception $e){
                    echo $e->getMessage();
                }<?= "?>\n" ?>
        </div>
    </div>
<?= '<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "size" => "modal-xl modal-fullscreen-xl-down",
    "footer"=>"",// always need it for jquery plugin
    "options" => [
        "tabindex" => false // important for Select2 to work properly
    ],
    /*"dialogOptions" => [
        "class" => "modal-dialog-scrollable"
    ],*/
    "clientOptions" => [
        "backdrop" => "static",
        "keyboard" => false
    ] 
])?>' . "\n" ?>
<?= '<?php Modal::end(); ?>' ?>

<?= '<?php if(Yii::$app->session->getFlash(\'openCreateModal\')){
    $this->registerJs(<<<JS
        jQuery(\'#btnCreate\').click();
    JS);
}?>' ?>

<?= '<?php if ($id = Yii::$app->session->getFlash(\'openViewModal\')) { ?>' . "\n" ?>
    <?= '<?php $this->registerJs(<<<JS' . "\n" ?>
        jQuery('a[href="/<?=  $controllerName  ?>/view?id=<?= '$id' ?>"]').click();
    <?= 'JS); ?>' . "\n" ?>
<?= '<?php } ?>' . "\n" ?>

<?= '<?php if ($id = Yii::$app->session->getFlash(\'openUpdateModal\')) { ?>' . "\n" ?>
<?= '<?php $this->registerJs(<<<JS' . "\n" ?>
    jQuery('a[href="/<?=  $controllerName  ?>/update?id=<?= '$id' ?>"]').click();
<?= 'JS); ?>' . "\n" ?>
<?= '<?php } ?>' . "\n" ?>

<?= '<?php $js =<<<JS
    jQuery(".alert").animate({opacity: 1.0}, 3000).fadeOut("slow");
JS;
$this->registerJs($js) ?>' ?>