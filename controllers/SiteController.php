<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Properties;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'bar-total-number-ajax' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    // public function actionMain()
    // {
    //     return $this->render('main', [
    //         'model' => (new Properties())->dropOptions(Yii::$app->request->queryParams),
    //     ]);
    // }

    public function actionIndex()
    {
        // return $this->render('index');
        // $searchModel = new PropertiesSearch();
        // $lineGraphProvider = (new Properties())->searchSummary(Yii::$app->request->queryParams);
        // $lineGraphProvider = (new Properties())->acqrdPerYear(Yii::$app->request->queryParams);
        // $barGraphProvider = (new Properties())->getNumberOfItems(Yii::$app->request->queryParams);
        // $forDispProvider = (new Properties())->forDisposals(Yii::$app->request->queryParams);
        // $forDispModalProvider = (new Properties())->forDisposalsModal(Yii::$app->request->queryParams);
        // $serviceableProvider = (new Properties())->servicables(Yii::$app->request->queryParams);
        // $holdersCountProvider = (new Properties())->holdersCount(Yii::$app->request->queryParams);
        // $totalUnitValueProvider = (new Properties())->getTotalUnitValue(Yii::$app->request->queryParams);
        // $getTotalUnitValuePerOfficeProvider = (new Properties())->getTotalUnitValuePerOffice(Yii::$app->request->queryParams);
        // $getServiceableFYearProvider = (new Properties())->getServiceableFYear(Yii::$app->request->queryParams);

        $properties = (new Properties())->getData(Yii::$app->request->queryParams);

        // return $this->render('index', [
        //     // 'searchModel' => $searchModel,
        //     'lineGraphProvider' => $lineGraphProvider,
        //     'barGraphProvider' => $barGraphProvider,
        //     'forDispProvider' => $forDispProvider,
        //     'forDispModalProvider' => $forDispModalProvider,
        //     'serviceableProvider' => $serviceableProvider,
        //     'holdersCountProvider' => $holdersCountProvider,
        //     'totalUnitValueProvider' => $totalUnitValueProvider,
        //     'getTotalUnitValuePerOfficeProvider' => $getTotalUnitValuePerOfficeProvider,
        //     'getServiceableFYearProvider' => $getServiceableFYearProvider,
        //     // 'properties' => $result['models'],
        //     // 'pagination' => $result['pagination'],
        // ]);

        return $this->render('index', [
            'lineGraphProvider' => $properties['lineGraphProvider'],
            'barGraphProvider' => $properties['barGraphProvider'],
            'forDispProvider' => $properties['forDispProvider'],
            'serviceableProvider' => $properties['serviceableProvider'],
            'holdersCountProvider' => $properties['holdersCountProvider'],
            'totalUnitValueProvider' => $properties['totalUnitValueProvider'],
            'getTotalUnitValuePerOfficeProvider' => $properties['getTotalUnitValuePerOfficeProvider'],
            'getServiceableFYearProvider' => $properties['getServiceableFYearProvider'],
            'getDropOptionsProvider' => $properties['getDropOptionsProvider'],
            'getPropDispAmntPerYearProvider' => $properties['getPropDispAmntPerYear'],
            'getPropDispCntPerYearProvider' => $properties['getPropDispCntPerYear'],
            'getPropAmountPerYearProvider' => $properties['getPropAmountPerYearProvider'],
        ]);
    }

    // function for property disposals amount per year
    public function actionPropDispAmntPerYearAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params = Yii::$app->request->post();
        $year = Yii::$app->request->post('year');

        if (!$year) {
            return [
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Invalid year'
            ];
        }

        return \app\models\Properties::getPropDispAmntPerYearModal($year, $params);
    }

    public function actionPropDispCntPerYearAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params = Yii::$app->request->post();
        $year = Yii::$app->request->post('year');

        if (!$year) {
            return [
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Invalid year'
            ];
        }

        return \app\models\Properties::getPropDispAmntPerYearModal($year, $params);
    }

    public function actionForDisposalsAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Pass queryParams to the model's method
        return \app\models\Properties::forDisposalsModal(Yii::$app->request->queryParams);
    }

    public function actionServiceablesAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return \app\models\Properties::serviceablesModal(Yii::$app->request->queryParams);
    }

    public function actionHoldersAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return \app\models\Properties::getHoldersModal(Yii::$app->request->queryParams);
    }

    public function actionHolderDetailsAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return \app\models\Properties::getByCurrentHolder(Yii::$app->request->queryParams);
    }


    public function actionBarTotalNumberAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params = Yii::$app->request->post();
        $propertyType = Yii::$app->request->post('property_type');

        if (!$propertyType) {
            return [
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Invalid property type'
            ];
        }

        return \app\models\Properties::getPropertyTypeInfo($propertyType, $params);
    }

    public function actionItemsPerYearAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params = Yii::$app->request->post();
        $dateAcquired = Yii::$app->request->post('date');

        if (!$dateAcquired) {
            return [
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Invalid year'
            ];
        }

        return \app\models\Properties::getAcquiredOnYear($dateAcquired, $params);
    }

    public static function actionPropertyServiceablesAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params = Yii::$app->request->post();
        $propertyType = Yii::$app->request->post('property_type');

        if (!$propertyType) {
            return [
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Invalid property type'
            ];
        }

        return \app\models\Properties::getPropertyServiceables($propertyType, $params);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionChart()
    {
        return $this->render('chart'); // views/site/chart.php
    }
}
