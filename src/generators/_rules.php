<?php

use yii\db\BaseActiveRecord;
use yii\web\Controller;

return [
    [['controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass'], 'filter', 'filter' => 'trim'],
    [['modelClass', 'controllerClass', 'baseControllerClass'], 'required'],
    [['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
    [['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
    [['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::class]],
    [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::class]],
    [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
    [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
    [['controllerClass', 'searchModelClass'], 'validateNewClass'],

    /** @see \dzil\crud\generators\Generator::validateModelClass()*/

    [['modelClass'], 'validateModelClass'],
    [['enableI18N'], 'boolean'],
    [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],

    [['modelsClassDetail'], 'required', 'when' => function ($model) {
        return $model->template === 'master-details';
    }, 'whenClient' => "function (attribute, value) {
                return $('#generator-template').val() === 'master-details';
            }", 'message' => 'Harus di-isi jika template master-details'],

    [['modelsClassDetailDetail'], 'required', 'when' => function ($model) {
        return $model->template === 'master-details-details';
    }, 'whenClient' => "function (attribute, value) {
                return $('#generator-template').val() === 'master-details-details';
            }", 'message' => 'Harus di-isi jika template master-details-details'],

    [['viewPath', 'labelID'], 'safe'],
];