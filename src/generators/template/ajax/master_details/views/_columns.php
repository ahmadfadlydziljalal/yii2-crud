<?php

use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\crud\generators\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$actionParams = $generator->generateActionParams();

echo "<?php\n";

?>
/* @var $this yii\web\View */
return [
    /*[
    'class' => 'kartik\grid\CheckboxColumn',
    'width' => '20px',
    ],*/
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
        'mergeHeader' => false
    ],
<?php
$count = 0;
foreach ($generator->getColumnNames() as $name) {
   if ($name == 'id' || $name == 'created_at' || $name == 'updated_at' || $name == 'created_by' || $name == 'updated_by') {
      echo "    // [\n";
      echo "        // 'class'=>'\kartik\grid\DataColumn',\n";
      echo "        // 'attribute'=>'" . $name . "',\n";
      echo "    // ],\n";
   } else if (++$count < 6) {
      echo "    [\n";
      echo "        'class'=>'\kartik\grid\DataColumn',\n";
      echo "        'attribute'=>'" . $name . "',\n";
      echo "        'headerOptions'=>['class' => 'text-nowrap'],\n";
      echo "        'contentOptions'=>['class' => 'text-nowrap'],\n";
      echo "    ],\n";
   } else {
      echo "    // [\n";
      echo "        // 'class'=>'\kartik\grid\DataColumn',\n";
      echo "        // 'attribute'=>'" . $name . "',\n";
      echo "        // 'headerOptions'=>['class' => 'text-nowrap'],\n";
      echo "        // 'contentOptions'=>['class' => 'text-nowrap'],\n";
      echo "    // ],\n";
   }
}
?>
    [
        'class' => 'kartik\grid\ActionColumn',
        'headerOptions' => [
            'style' => 'width: 2px;'
        ],
        'contentOptions' => [
            'class' => 'text-nowrap',
        ],
        'header' => '',
        'viewOptions' => [
            'label' => '<i class="bi bi-eye"></i>',
            'role' => 'modal-remote',
            'title' => 'View',
            'data-toggle' => 'tooltip'
        ],
        'updateOptions' => [
            'label' => '<i class="bi bi-pencil"></i>',
            'role' => 'modal-remote',
            'title' => 'Update',
            'data-toggle' => 'tooltip'
        ],
        'deleteOptions' => [
            'label' => '<i class="bi bi-trash text-danger"></i>',
            'class' => 'text-danger',
            'role' => 'modal-remote',
            'title' => 'Delete',
            'data-confirm' => false,
            'data-method' => false,// for override yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Are you sure?',
            'data-confirm-message' => 'Are you sure want to delete this item'
        ],
    ],
];   