<?php

namespace app\models;

use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

/**
 * This is the model class for table "properties".
 *
 * @property int $id
 * @property string $timestamp
 * @property string $doc_no
 * @property string $property_type
 * @property string $property_no
 * @property string $particular
 * @property string $date_acquired
 * @property float $unit_value
 * @property int $possessor
 * @property string|null $mr_date
 * @property string $current_holder
 * @property string $office
 * @property string $operability
 * @property string $remarks
 */
class Properties extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'properties';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['timestamp', 'date_acquired', 'mr_date'], 'safe'],
            [['doc_no', 'property_type', 'property_no', 'particular', 'date_acquired', 'unit_value', 'possessor', 'current_holder', 'office', 'operability', 'remarks'], 'required'],
            [['particular', 'remarks'], 'string'],
            [['unit_value'], 'number'],
            [['possessor'], 'integer'],
            [['doc_no', 'property_type', 'property_no', 'current_holder', 'office'], 'string', 'max' => 100],
            [['operability'], 'string', 'max' => 220],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'timestamp' => 'Timestamp',
            'doc_no' => 'Doc No',
            'property_type' => 'Property Type',
            'property_no' => 'Property No',
            'particular' => 'Particular',
            'date_acquired' => 'Date Acquired',
            'unit_value' => 'Unit Value',
            'possessor' => 'Possessor',
            'mr_date' => 'Mr Date',
            'current_holder' => 'Current Holder',
            'office' => 'Office',
            'operability' => 'Operability',
            'remarks' => 'Remarks',
        ];
    }

    public function dropOptions($params)
    {
        // $office = $params['office'] ?? null;

        $query = self::find()
            ->select(['office as Office'])
            ->from('properties')
            ->orderBy(['Office' => SORT_ASC]);

        $result = $query->asArray()->all();

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['Office'],
            ],
        ]);
    }

    // public function acqrdPerYear()
    // {
    //     $sql = "SELECT YEAR(date_acquired) as year, COUNT(*) as count
    //             FROM `properties`
    //             GROUP by year
    //             ORDER BY year";

    //     $result = Yii::$app->db->createCommand($sql)->queryAll();

    //     return new ArrayDataProvider([
    //         'allModels' => $result,
    //         'pagination' => false,
    //         'sort' => [
    //             'attributes' => ['year', 'count'],
    //         ],
    //     ]);
    // }

    public function acqrdPerYear($queryParams = [])
    {
        $office = $queryParams['office'] ?? null;

        // $query = self::find()
        //     ->select(['SUM(unit_value) as Value', 'YEAR(date_acquired) as Year'])
        //     ->groupBy('Year');

        $sql = self::find()
            ->select(['YEAR(date_acquired) as year', 'COUNT(*) as count'])
            ->groupBy('year')
            ->orderBy(['year' => SORT_ASC]);
        if ($office) {
            $sql->andWhere(['office' => $office]);
        }

        $result = $sql->asArray()->all();

        // echo '<script>console.log("The SQL:");</script>';
        // echo '<script>console.log(' . json_encode($result) . ');</script>';


        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['year', 'count'],
            ],
        ]);
    }


    // Added this public function
    // public function searchSummary($params)
    // {
    //     $sql = "
    //         SELECT
    //         property_type,
    //         CONCAT('₱', FORMAT(SUM(unit_value), 2)) AS total_unit_value,
    //         CONCAT(FORMAT((SUM(unit_value) / total_value.total_sum) * 100, 2), '%') AS percentage
    //         FROM
    //         properties,
    //         (SELECT SUM(unit_value) AS total_sum FROM properties) AS total_value
    //         GROUP BY
    //         property_type
    //         ORDER BY
    //         SUM(unit_value) DESC
    //     ";

    //     $rows = Yii::$app->db->createCommand($sql)->queryAll();

    //     return new ArrayDataProvider([
    //         'allModels' => $rows,
    //         'pagination' => false,
    //         'sort' => [
    //             'attributes' => ['property_type', 'total_unit_value', 'percentage'],
    //         ],
    //     ]);
    // }

    public function searchSummary($params)
    {
        $office = $params['office'] ?? null;

        // Base query
        $sql = "
        SELECT
            property_type,
            CONCAT('₱', FORMAT(SUM(unit_value), 2)) AS total_unit_value,
            CONCAT(FORMAT((SUM(unit_value) / total_value.total_sum) * 100, 2), '%') AS percentage
        FROM
            properties,
            (SELECT SUM(unit_value) AS total_sum FROM properties {where_sub}) AS total_value
        {where_main}
        GROUP BY
            property_type
        ORDER BY
            SUM(unit_value) DESC
        ";

        $whereClause = '';
        $paramsSql = [];

        if ($office) {
            $whereClause = 'WHERE office = :office';
            $paramsSql[':office'] = $office;
        }

        // Replace the placeholders
        $sql = str_replace('{where_main}', $whereClause, $sql);
        $sql = str_replace('{where_sub}', $whereClause, $sql);

        // Run the query
        $rows = Yii::$app->db->createCommand($sql)
            ->bindValues($paramsSql)
            ->queryAll();

        return new ArrayDataProvider([
            'allModels' => $rows,
            'pagination' => false,
            'sort' => [
                'attributes' => ['property_type', 'total_unit_value', 'percentage'],
            ],
        ]);
    }


    // public function getNumberOfItems()
    // {
    //     $sql = "SELECT property_type, COUNT(*) as count
    //             FROM `properties`
    //             GROUP by property_type
    //             ORDER BY `count` DESC";

    //     $result = Yii::$app->db->createCommand($sql)->queryAll();

    //     return new ArrayDataProvider([
    //         'allModels' => $result,
    //         'pagination' => false,
    //         'sort' => [
    //             'attributes' => ['property_type', 'count'],
    //         ],
    //     ]);
    // }


    // function for property disposals amount per year
    public function getPropDispAmntPerYear($params)
    {
        $office = $params['office'] ?? null;

        $query = self::find()
            ->select(['sum(unit_value) as Value', 'YEAR(timestamp) as Year'])
            ->where(['operability' => 'For disposal'])
            ->groupBy('Year');

        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        $result = $query->asArray()->all();

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['Year', 'Value'],
            ],
        ]);
    }

    public static function getPropDispAmntPerYearModal($year, $params)
    {
        // $query = self::find()
        //     ->select(['COUNT(possessor) as count', 'YEAR(timestamp) as Year'])
        //     ->where(['operability' => 'For disposal'])
        //     ->groupBy('Year');

        // if ($office) {
        //     $query->andWhere(['office' => $office]);
        // }
        $office = $params['office'] ?? null;
        $query = self::find()
            ->select([
                'properties.particular AS particular',
                'properties.unit_value AS unit_value',
                'members_profile.full_name AS full_name',
                'properties.current_holder AS current_holder',
            ])
            ->innerJoin('members_profile', 'properties.possessor = members_profile.membership_id')
            ->where(['operability' => 'For disposal'])
            ->andWhere(new Expression('YEAR(properties.timestamp) = :year', [':year' => $year]));

        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Get total count *before* filtering
        $totalRecords = $query->count();

        // Search
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $query->andFilterWhere([
                'or',
                ['like', 'properties.particular', $search],
                ['like', 'properties.unit_value', $search],
                ['like', 'members_profile.full_name', $search],
                ['like', 'properties.current_holder', $search],
            ]);
        }

        $filteredCount = $query->count(); // Count after filtering

        // Pagination
        $offset = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['length']) ? (int)$params['length'] : 10;
        $query->offset($offset)->limit($limit);

        // Sorting
        if (!empty($params['order'][0])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'] === 'desc' ? SORT_DESC : SORT_ASC;
            $query->orderBy([$columnName => $dir]);
        }

        // Fetch results
        $models = $query->asArray()->all();

        foreach ($models as &$row) {
            $row['Possessor'] = $row['full_name']; // Rename to match frontend
            unset($row['full_name']); // Clean up
        }

        // Return in DataTables format
        return [
            // 'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
            // 'recordsTotal' => $totalRecords,
            // 'recordsFiltered' => $filteredCount,
            // 'data' => $models,
            'draw' => (int)($params['draw'] ?? 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredCount,
            'data' => $models,
        ];
    }

    public function getPropDispCntPerYear($params)
    {
        $office = $params['office'] ?? null;

        $query = self::find()
            ->select(['COUNT(possessor) as count', 'YEAR(timestamp) as Year'])
            ->where(['operability' => 'For disposal'])
            ->groupBy('Year');

        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        $result = $query->asArray()->all();

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['count', 'Year'],
            ],
        ]);
    }

    public function getPropAmountPerYear($params)
    {
        $office = $params['office'] ?? null;

        $query = self::find()
            ->select(['SUM(unit_value) as Value', 'YEAR(date_acquired) as Year'])
            ->groupBy('Year');

        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        $result = $query->asArray()->all();

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['Value', 'Year'],
            ],
        ]);
    }

    // Commented this out since it is the same for the amount
    // public static function getPropDispCntPerYearModal($year, $params)
    // {

    //     $office = $params['office'] ?? null;
    //     $query = self::find()
    //         ->select([
    //             'properties.particular AS particular',
    //             'properties.unit_value AS unit_value',
    //             'members_profile.full_name AS full_name',
    //             'properties.current_holder AS current_holder',
    //         ])
    //         ->innerJoin('members_profile', 'properties.possessor = members_profile.membership_id')
    //         ->where(['operability' => 'For disposal'])
    //         ->andWhere(new Expression('YEAR(properties.timestamp) = :year', [':year' => $year]));

    //     if ($office) {
    //         $query->andWhere(['office' => $office]);
    //     }

    //     // Get total count *before* filtering
    //     $totalRecords = $query->count();

    //     // Search
    //     if (!empty($params['search']['value'])) {
    //         $search = $params['search']['value'];
    //         $query->andFilterWhere([
    //             'or',
    //             ['like', 'properties.particular', $search],
    //             ['like', 'properties.unit_value', $search],
    //             ['like', 'members_profile.full_name', $search],
    //             ['like', 'properties.current_holder', $search],
    //         ]);
    //     }

    //     $filteredCount = $query->count(); // Count after filtering

    //     // Pagination
    //     $offset = isset($params['start']) ? (int)$params['start'] : 0;
    //     $limit = isset($params['length']) ? (int)$params['length'] : 10;
    //     $query->offset($offset)->limit($limit);

    //     // Sorting
    //     if (!empty($params['order'][0])) {
    //         $columnIdx = $params['order'][0]['column'];
    //         $columnName = $params['columns'][$columnIdx]['data'];
    //         $dir = $params['order'][0]['dir'] === 'desc' ? SORT_DESC : SORT_ASC;
    //         $query->orderBy([$columnName => $dir]);
    //     }

    //     // Fetch results
    //     $models = $query->asArray()->all();

    //     foreach ($models as &$row) {
    //         $row['Possessor'] = $row['full_name']; // Rename to match frontend
    //         unset($row['full_name']); // Clean up
    //     }

    //     // Return in DataTables format
    //     return [
    //         // 'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
    //         // 'recordsTotal' => $totalRecords,
    //         // 'recordsFiltered' => $filteredCount,
    //         // 'data' => $models,
    //         'draw' => (int)($params['draw'] ?? 1),
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $filteredCount,
    //         'data' => $models,
    //     ];
    // }


    public function getNumberOfItems($params)
    {
        $office = $params['office'] ?? null;

        $sql = self::find()
            ->select(['property_type', 'COUNT(*) as count'])
            ->groupBy('property_type')
            ->orderBy(['count' => SORT_DESC]);

        if ($office) {
            $sql->andWhere(['office' => $office]);
        }

        $result = $sql->asArray()->all();

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['property_type', 'count'],
            ],
        ]);
    }


    // public function forDisposals()
    // {
    //     $sql = "SELECT operability, COUNT(*) as count 
    //             FROM `properties` 
    //             WHERE operability = 'For disposal'";
    //     $result = Yii::$app->db->createCommand($sql)->queryAll();
    //     return new ArrayDataProvider([
    //         'allModels' => $result,
    //         'pagination' => false,
    //         'sort' => [
    //             'attributes' => ['operability', 'count'],
    //         ],
    //     ]);
    // }

    public function forDisposals($params)
    {
        $office = $params['office'] ?? null;

        $sql = "
        SELECT operability, COUNT(*) as count 
        FROM properties
        {where}
        GROUP BY operability
        ";

        $whereClause = 'WHERE operability = "For disposal"';  // Default condition for 'For disposal'
        $paramsSql = [];

        // Adding office filter if it's provided
        if ($office) {
            $whereClause .= ' AND office = :office';
            $paramsSql[':office'] = $office;
        }

        // Replace placeholder with actual WHERE clause
        $sql = str_replace('{where}', $whereClause, $sql);

        $result = Yii::$app->db->createCommand($sql)
            ->bindValues($paramsSql)
            ->queryAll();

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['operability', 'count'],
            ],
        ]);
    }


    // public static function forDisposalsModal($params)
    // {
    //     // Build the query to fetch properties for disposal
    //     $query = self::find()
    //         ->select(['property_no', 'particular', 'date_acquired', 'unit_value', 'current_holder', 'office'])
    //         ->where(['operability' => 'For disposal']);

    //     // Example: Search filter (use $params['search']['value'])
    //     if (!empty($params['search']['value'])) {
    //         $search = $params['search']['value'];
    //         $query->andFilterWhere([
    //             'or',
    //             ['like', 'property_no', $search],
    //             ['like', 'particular', $search],
    //             ['like', 'current_holder', $search],
    //             ['like', 'office', $search]
    //         ]);
    //     }

    //     $totalRecords = $query->count(); // Get total record count for pagination

    //     // Apply pagination
    //     $query->offset($params['start'] ?? 0)
    //         ->limit($params['length'] ?? 10);

    //     // Apply sorting
    //     if (!empty($params['order'][0])) {
    //         $columnIdx = $params['order'][0]['column'];
    //         $columnName = $params['columns'][$columnIdx]['data'];
    //         $dir = $params['order'][0]['dir'];
    //         $query->orderBy([$columnName => ($dir === 'asc') ? SORT_ASC : SORT_DESC]);
    //     }

    //     $models = $query->asArray()->all(); // Get the data

    //     return [
    //         'draw' => intval($params['draw'] ?? 1),
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $totalRecords,
    //         'data' => $models,
    //     ];
    // }

    public static function forDisposalsModal($params)
    {
        // Extract office or any other filtering criteria from $params
        $office = $params['office'] ?? null;
        // echo "<script>console.log('The Office: $office');</script>"; 

        // Start building the query to fetch properties for disposal
        $query = self::find()
            ->select(['property_no', 'particular', 'date_acquired', 'unit_value', 'current_holder', 'mr_date', 'office'])
            ->where(['operability' => 'For disposal']);

        // Apply the office filter if it exists
        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Search filter (optional based on search input in the modal)
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $query->andFilterWhere([
                'or',
                ['like', 'property_no', $search],
                ['like', 'particular', $search],
                ['like', 'date_acquired', $search],
                ['like', 'unit_value', $search],
                ['like', 'current_holder', $search],
                ['like', 'mr_date', $search],
                ['like', 'office', $search]
            ]);
        }

        // Get the total number of records for pagination
        $totalRecords = $query->count();

        // Apply pagination based on start and length parameters from the datatable (or modal view)
        $query->offset($params['start'] ?? 0)
            ->limit($params['length'] ?? 10);

        // Apply sorting based on the column and direction
        if (!empty($params['order'][0])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'];
            $query->orderBy([$columnName => ($dir === 'asc') ? SORT_ASC : SORT_DESC]);
        }

        // Fetch the models from the query
        $models = $query->asArray()->all();

        // Return the result in the required DataTables format
        return [
            'draw' => intval($params['draw'] ?? 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $models,
        ];
    }


    // public function servicables()
    // {
    //     $sql = "SELECT operability, COUNT(*) as count 
    //             FROM `properties` 
    //             WHERE operability = 'Serviceable' 
    //             OR operability = '' 
    //             OR operability IS NULL";
    //     $result = Yii::$app->db->createCommand($sql)->queryAll();
    //     return new ArrayDataProvider([
    //         'allModels' => $result,
    //         'pagination' => false,
    //         'sort' => [
    //             'attributes' => ['operability', 'count'],
    //         ],
    //     ]);
    // }

    public function servicables($params)
    {
        // Extract office or any other filtering criteria from $params
        $office = $params['office'] ?? null;

        // Start building the query to fetch serviceable properties
        $query = self::find()
            ->select(['operability', 'COUNT(*) as count'])
            ->where([
                'or',
                ['operability' => 'Serviceable'],
                ['operability' => ''],
                ['operability' => null]
            ]);

        // Apply the office filter if it exists
        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Execute the query and get the result
        $result = $query->asArray()->all();

        // Return the result in an ArrayDataProvider without pagination
        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,  // Disable pagination as per your requirement
            'sort' => [
                'attributes' => ['operability', 'count'],
            ],
        ]);
    }


    // public static function serviceablesModal($params)
    // {
    //     // Build the query to fetch properties for disposal
    //     $query = self::find()
    //         ->select(['property_no', 'particular', 'date_acquired', 'unit_value', 'current_holder', 'office'])
    //         ->where([
    //             'or',
    //             ['operability' => 'Serviceable'],
    //             ['operability' => ''],
    //             ['operability' => null]
    //         ]);

    //     // Example: Search filter (use $params['search']['value'])
    //     if (!empty($params['search']['value'])) {
    //         $search = $params['search']['value'];
    //         $query->andFilterWhere([
    //             'or',
    //             ['like', 'property_no', $search],
    //             ['like', 'particular', $search],
    //             ['like', 'current_holder', $search],
    //             ['like', 'office', $search]
    //         ]);
    //     }

    //     $totalRecords = $query->count(); // Get total record count for pagination

    //     // Apply pagination
    //     $query->offset($params['start'] ?? 0)
    //         ->limit($params['length'] ?? 10);

    //     // Apply sorting
    //     if (!empty($params['order'][0])) {
    //         $columnIdx = $params['order'][0]['column'];
    //         $columnName = $params['columns'][$columnIdx]['data'];
    //         $dir = $params['order'][0]['dir'];
    //         $query->orderBy([$columnName => ($dir === 'asc') ? SORT_ASC : SORT_DESC]);
    //     }

    //     $models = $query->asArray()->all(); // Get the data

    //     return [
    //         'draw' => intval($params['draw'] ?? 1),
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $totalRecords,
    //         'data' => $models,
    //     ];
    // }

    public static function serviceablesModal($params)
    {
        // Build the query to fetch serviceable properties
        $query = self::find()
            ->select(['property_no', 'particular', 'date_acquired', 'unit_value', 'current_holder', 'mr_date', 'office'])
            ->where([
                'or',
                ['operability' => 'Serviceable'],
                ['operability' => ''],
                ['operability' => null]
            ]);

        // Apply office filter from the dropdown or query parameters
        $office = $params['office'] ?? null;
        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Example: Search filter (use $params['search']['value'])
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $query->andFilterWhere([
                'or',
                ['like', 'property_no', $search],
                ['like', 'particular', $search],
                ['like', 'current_holder', $search],
                ['like', 'mr_date', $search],
                ['like', 'office', $search]
            ]);
        }

        // Get total record count for pagination (before filtering)
        $totalRecords = $query->count();

        // Apply pagination
        $query->offset($params['start'] ?? 0)
            ->limit($params['length'] ?? 10);

        // Apply sorting if provided
        if (!empty($params['order'][0])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'];
            $query->orderBy([$columnName => ($dir === 'asc') ? SORT_ASC : SORT_DESC]);
        }

        // Execute the query and get the data
        $models = $query->asArray()->all();

        // Return data in the format needed for DataTables (with pagination support)
        return [
            'draw' => intval($params['draw'] ?? 1),           // The draw counter for DataTables
            'recordsTotal' => $totalRecords,                  // Total records before filtering
            'recordsFiltered' => $totalRecords,               // Total records after filtering
            'data' => $models,                                // The filtered data
        ];
    }


    // public function holdersCount()
    // {
    //     $query = "SELECT count(DISTINCT(`current_holder`)) as count
    //             FROM `properties`";
    //     $result = Yii::$app->db->createCommand($query)->queryAll();
    //     return new ArrayDataProvider([
    //         'allModels' => $result,
    //         'pagination' => false,
    //     ]);
    // }

    public function holdersCount($params)
    {
        // Extract office or any other filtering criteria from $params
        $office = $params['office'] ?? null;

        // Start building the query to count distinct current holders
        $query = self::find()
            ->select(['count(DISTINCT(current_holder)) as count'])
            ->from('properties');

        // // Check if the 'office' filter is passed and apply it to the query
        // if (!empty($params['filter']['office'])) {
        //     // Only fetch data for the selected office
        //     $query->andWhere(['office' => $params['filter']['office']]);
        // }

        if ($office) {
            $query->where(['office' => $office]);
        }

        // Execute the query and get the result
        $result = $query->asArray()->one();  // Use `one()` since you are counting

        // Return the result in an ArrayDataProvider without pagination
        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
        ]);
    }


    // public function getTotalUnitValue()
    // {
    //     $sql = "SELECT CONCAT('₱', FORMAT(SUM(unit_value), 2)) AS total_unit_value
    //             FROM `properties`";
    //     $result = Yii::$app->db->createCommand($sql)->queryAll();
    //     return new ArrayDataProvider([
    //         'allModels' => $result,
    //         'pagination' => false,
    //     ]);
    // }

    public function getTotalUnitValue($params)
    {
        // Extract office filter from params
        $office = $params['office'] ?? null;

        // Build the query using ActiveRecord
        $query = self::find()
            ->select(["CONCAT('₱', FORMAT(SUM(unit_value), 2)) AS total_unit_value"])
            ->from('properties');

        // Apply office filter if provided
        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Execute the query
        $result = $query->asArray()->all();

        // Return the result in an ArrayDataProvider without pagination
        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
        ]);
    }

    // public function getTotalUnitValuePerOffice()
    // {
    //     $sql = "SELECT `property_type` as 'Property Type', SUM(`unit_value`) as 'Total Unit Value'
    //             FROM `properties`
    //             GROUP BY `property_type`
    //             ORDER BY `Total Unit Value` DESC";
    //     $result = Yii::$app->db->createCommand($sql)->queryAll();
    //     return new ArrayDataProvider([
    //         'allModels' => $result,
    //         'pagination' => false,
    //         'sort' => [
    //             'attributes' => ['Property Type', 'Total Unit Value'],
    //         ],
    //     ]);
    // }

    public function getTotalUnitValuePerOffice($params)
    {
        $office = $params['office'] ?? null;

        // Use proper SQL aliasing format for ActiveRecord
        $query = self::find()
            ->select([
                'property_type AS `Property Type`',
                'SUM(unit_value) AS `Total Unit Value`'
            ])
            ->from('properties');

        // Apply office filter
        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Group and sort
        $query->groupBy('property_type')
            ->orderBy(['Total Unit Value' => SORT_DESC]);

        // Execute and get all results (multiple rows expected)
        $result = $query->asArray()->all();

        // Return as ArrayDataProvider
        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['Property Type', 'Total Unit Value'],
            ],
        ]);
    }


    // public function getServiceableFYear()
    // {
    //     $sql = <<<SQL
    //             SELECT `property_type`, COUNT(*) AS Count
    //             FROM `properties`
    //             WHERE (
    //                 operability = 'Serviceable'
    //                 OR operability = ''
    //                 OR operability IS NULL
    //             )
    //             AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)
    //             GROUP BY `property_type`
    //             ORDER BY Count DESC
    //             SQL;

    //     $result = Yii::$app->db->createCommand($sql)->queryAll();
    //     return new ArrayDataProvider([
    //         'allModels' => $result,
    //         'pagination' => false,
    //         'sort' => [
    //             'attributes' => ['property_type', 'Count'],
    //         ],
    //     ]);
    // }


    public function getServiceableFYear($params)
    {
        // Extract office filter from params
        $office = $params['office'] ?? null;

        // Build the query using ActiveRecord
        $query = self::find()
            ->select(['property_type', 'COUNT(*) AS Count'])
            ->where([
                'or',
                ['operability' => 'Serviceable'],
                ['operability' => ''],
                ['operability' => null],
            ])
            ->andWhere(['<=', 'mr_date', new \yii\db\Expression('DATE_SUB(CURDATE(), INTERVAL 5 YEAR)')])
            ->groupBy('property_type')
            ->orderBy(['Count' => SORT_DESC]);

        // Apply office filter if provided
        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Execute the query
        $result = $query->asArray()->all();

        // Return the result in an ArrayDataProvider without pagination
        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
            'sort' => [
                'attributes' => ['property_type', 'Count'],
            ],
        ]);
    }



    // Modals (not updated) ---------------------------------------------------------------------------------------
    // public static function getHoldersModal($params)
    // {
    //     $query = self::find()
    //         ->select(['date_acquired', 'particular AS item', 'property_type', 'current_holder'])
    //         ->from('properties');

    //     if (!empty($params['search']['value'])) {
    //         $search = $params['search']['value'];
    //         $query->andFilterWhere([
    //             'or',
    //             ['like', 'date_acquired', $search],
    //             ['like', 'particular', $search],
    //             ['like', 'property_type', $search],
    //             ['like', 'current_holder', $search]
    //         ]);
    //     }
    //     $totalRecords = $query->count(); // Get total record count for pagination

    //     // Apply pagination
    //     $query->offset($params['start'] ?? 0)
    //         ->limit($params['length'] ?? 10);

    //     // Apply sorting
    //     if (!empty($params['order'][0])) {
    //         $columnIdx = $params['order'][0]['column'];
    //         $columnName = $params['columns'][$columnIdx]['data'];
    //         $dir = $params['order'][0]['dir'];
    //         $query->orderBy([$columnName => ($dir === 'asc') ? SORT_ASC : SORT_DESC]);
    //     }
    //     $models = $query->asArray()->all(); // Get the data
    //     return [
    //         'draw' => intval($params['draw'] ?? 1),
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $totalRecords,
    //         'data' => $models,
    //     ];
    // }

    public static function getHoldersModal($params)
    {
        $office = $params['office'] ?? null;
        // Start the query to fetch properties with relevant columns
        $query = self::find()
            ->select(['current_holder'])
            ->distinct();


        if ($office) {
            $query->where(['office' => $office]);
        }

        // Apply search filter if a search value is provided
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $query->andFilterWhere([
                'or',
                ['like', 'current_holder', $search],
            ]);
        }

        // Get total record count before applying pagination (for DataTables)
        $totalRecords = $query->count();

        // Apply pagination (start and length) for DataTables
        $query->offset($params['start'] ?? 0)
            ->limit($params['length'] ?? 10);

        // Apply sorting if sorting parameters are provided
        if (!empty($params['order'][0])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'];
            $query->orderBy([$columnName => ($dir === 'asc') ? SORT_ASC : SORT_DESC]);
        }

        // Fetch the data
        $models = $query->asArray()->all();

        // Return the data in a DataTables-compatible format
        return [
            'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,  // Assuming totalRecords is same for filtered data
            'data' => $models,
        ];
    }

    public static function getByCurrentHolder($params)
    {
        $currentHolder = $params['current_holder'] ?? null;

        $query = self::find()
            ->where(['current_holder' => $currentHolder]);

        $totalRecords = $query->count();

        $query->offset($params['start'] ?? 0)
            ->limit($params['length'] ?? 10);

        if (!empty($params['order'][0])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'];
            $query->orderBy([$columnName => ($dir === 'asc') ? SORT_ASC : SORT_DESC]);
        }

        return [
            'draw' => intval($params['draw'] ?? 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $query->asArray()->all(),
        ];
    }



    // public static function getPropertyTypeInfo($propertyType, $params)
    // {
    //     $query = self::find()
    //         ->select(['particular', 'date_acquired', 'unit_value', 'current_holder'])
    //         ->where(['property_type' => $propertyType]);

    //     // Get total count *before* filtering
    //     $totalRecords = $query->count();

    //     // Search
    //     if (!empty($params['search']['value'])) {
    //         $search = $params['search']['value'];
    //         $query->andFilterWhere([
    //             'or',
    //             ['like', 'particular', $search],
    //             ['like', 'date_acquired', $search],
    //             ['like', 'unit_value', $search],
    //             ['like', 'current_holder', $search],
    //         ]);
    //     }

    //     $filteredCount = $query->count(); // Count after filtering

    //     // Pagination
    //     $offset = isset($params['start']) ? (int)$params['start'] : 0;
    //     $limit = isset($params['length']) ? (int)$params['length'] : 10;
    //     $query->offset($offset)->limit($limit);

    //     // Sorting
    //     if (!empty($params['order'][0])) {
    //         $columnIdx = $params['order'][0]['column'];
    //         $columnName = $params['columns'][$columnIdx]['data'];
    //         $dir = $params['order'][0]['dir'] === 'desc' ? SORT_DESC : SORT_ASC;
    //         $query->orderBy([$columnName => $dir]);
    //     }

    //     // Fetch results
    //     $models = $query->asArray()->all();
    //     // self::debug_to_console($models);
    //     // Return in DataTables format
    //     return [
    //         'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $filteredCount,
    //         'data' => $models,
    //     ];
    // }

    public static function getPropertyTypeInfo($propertyType, $params)
    {
        $office = $params['office'] ?? null;

        $query = self::find()
            ->select(['particular', 'date_acquired', 'unit_value', 'current_holder', 'mr_date'])
            ->where(['property_type' => $propertyType]);

        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Get total count *before* filtering
        $totalRecords = $query->count();

        // Search
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $query->andFilterWhere([
                'or',
                ['like', 'particular', $search],
                ['like', 'date_acquired', $search],
                ['like', 'unit_value', $search],
                ['like', 'current_holder', $search],
                ['like', 'mr_date', $search],
            ]);
        }

        $filteredCount = $query->count(); // Count after filtering

        // Pagination
        $offset = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['length']) ? (int)$params['length'] : 10;
        $query->offset($offset)->limit($limit);

        // Sorting
        if (!empty($params['order'][0])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'] === 'desc' ? SORT_DESC : SORT_ASC;
            $query->orderBy([$columnName => $dir]);
        }

        // Fetch results
        $models = $query->asArray()->all();
        // self::debug_to_console($models);
        // Return in DataTables format
        return [
            'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredCount,
            'data' => $models,
        ];
    }

    // public static function getAcquiredOnYear($year, $params)
    // {
    //     $query = self::find()
    //         // ->select(['particular', 'date_acquired', 'unit_value', 'current_holder'])
    //         ->select(['particular', 'unit_value', 'current_holder'])
    //         ->andWhere(new \yii\db\Expression('YEAR(date_acquired) = :year', [':year' => $year]));

    //     // Get total count *before* filtering
    //     $totalRecords = $query->count();

    //     // Search
    //     if (!empty($params['search']['value'])) {
    //         $search = $params['search']['value'];
    //         $query->andFilterWhere([
    //             'or',
    //             ['like', 'particular', $search],
    //             // ['like', 'date_acquired', $search],
    //             ['like', 'unit_value', $search],
    //             ['like', 'current_holder', $search],
    //         ]);
    //     }

    //     $filteredCount = $query->count(); // Count after filtering

    //     // Pagination
    //     $offset = isset($params['start']) ? (int)$params['start'] : 0;
    //     $limit = isset($params['length']) ? (int)$params['length'] : 10;
    //     $query->offset($offset)->limit($limit);

    //     // Sorting
    //     if (!empty($params['order'][0])) {
    //         $columnIdx = $params['order'][0]['column'];
    //         $columnName = $params['columns'][$columnIdx]['data'];
    //         $dir = $params['order'][0]['dir'] === 'desc' ? SORT_DESC : SORT_ASC;
    //         $query->orderBy([$columnName => $dir]);
    //     }

    //     // Fetch results
    //     $models = $query->asArray()->all();

    //     // Return in DataTables format
    //     return [
    //         'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $filteredCount,
    //         'data' => $models,
    //     ];
    // }
    public static function getAcquiredOnYear($year, $params)
    {

        $office = $params['office'] ?? null;

        $query = self::find()
            // ->select(['particular', 'date_acquired', 'unit_value', 'current_holder'])
            ->select(['particular', 'unit_value', 'current_holder', 'mr_date'])
            ->where(['YEAR(date_acquired)' => $year]);

        // Check if the 'office' filter is passed and apply it to the query
        if ($office) {
            // Only fetch data for the selected office
            $query->andWhere(['office' => $office]);
        }

        // Get total count *before* filtering
        $totalRecords = $query->count();

        // Search
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $query->andFilterWhere([
                'or',
                ['like', 'particular', $search],
                // ['like', 'date_acquired', $search],
                ['like', 'unit_value', $search],
                ['like', 'current_holder', $search],
                ['like', 'mr_date', $search],
            ]);
        }

        $filteredCount = $query->count(); // Count after filtering

        // Pagination
        $offset = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['length']) ? (int)$params['length'] : 10;
        $query->offset($offset)->limit($limit);

        // Sorting
        if (!empty($params['order'][0])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'] === 'desc' ? SORT_DESC : SORT_ASC;
            $query->orderBy([$columnName => $dir]);
        }

        // Fetch results
        $models = $query->asArray()->all();

        // Return in DataTables format
        return [
            'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredCount,
            'data' => $models,
        ];
    }


    // public static function getPropertyServiceables($propertyType, $params)
    // {

    //     $query = self::find()
    //         // ->select(['particular', 'date_acquired', 'unit_value', 'current_holder'])
    //         ->select(['particular', 'unit_value', 'current_holder', 'date_acquired'])
    //         ->where(new \yii\db\Expression('`timestamp` >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)'))
    //         ->andWhere(new \yii\db\Expression('operability = "Serviceable" OR operability = "" OR operability IS NULL'))
    //         ->andWhere(new \yii\db\Expression('property_type = :propertyType', [':propertyType' => $propertyType]));

    //     // Get total count *before* filtering
    //     $totalRecords = $query->count();

    //     // Search
    //     if (!empty($params['search']['value'])) {
    //         $search = $params['search']['value'];
    //         $query->andFilterWhere([
    //             'or',
    //             ['like', 'particular', $search],
    //             // ['like', 'date_acquired', $search],
    //             ['like', 'unit_value', $search],
    //             ['like', 'current_holder', $search],
    //             ['like', 'date_acquired', $search],
    //         ]);
    //     }

    //     $filteredCount = $query->count(); // Count after filtering

    //     // Pagination
    //     $offset = isset($params['start']) ? (int)$params['start'] : 0;
    //     $limit = isset($params['length']) ? (int)$params['length'] : 10;
    //     $query->offset($offset)->limit($limit);

    //     // Sorting
    //     if (!empty($params['order'][0])) {
    //         $columnIdx = $params['order'][0]['column'];
    //         $columnName = $params['columns'][$columnIdx]['data'];
    //         $dir = $params['order'][0]['dir'] === 'desc' ? SORT_DESC : SORT_ASC;
    //         $query->orderBy([$columnName => $dir]);
    //     }

    //     // Fetch results
    //     $models = $query->asArray()->all();

    //     // Return in DataTables format
    //     return [
    //         'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $filteredCount,
    //         'data' => $models,
    //     ];
    // }

    // public static function getProp
    public static function getPropertyServiceables($propertyType, $params)
    {

        $office = $params['office'] ?? null;

        // $query = self::find()
        //     // ->select(['particular', 'date_acquired', 'unit_value', 'current_holder'])
        //     ->select(['particular', 'unit_value', 'current_holder', 'date_acquired'])
        //     ->where(new \yii\db\Expression('`timestamp` >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)'))
        //     ->andWhere(new \yii\db\Expression('operability = "Serviceable" OR operability = "" OR operability IS NULL'))
        //     ->andWhere(new \yii\db\Expression('property_type = :propertyType', [':propertyType' => $propertyType]));

        $query = self::find()
            // ->select(['property_type', 'COUNT(*) AS Count'])
            ->select(['particular', 'unit_value', 'current_holder', 'mr_date', 'date_acquired'])
            ->where([
                'or',
                ['operability' => 'Serviceable'],
                ['operability' => ''],
                ['operability' => null],
            ])
            ->andWhere(['<=', 'mr_date', new \yii\db\Expression('DATE_SUB(CURDATE(), INTERVAL 5 YEAR)')])
            ->andWhere(['property_type' => $propertyType]);

        if ($office) {
            $query->andWhere(['office' => $office]);
        }

        // Get total count *before* filtering
        $totalRecords = $query->count();

        // Search
        if (!empty($params['search']['value'])) {
            $search = $params['search']['value'];
            $query->andFilterWhere([
                'or',
                ['like', 'particular', $search],
                // ['like', 'date_acquired', $search],
                ['like', 'unit_value', $search],
                ['like', 'current_holder', $search],
                ['like', 'mr_date', $search],
                ['like', 'date_acquired', $search],
            ]);
        }

        $filteredCount = $query->count(); // Count after filtering

        // Pagination
        $offset = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['length']) ? (int)$params['length'] : 10;
        $query->offset($offset)->limit($limit);

        // Sorting
        if (!empty($params['order'][0])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'] === 'desc' ? SORT_DESC : SORT_ASC;
            $query->orderBy([$columnName => $dir]);
        }

        // Fetch results
        $models = $query->asArray()->all();

        // Return in DataTables format
        return [
            'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredCount,
            'data' => $models,
        ];
    }

    // Admin and Finance Division
    // Programs and Projects Management Division
    // Engineering Plans, Designs and Specifications Division
    // Office of the Director
    // Standards Regulation and Enforcement Division
    // General Service Section
    // Cashier and Disbursement Section
    // Office of the Assistant Director
    // Records Section
    // Human Resource Section
    // Planning Section
    // Accounting Section
    // Information Section
    // Procurement Section
    // Budget Section
    // Special Engineering Programs and Projects Division
    // Planning Knowledge Management and Digitalization Division

    public function getData($queryParams)
    {
        return [
            'lineGraphProvider' => $this->acqrdPerYear($queryParams),
            'barGraphProvider' => $this->getNumberOfItems($queryParams),
            'forDispProvider' => $this->forDisposals($queryParams),
            'serviceableProvider' => $this->servicables($queryParams),
            'holdersCountProvider' => $this->holdersCount($queryParams),
            'totalUnitValueProvider' => $this->getTotalUnitValue($queryParams),
            'getTotalUnitValuePerOfficeProvider' => $this->getTotalUnitValuePerOffice($queryParams),
            'getServiceableFYearProvider' => $this->getServiceableFYear($queryParams),
            'getDropOptionsProvider' => $this->dropOptions($queryParams),
            'getPropDispAmntPerYear' => $this->getPropDispAmntPerYear($queryParams),
            'getPropDispCntPerYear' => $this->getPropDispCntPerYear($queryParams),
            'getPropAmountPerYearProvider' => $this->getPropAmountPerYear($queryParams),
        ];
    }
}
