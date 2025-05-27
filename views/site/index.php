<!-- #00712D - green -->
<!-- #0AB429 - lightgree -->
<!-- #9AFA00 lighter green -->
<!-- #FF9008 - orange -->
<!-- #E77706 - darker orange -->
<!-- #F5C799 - semi transparent orange -->
<!-- #FFE203 - yellow -->
<!-- #D73600 - red -->

<?php

use yii\grid\GridView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $lineGraphProvider */
/** @var yii\data\ArrayDataProvider $barGraphProvider*/
/** @var yii\data\ArrayDataProvider $forDispProvider*/
/** @var yii\data\ArrayDataProvider $serviceableProvider*/
/** @var yii\data\ArrayDataProvider $holdersCountProvider*/
/** @var yii\data\ArrayDataProvider $totalUnitValueProvider*/
/** @var yii\data\ArrayDataProvider $getTotalUnitValuePerOfficeProvider*/
/** @var yii\data\ArrayDataProvider $getServiceableFYearProvider*/
/** @var yii\data\ArrayDataProvider $getDropOptionsProvider*/
/** @var yii\data\ArrayDataProvider $getPropDispAmntPerYearProvider*/
/** @var yii\data\ArrayDataProvider $getPropDispCntPerYearProvider*/
/** @var yii\data\ArrayDataProvider $getPropAmountPerYearProvider*/

// AppAsset::register($this);

$this->title = 'BafeMIS';

// $options = $getDropOptionsProvider->getModels() ?? 'No data available';

// $this->params['options'] = $options;
$dropModels = $getDropOptionsProvider->getModels() ?? 'No data available';

$propAmountModels = $getPropAmountPerYearProvider->getModels() ?? 'No data available';
$propAmountLabels = [];
$propAmountData = [];
foreach ($propAmountModels as $model) {
    $propAmountLabels[] = $model['Year'];
    $propAmountData[] = $model['Value'];
}
echo '<script> console.log(' . json_encode($propAmountModels) . ');</script>';

$propCountModels = $getPropDispCntPerYearProvider->getModels() ?? 'No data available';
$propCountLabels = [];
$propCountData = [];
foreach ($propCountModels as $model) {
    $propCountLabels[] = $model['Year'];
    $propCountData[] = $model['count'];
}


$propModel = $getPropDispAmntPerYearProvider->getModels();
$propDispLabels = [];
$propDispData = [];

foreach ($propModel as $model) {
    $propDispLabels[] = $model['Year'];
    $propDispData[] = $model['Value'];
}

// Format dropdown list: ['OfficeName' => 'OfficeName']
$options = [];
foreach ($dropModels as $item) {
    $office = $item['Office'];
    $options[$office] = $office;
}

// Pass to view via $this->params
\Yii::$app->view->params['options'] = $options;

$fYearServiceableModels = $getServiceableFYearProvider->getModels() ?? 'No data available';
$fYearServiceableLabels = [];
$fYearServiceableData = [];

foreach ($fYearServiceableModels as $model) {
    $fYearServiceableLabels[] = $model['property_type'];
    $fYearServiceableData[] = $model['Count'];
}

$perOfficeModels = $getTotalUnitValuePerOfficeProvider->getModels();
$perOfficeLabels = [];
$perOfficeTotal = [];
$totalUnitValue = 0;

foreach ($perOfficeModels as $model) {
    $perOfficeLabels[] = $model['Property Type'];
    $perOfficeTotal[] = $model['Total Unit Value'];
}

$dispLabel = $forDispProvider->getModels()[0]['operability'] ?? 'For Disposal';
$dispCount = $forDispProvider->getModels()[0]['count'] ?? '0';

$serviceableLabel = "Serviceable";
$serviceableCount = $serviceableProvider->getModels()[0]['count'] ?? '0';

$numberOfHolders = $holdersCountProvider->getModels()['count'] ?? 'No data available';
$rawNumber = $totalUnitValueProvider->getModels()[0]['total_unit_value'] ?? 'No data available';
// $rawNumber = $totalUnitValueProvider->getModels()[0]['Total Unit Value'] ?? 'No data available';

$number = str_replace([',', '₱'], '', $rawNumber);

$totalUnitValue = '₱' . number_format($number / 1_000_000, 2) . 'M' ?? 'No data available';

// $fdPropertyNumber = [];
// $fdParticular = [];
// $fdDateAcquired = [];
// $fdUnitValue = [];
// $fdCurrentHolder = [];
// $fdOffice = [];

// $fdModels = $forDispModalProvider->getModels();
// $fbTitles = array_keys($fdModels[0]);

