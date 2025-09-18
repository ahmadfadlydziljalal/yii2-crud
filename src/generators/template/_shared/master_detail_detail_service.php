<?php

/**
 * This is the template for generating a Service class
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var $this yii\web\View */
/** @var $generator \dzil\crud\generators\Generator */

$serviceClassName = $generator->getServiceClassName();
$modelClass = StringHelper::basename($generator->modelClass);
$detailModelClass = !empty($generator->modelsClassDetail) ? StringHelper::basename($generator->modelsClassDetail) : null;
$detailDetailModelClass = !empty($generator->modelsClassDetailDetail) ? StringHelper::basename($generator->modelsClassDetailDetail) : null;

$fkToMaster = Inflector::underscore($modelClass) . '_id';
$fkToDetail = $detailModelClass ? (Inflector::underscore($detailModelClass) . '_id') : null;

echo "<?php\n";
?>

namespace app\services;

use Yii;
use <?= $generator->modelClass ?>;
<?php if ($detailModelClass): ?>
use <?= $generator->modelsClassDetail ?>;
<?php endif; ?>
<?php if ($detailDetailModelClass): ?>
use <?= $generator->modelsClassDetailDetail ?>;
<?php endif; ?>
use dzil\crud\Tabular;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use Throwable;

/**
 * A service class responsible for handling operations related to the `<?= $modelClass ?>` model.
 */
class <?= $serviceClassName . "\n" ?>
{
    /**
     * Create <?= $modelClass ?> (with detail(s)<?= $detailDetailModelClass ? ' and sub-detail(s)' : '' ?>)
     */
    public function create(<?= $modelClass ?> $model, array $post): array
    {
<?php if ($detailModelClass): ?>
        $modelsDetail = Tabular::createMultiple(<?= $detailModelClass ?>::class);
        Tabular::loadMultiple($modelsDetail, $post);

<?php if ($detailDetailModelClass): ?>
        $modelsDetailDetail = [];
<?php endif; ?>
<?php endif; ?>

        $isValid = $model->validate();
<?php if ($detailModelClass): ?>
        $isValid = Tabular::validateMultiple($modelsDetail) && $isValid;

<?php if ($detailDetailModelClass): ?>
        if (isset($post['<?= $detailDetailModelClass ?>'][0][0])) {
            foreach ($post['<?= $detailDetailModelClass ?>'] as $i => $detailDetails) {
                foreach ($detailDetails as $j => $detailDetail) {
                    $data['<?= $detailDetailModelClass ?>'] = $detailDetail;
                    $m = new <?= $detailDetailModelClass ?>();
                    $m->load($data);
                    $modelsDetailDetail[$i][$j] = $m;
                    $isValid = $m->validate() && $isValid;
                }
            }
        }
<?php endif; ?>
<?php endif; ?>

        if (!$isValid) {
            return [false, $modelsDetail, $modelsDetailDetail];
        }

        $transaction = <?= $modelClass ?>::getDb()->beginTransaction();
        try {
            if (!$model->save(false)) {
                $transaction->rollBack();
                return [false, $modelsDetail, $modelsDetailDetail];
            }

<?php if ($detailModelClass): ?>
            foreach ($modelsDetail as $i => $detail) {
                $detail-><?= $fkToMaster ?> = $model->id;
                if (!$detail->save(false)) {
                    $transaction->rollBack();
                    return [false, $modelsDetail, $modelsDetailDetail];
                }

<?php if ($detailDetailModelClass): ?>
                if (isset($modelsDetailDetail[$i])) {
                    foreach ($modelsDetailDetail[$i] as $subDetail) {
                        $subDetail-><?= $fkToDetail ?> = $detail->id; // ✅ snake_case FK
                        if (!$subDetail->save(false)) {
                            $transaction->rollBack();
                            return [false, $modelsDetail, $modelsDetailDetail];
                        }
                    }
                }
<?php endif; ?>
            }
<?php endif; ?>

            $transaction->commit();
            return [true, $modelsDetail, $modelsDetailDetail];

        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return [false, $modelsDetail, $modelsDetailDetail];
        }
    }

