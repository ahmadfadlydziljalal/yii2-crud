<?php

/**
 * This is the template for generating a Service class file.
 */

/** @var $this yii\web\View */
/** @var $generator \dzil\crud\generators\Generator */

use yii\helpers\StringHelper;

$modelClass     = StringHelper::basename($generator->modelClass);
$modelsDetail   = StringHelper::basename($generator->modelsClassDetail);
$serviceClass   = $modelClass . 'Service';

echo "<?php\n";

?>

namespace app\services;

use Yii;
use Exception;
use <?= ltrim($generator->modelClass, '\\') ?>;
use <?= ltrim($generator->modelsClassDetail, '\\') ?>;
use dzil\crud\Tabular;
use yii\helpers\ArrayHelper;

/**
* <?= $modelClass ?>Service class provides methods to handle the creation and updating of an <?= $modelClass ?> model,
* along with its associated <?= $modelsDetail ?> models transactionally.
* Validation and database operations are managed to ensure consistency and integrity.
*/
class <?= $serviceClass . "\n" ?>
{

    /**
     * Creates a main <?= $modelClass ?> model and its related <?= $modelsDetail ?> models using tabular data.
     *
     * @param <?= $modelClass ?> $model The primary model instance being created.
     * @param array $postData The tabular data for the main model and its related details.
     * @return array Returns an array where the first element is a boolean indicating success or failure,
     *               and the second element contains the array of <?= $modelsDetail ?> models.
     */
    public function create(<?= $modelClass ?> $model, array $postData): array
    {
        $modelsDetail = Tabular::createMultiple(<?= $modelsDetail ?>::class);
        Tabular::loadMultiple($modelsDetail, $postData);

        $isValid = $model->validate();
        $isValid = Tabular::validateMultiple($modelsDetail) && $isValid;

        if ($isValid) {
            $transaction = <?= $modelClass ?>::getDb()->beginTransaction();
            try {
                if ($flag = $model->save(false)) {
                    foreach ($modelsDetail as $detail) {
                        $detail-><?= strtolower($modelClass) ?>_id = $model->id;
                        if (!($flag = $detail->save(false))) {
                            break;
                        }
                    }
                }

                if ($flag) {
                    $transaction->commit();
                    return [true, $modelsDetail];
                }
                $transaction->rollBack();
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $transaction->rollBack();
            }
        }

        return [false, $modelsDetail];
    }

    /**
     * Updates an existing <?= $modelClass ?> model and its associated <?= $modelsDetail ?> models using tabular data.
     *
     * @param <?= $modelClass ?> $model The primary model instance being updated.
     * @param array $postData The tabular data for the main model and its related details.
     * @return array Returns an array where the first element is a boolean indicating success or failure,
     *               and the second element contains the updated array of <?= $modelsDetail ?> models.
     */
    public function update(<?= $modelClass ?> $model, array $postData): array
    {
        $modelsDetail = !empty($model-><?= lcfirst($modelsDetail) ?>s)
            ? $model-><?= lcfirst($modelsDetail) ?>s
            : [new <?= $modelsDetail ?>()];

        $oldIDs = ArrayHelper::map($modelsDetail, 'id', 'id');
        $modelsDetail = Tabular::createMultiple(<?= $modelsDetail ?>::class, $modelsDetail);

        Tabular::loadMultiple($modelsDetail, $postData);
        $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelsDetail, 'id', 'id')));

        $isValid = $model->validate();
        $isValid = Tabular::validateMultiple($modelsDetail) && $isValid;

        if ($isValid) {
            $transaction = <?= $modelClass ?>::getDb()->beginTransaction();
            try {
                if ($flag = $model->save(false)) {
                    if (!empty($deletedIDs)) {
                        <?= $modelsDetail ?>::deleteAll(['id' => $deletedIDs]);
                    }

                    foreach ($modelsDetail as $detail) {
                        $detail-><?= strtolower($modelClass) ?>_id = $model->id;
                        if (!($flag = $detail->save(false))) {
                            break;
                        }
                    }
                }

                if ($flag) {
                    $transaction->commit();
                    return [true, $modelsDetail];
                }
                $transaction->rollBack();
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $transaction->rollBack();
            }
        }

        return [false, $modelsDetail];
    }
}
