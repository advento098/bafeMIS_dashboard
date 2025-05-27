<?php

use app\models\Properties;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\PropertiesSearch $searchModel */
/** @var yii\data\ArrayDataProvider $dataProvider */

$this->title = 'Properties';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="properties-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Properties', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'timestamp',
            'doc_no',
            'property_type',
            'total_unit_value',
            'percentage',
            // 'property_no',
            //'particular:ntext',
            //'date_acquired',
            //'unit_value',
            //'possessor',
            //'mr_date',
            //'current_holder',
            //'office',
            //'operability',
            //'remarks:ntext',

            // Test of adding this column

            // [
            //     'label' => 'Details',
            //     'format' => 'raw',
            //     'value' => function ($data) {
            //         // example static link (or pass something meaningful if available)
            //         return '<a href="#">View</a>';
            //     },
            // ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>