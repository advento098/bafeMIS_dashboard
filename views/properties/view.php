<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Properties $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Properties', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="properties-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'timestamp',
            'doc_no',
            'property_type',
            'property_no',
            'particular:ntext',
            'date_acquired',
            'unit_value',
            'possessor',
            'mr_date',
            'current_holder',
            'office',
            'operability',
            'remarks:ntext',
        ],
    ]) ?>

</div>