// foreach ($fdModels as $model) {
//     $fdPropertyNumber[] = $model['property_no'];
//     $fdParticular[] = $model['particular'];
//     $fdDateAcquired[] = $model['date_acquired'];
//     $fdUnitValue[] = $model['unit_value'];
//     $fdCurrentHolder[] = $model['current_holder'];
//     $fdOffice[] = $model['office'];
// }

$barModels = $barGraphProvider->getModels();
$barLabels = [];
$barData = [];

foreach ($barModels as $model) {
    $barLabels[] = $model['property_type'];
    $numericValue = (int) $model['count'];
    $barData[] = $numericValue;
}

// Prepare data from the $dataProvider
$models = $lineGraphProvider->getModels();
$lineLabels = [];
$lineData = [];

foreach ($models as $model) {
    $lineLabels[] = $model['year'];
    $numericValue = $model['count'] ?? 0;
    $lineData[] = $numericValue;
}

$currentOffice = Yii::$app->request->get('office') ?? '';
$currentLabel = $options[$currentOffice] ?? null;

// Pass PHP data to JS
$this->registerJsVar('data', [
    'barLabels' => $barLabels,
    'barData' => $barData,
    'lineLabels' => $lineLabels,
    'lineData' => $lineData,
    'perOfficeLabels' => $perOfficeLabels,
    'perOfficeTotal' => $perOfficeTotal,
    'totalUnitValue' => $rawNumber,
    'fYearServiceableLabels' => $fYearServiceableLabels,
    'fYearServiceableData' => $fYearServiceableData,
    'dropOptions' => $options,
    'office' => $currentLabel,
    'propDispLabels' => $propDispLabels,
    'propDispData' => $propDispData,
    'propDispCntLabels' => $propCountLabels,
    'propDispCntData' => $propCountData,
    'propAmountLabels' => $propAmountLabels,
    'propAmountData' => $propAmountData,
]);