    /**
     * Update <?= $modelClass ?> with its detail and the sub-detail.
     *
     * Controller will passing:
     *   - $model (loaded master),
     *   - $postData (Yii::$app->request->post()),
     *   - $modelsDetail (array of detail models from DB / placeholders),
     *   - $modelsDetailDetail (array of arrays of sub-detail models from DB / placeholders)
     *
     * @param <?= $modelClass ?> $model
     * @param array $postData
     * @param <?= $detailModelClass ?>[] $modelsDetail
     * @param <?= $detailDetailModelClass ?>[][] $modelsDetailDetail
     * @return array
     * @throws Throwable
     * @throws Exception
     */
    public function update(<?= $modelClass ?> $model, array $postData, array $modelsDetail, array $modelsDetailDetail): array
    {
        // build a mapping ID -> model for old sub-details, and a list of old sub-detail IDs
        $oldDetailIds = ArrayHelper::map($modelsDetail, 'id', 'id'); // id => id (for deleted compare)

        $oldSubDetailMap = []; // id => Model (instances)
        $oldSubDetailIds = []; // list of ids
        foreach ($modelsDetailDetail as $subArr) {
            if (!is_array($subArr)) {
                continue;
            }
            $indexed = ArrayHelper::index($subArr, 'id');
            $oldSubDetailMap = ArrayHelper::merge($oldSubDetailMap, $indexed);
            $oldSubDetailIds = array_merge($oldSubDetailIds, array_keys($indexed));
        }

        // recreate modelsDetail from posted data (Tabular)
        $modelsDetail = Tabular::createMultiple(<?= $detailModelClass ?>::class, $modelsDetail);
        Tabular::loadMultiple($modelsDetail, $postData);

        // compute deleted detail IDs
        $newDetailIds = array_filter(ArrayHelper::map($modelsDetail, 'id', 'id'));
        $deletedDetailIds = array_diff($oldDetailIds, $newDetailIds);

        // prepare sub-details from POST: build new models and collect posted ids
        $postedSubDetailIds = [];
        $newModelsDetailDetail = []; // indexed by detail index ($i)
<?php if ($detailDetailModelClass): ?>
        if (isset($postData['<?= $detailDetailModelClass ?>'][0][0])) {
            foreach ($postData['<?= $detailDetailModelClass ?>'] as $i => $subDetails) {
                // collect posted ids for this detail index
                $postedSubDetailIds = ArrayHelper::merge($postedSubDetailIds, array_filter(ArrayHelper::getColumn($subDetails, 'id')));
                foreach ($subDetails as $j => $subDetailData) {
                    $data['<?= $detailDetailModelClass ?>'] = $subDetailData;
                    $subId = $subDetailData['id'] ?? null;

                    $subModel = (isset($subId) && isset($oldSubDetailMap[$subId]))
                        ? $oldSubDetailMap[$subId] // reuse existing instance
                        : new <?= $detailDetailModelClass ?>();
                    $subModel->load($data);
                    $newModelsDetailDetail[$i][$j] = $subModel;
                }
            }
        }
<?php endif; ?>

        // deleted sub-detail IDs = old minus posted
        $deletedSubDetailIds = array_diff($oldSubDetailIds, $postedSubDetailIds);

        // validate master + details + sub-details
        $isValid = $model->validate();
        $isValid = Tabular::validateMultiple($modelsDetail) && $isValid;
        foreach ($newModelsDetailDetail as $arr) {
            if (!empty($arr)) {
                foreach ($arr as $m) {
                    $isValid = $m->validate() && $isValid;
                }
            }
        }

        if (!$isValid) {
            return [false, $modelsDetail, $modelsDetailDetail];
        }

        $transaction = <?= $modelClass ?>::getDb()->beginTransaction();
        try {
            // save master
            if (!$model->save(false)) {
                $transaction->rollBack();
                return [false, $modelsDetail, $modelsDetailDetail];
            }

            // delete removed sub-details and details
            if (!empty($deletedSubDetailIds)) {
                <?= $detailDetailModelClass ?>::deleteAll(['id' => $deletedSubDetailIds]);
            }
            if (!empty($deletedDetailIds)) {
                <?= $detailModelClass ?>::deleteAll(['id' => $deletedDetailIds]);
            }

            // save (new/updated) details and their sub-details
            foreach ($modelsDetail as $i => $detail) {
                $detail-><?= $fkToMaster ?> = $model->id;
                if (!$detail->save(false)) {
                    $transaction->rollBack();
                    return [false, $modelsDetail, $modelsDetailDetail];
                }

                if (isset($newModelsDetailDetail[$i]) && is_array($newModelsDetailDetail[$i])) {
                    foreach ($newModelsDetailDetail[$i] as $subDetail) {
                        $subDetail-><?= $fkToDetail ?> = $detail->id;
                        if (!$subDetail->save(false)) {
                            $transaction->rollBack();
                            return [false, $modelsDetail, $modelsDetailDetail];
                        }
                    }
                }
            }

            $transaction->commit();
            return [true, $modelsDetail, $modelsDetailDetail];
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return [false, $modelsDetail, $modelsDetailDetail];
        }
    }
}
