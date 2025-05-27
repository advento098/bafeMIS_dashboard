<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use app\models\Properties;
use Yii;

/**
 * PropertiesSearch represents the model behind the search form of `app\models\Properties`.
 */
class PropertiesSearch extends Properties
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'possessor'], 'integer'],
            [['timestamp', 'doc_no', 'property_type', 'property_no', 'particular', 'date_acquired', 'mr_date', 'current_holder', 'office', 'operability', 'remarks'], 'safe'],
            [['unit_value'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Properties::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'date_acquired' => $this->date_acquired,
            'unit_value' => $this->unit_value,
            'possessor' => $this->possessor,
            'mr_date' => $this->mr_date,
        ]);

        $query->andFilterWhere(['like', 'doc_no', $this->doc_no])
            ->andFilterWhere(['like', 'property_type', $this->property_type])
            ->andFilterWhere(['like', 'property_no', $this->property_no])
            ->andFilterWhere(['like', 'particular', $this->particular])
            ->andFilterWhere(['like', 'current_holder', $this->current_holder])
            ->andFilterWhere(['like', 'office', $this->office])
            ->andFilterWhere(['like', 'operability', $this->operability])
            ->andFilterWhere(['like', 'remarks', $this->remarks]);

        return $dataProvider;
    }
}
