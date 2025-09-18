<?php

namespace dzil\crud;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;


/**
 * Class Tabular
 * 
 * This class is part of the Yii2 Gii CRUD extension and is designed to handle
 * tabular data operations. It provides functionality for managing and processing
 * multiple rows of data in a structured format.
 * 
 * @package yii2-dzil-crud
 */
class Tabular extends Model
{

    /**
     * Creates and populates a set of models.
     *
     * @param string $modelClass
     * @param array $multipleModels
     * @return array
     */
    public static function createMultiple(string $modelClass, array $multipleModels = []): array
    {
        $model = new $modelClass;
        $formName = $model->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if (!empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, 'id', 'id'));
            if(!empty($keys)){
                $multipleModels = array_combine($keys, $multipleModels);
            }
        }

        if ($post && is_array($post)) {
            foreach ($post as $item) {
                if (!empty($item['id']) && isset($multipleModels[$item['id']])) {
                    $models[] = $multipleModels[$item['id']];
                } else {
                    $models[] = new $modelClass;
                }
            }
        }

        unset($model, $formName, $post);
        return $models;
    }

}