// Include your external JS file
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile("https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css");
$this->registerJsFile("https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation", ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile("https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js", ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile("https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2", ['depends' => [\yii\web\JqueryAsset::class]]);


$this->registerJsFile('@web/js/site/charts.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>
<script>
    window.apiUrls = {
        forDisposalsJson: '<?= \yii\helpers\Url::to(['site/for-disposals-json']) ?>'
    };
</script>

<div class="site-index container my-auto">
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-4 d-flex flex-column gap-3">
            <!-- Bar Chart -->
            <div class="border rounded shadow p-3 text-center bg-white">
                <h3 style="font-size: 15px; font-weight: bold;"> Number of Items</h3>
                <canvas id="barChart"></canvas>
            </div>

            <!-- Disposable and Serviceable Counters -->
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded border shadow text-center h-100 bg-white" data-bs-toggle="modal" data-bs-target="#dispModal" style="cursor: pointer;">
                        <h1><?= $dispCount ?></h1>
                        <p><?= $dispLabel ?></p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded border shadow text-center h-100 bg-white" data-bs-toggle="modal" data-bs-target="#servModal" style="cursor: pointer;">
                        <h1><?= $serviceableCount ?></h1>
                        <p><?= $serviceableLabel ?></p>
                    </div>
                </div>
            </div>

            <!-- Line Chart -->
            <div class="border rounded shadow p-3 text-center bg-white">
                <h3 style="font-size: 15px; font-weight: bold;">Items Acquired per Year</h3>
                <canvas id="lineChart"></canvas>
            </div>
        </div>

        <!-- Center Column ---------------------------------------------------------------------------------------------------------------------------------------->
        <div class="col-lg-4 d-flex flex-column gap-3">
            <!-- Property disposal count per year -->
            <div class="border rounded shadow p-3 text-center bg-white">
                <h3 style="font-size: 15px; font-weight: bold;">Property Disposal Count Per Year</h3>
                <canvas id="propDispCountPerYear"></canvas>
            </div>

            <!-- Property disposal amount per year -->
            <div class="border rounded shadow p-3 text-center bg-white">
                <h3 style="font-size: 15px; font-weight: bold;">Property Disposal Amount Per Year</h3>
                <canvas id="propDispAmountPerYear" style="width: 100%; height: 100%;"></canvas>
            </div>

            <!-- Property amount per year -->
            <div class="border rounded shadow p-3 text-center bg-white">
                <h3 style="font-size: 15px; font-weight: bold;">Property Amount Per Year</h3>
                <canvas id="propAmountPerYear"></canvas>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4 d-flex flex-column gap-3">
            <!-- Number of Holders and Total Unit Value -->
            <div class="row g-3">
                <div class="col-6">
                    <div class="rounded border shadow text-center p-3 h-100 bg-white" data-bs-toggle="modal" data-bs-target="#holdModal" style="cursor: pointer; font-size: 15px;">
                        <h1 style="font-size: 36px"><?= $numberOfHolders ?></h1>
                        <p>Number of holders</p>
                    </div>
                </div>
                <div class=" col-6">
                    <div class="rounded border shadow text-center p-3 h-100 bg-white" style="font-size: 15px;">
                        <h1 style="font-size: 36px"><?= $totalUnitValue ?></h1>
                        <p>Total Unit Value</p>
                    </div>
                </div>
            </div>

            <!-- Doughnut chart -->
            <div class="border rounded shadow text-center p-3 d-flex justify-content-center align-items-center bg-white"
                style="width: 100%; max-width: 700px; height: 353px;">

                <canvas id="pieChart" style="width: 100%; height: 100%;"></canvas>
            </div>

            <!-- Additional Bar Chart -->
            <div class="border rounded shadow p-3 text-center bg-white">
                <h3 style="font-size: 15px; font-weight: bold;">Serviceable for Over 5 Years</h3>
                <canvas id="rBar"></canvas>
            </div>
        </div>
    </div>
</div>




<!-- Modal for Disposable -->
<div id="dispModal" class="modal fade" tabindex="-1" aria-labelledby="dispModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 70vw; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dispModalLabel"><b><?= $dispLabel ?></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="loadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="tableContainer" class="table-responsive w-100">
                    <table id="disposal_table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->

            </div>
        </div>
    </div>
</div>

<!-- Modal for Serviceable -->
<div id="servModal" class="modal fade" tabindex="-1" aria-labelledby="servModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 70vw; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="servModalLabel"><b><?= $serviceableLabel ?></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="servLoadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="servTableContainer" class="table-responsive w-100">
                    <table id="serviceable_table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for Holders -->
<div id="holdModal" class="modal fade" tabindex="-1" aria-labelledby="holdersModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 70vw; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="holdersModalLabel"><b><?= "Holders" ?></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="holdLoadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="holdTableContainer" class="table-responsive w-100">
                    <table id="holders_table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for detailed holders table -->
<div id="detailedHoldModal" class="modal fade" tabindex="-1" aria-labelledby="detailedHoldersModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 70vw; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row mx-3 w-100">
                    <div class="col-12">
                        <div class="modal-header position-relative">
                            <button type="button" id="toHoldersBtn" class="btn position-absolute start-0 ms-3">
                                ← Back
                            </button>

                            <h5 class="modal-title mx-auto text-center">
                                <b>Detailed Holder Info</b>
                            </h5>

                            <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="detailedHoldLoadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="detailedHoldTableContainer" class="table-responsive w-100">
                    <table id="detailed_table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for total number of items (bar graph) -->
<div id="barModal" class="modal fade" tabindex="-1" aria-labelledby="barModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: none; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="barModalLabel"><b></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="barLoadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="barTableContainer" class="table-responsive w-100">
                    <table id="barN_table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for items acquired per year (line graph) -->
<div id="acquiredPerYearModal" class="modal fade" tabindex="-1" aria-labelledby="acquiredPerYearModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: none; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="acquiredPerYearModalLabel"><b></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="acquiredPerYearLoadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="acquiredPerYearTableContainer" class="table-responsive w-100">
                    <table id="acquiredPerYear_table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for serviceable over 5 year per property_type (horizontal bar graph) -->
<div id="propertyServiceablesModal" class="modal fade" tabindex="-1" aria-labelledby="propertyServiceablesModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: none; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="propertyServiceablesModalLabel"><b></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="propertyServiceablesLoadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="propertyServiceablesTableContainer" class="table-responsive w-100">
                    <table id="propertyServiceables_table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for Property Disposable count per year -->
<div id="propDispCntPerYearModal" class="modal fade" tabindex="-1" aria-labelledby="propDispCntPerYearModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: none; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="propDispCntPerYearModalLabel"><b></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="propDispCntPerYearLoadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="propDispCntPerYearTableContainer" class="table-responsive w-100">
                    <table id="propDispCntPerYear_table" class="table table-bordered table-hover" style="width: fit-content;">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for Property Disposable Amount per year -->
<div id="propDispPerYearModal" class="modal fade" tabindex="-1" aria-labelledby="propDispPerYearModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: none; width: fit-content;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="propDispPerYearModalLabel"><b></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="width: auto;">
                <!-- Loading icon -->
                <div id="propDispPerYearLoadingScreenCont" class="loading-container">
                    <div id='loadingSpinner'></div>
                </div>

                <!-- Table -->
                <div id="propDispPerYearTableContainer" class="table-responsive w-100">
                    <table id="propDispPerYear_table" class="table table-bordered table-hover" style="width: fit-content;">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- End of table -->
            </div>
        </div>
    </div>
</div>