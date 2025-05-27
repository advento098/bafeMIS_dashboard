<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;
use yii\bootstrap5\Dropdown;
use controllers\SiteController;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="@web/css/site.css" rel="stylesheet" />

    <title><?= Html::encode($this->title) ?></title>
    <?= \yii\helpers\Html::csrfMetaTags() ?>
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <header id="header" class="d-flex align-items-center">
        <?php
        NavBar::begin([
            // 'brandLabel' => Yii::$app->name,
            'brandLabel' => Html::img('@web/assets/img/logo.png', [
                'alt' => Yii::$app->name,
                'style' => 'height:80px; display:inline; margin-right:30px;'
            ]) .
                '<span class="d-inline-block align-middle" style="font-size: 40px; font-family: Arial, sans-serif; font-weight: bold; color: #FF9008;">'
                . Html::encode(Yii::$app->name) .
                '</span>',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top ']
        ]);

        // // Variables ----------------------------------------------------------------
        $options = Yii::$app->view->params['options'] ?? [];
        $options = array_merge($options, [
            null => 'All Offices',
        ]);
        ksort($options);
        $currentOffice = Yii::$app->request->get('office') ?? '';
        $currentLabel = $options[$currentOffice];
        // // End Variables ------------------------------------------------------------

        echo '<div class="d-flex align-items-end justify-content-end" style="width: 88%;">';
        echo Html::beginForm(Url::to(['site/index']), 'get', [
            'id' => 'office-form',
            // 'class' => 'dropdown d-flex h-100 align-items-center justify-items-center border border-danger',
            'class' => 'dropdown',
            'style' => 'height: 40px; margin: 0; padding: 0;',
        ]);

        echo Html::button(
            '<span class="align-middle text-truncate d-inline-block" style="max-width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">' . Html::encode($currentLabel) . '</span> <span class="dropdown-toggle ms-2"></span>',
            [
                'class' => 'btn btn-secondary d-flex w-100 h-100 align-items-center justify-content-between px-3',
                'type' => 'button',
                'id' => 'dropdownMenuButton',
                'data-bs-toggle' => 'dropdown',
                'aria-expanded' => 'false',
                'title' => $currentLabel,
                'style' => 'max-width: 400px; min-width: 200px; white-space: nowrap;',
                'encode' => false,
            ]
        );

        echo '<ul class="dropdown-menu w-auto" aria-labelledby="dropdownSearchButton" id="dropdownSearchMenu">';
        foreach ($options as $key => $label) {
            echo Html::tag('li', Html::a(Html::encode($label), Url::to(['site/index', 'office' => $key]), [
                'class' => 'dropdown-item',
                'data-value' => $key,
                'data-label' => $label,
            ]));
        }
        echo '</ul>';
        echo Html::endForm();
        echo '</div>';
        // echo Nav::widget([
        //     'options' => ['class' => 'navbar-nav', 'style' => 'width: 350px;'],
        //     'items' => [
        //         ['label' => 'Dashboard', 'url' => ['/site/index']],
        //         // ['label' => 'About', 'url' => ['/site/about']],
        //         // ['label' => 'Contact', 'url' => ['/site/contact']],
        //         // // ['label' => 'Properties Test', 'url' => ['/properties/index']],
        //         // Yii::$app->user->isGuest
        //         //     ? ['label' => 'Login', 'url' => ['/site/login']]
        //         //     : '<li class="nav-item">'
        //         //     . Html::beginForm(['/site/logout'])
        //         //     . Html::submitButton(
        //         //         'Logout (' . Yii::$app->user->identity->username . ')',
        //         //         ['class' => 'nav-link btn btn-link logout']
        //         //     )
        //         //     . Html::endForm()
        //         //     . '</li>'
        //     ]
        // ]);
        NavBar::end();
        ?>
    </header>

    <main id="main" class="flex-shrink-0 mt-5" role="main">
        <div class="container">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <footer id="footer" class="mt-auto mb-5 py-3 bg-light rounded w-50 mx-auto">
        <div class="container">
            <div class="row text-muted">
                <div class="col-md-6 text-center text-md-start">&copy; BafeMIS </div>
                <div class="col-md-6 text-center text-md-end">Copyright <?= date('Y') ?></div>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php $this->registerJsFile('@web/js/site/charts.js', ['depends' => [\yii\web\JqueryAsset::class]]); ?>
</body>

</html>
<?php $this->endPage() ?>