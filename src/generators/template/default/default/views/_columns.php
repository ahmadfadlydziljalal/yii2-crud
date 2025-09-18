<?php

use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\yii2_crud\generators\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$actionParams = $generator->generateActionParams();

echo "<?php\n";

?>
return [
    [
        'class' => 'yii\grid\SerialColumn',
    ],
    <?php
    $count = 0;
    foreach ($generator->getTableSchema()->columns as $column) {

        $format = $generator->generateColumnFormat($column);

        if ($column->name=='id'||$column->name=='created_at'||$column->name=='updated_at' ||$column->name=='created_by'||$column->name=='updated_by'){
            echo "    // [\n";
            echo "        // 'class'=>'\yii\grid\DataColumn',\n";
            echo "        // 'attribute'=>'" . $column->name . "',\n";
            echo "        // 'format'=>'" . $format . "',\n";
            echo "    // ],\n";
        } else if (++$count < 6) {
            echo "    [\n";
            echo "        'class'=>'\yii\grid\DataColumn',\n";
            echo "        'attribute'=>'" . $column->name . "',\n";
            echo "        'format'=>'" . $format . "',\n";
            echo "    ],\n";
        } else {
            echo "    // [\n";
            echo "        // 'class'=>'\yii\grid\DataColumn',\n";
            echo "        // 'attribute'=>'" . $column->name . "',\n";
            echo "        // 'format'=>'" . $format . "',\n";
            echo "    // ],\n";
        }
    }
    ?>
    [
        'class' => 'yii\grid\ActionColumn',
    ],
];   