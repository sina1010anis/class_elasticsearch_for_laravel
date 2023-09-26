<?php
namespace App\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;

class ConnectionElasticsearch
{
    protected $connetion_data;
    protected $where_data;


    public function __construct()
    {

        // Connect to elasticsearch using two variables ELASTICSEARCH_USERNAME and ELASTICSEARCH_PASSWORD
        $this->connetion_data = ClientBuilder::create()
            ->setBasicAuthentication(env('ELASTICSEARCH_USERNAME'), env('ELASTICSEARCH_PASSWORD'));

    }

    public function connction()
    {
        // Check the connection
        return $this->connetion_data;

    }

    public function connctionBuild()
    {

        return $this->connetion_data->build();

    }

    public function connctionVersion()
    {

        // Check the version
        return $this->connctionBuild()->info()['version']['number'];

    }

    public function forDatas(array $datas, string $keyArray): array
    {
        $push = [];
        foreach ($datas as $data) {
            $push[$keyArray] = $data;
        }
        return $push;
    }
    public function connctionIndex(string $index, array $datas = [], string|null $type = null)
    {
        $type = ($type == null) ? $index : $type;
        //Save to elasticsearch
        // $data exampel =>
        $params = [
            'body' => $datas,
            'index' => $index,
            'type' => $type,
        ];
        return $this->connctionBuild()->index($params);

    }

    public function connectionWhere(array $wheres = [], string $index = '_all')
    {
        // $wheres = [['brand' => 'BMW'], ['model' => '730i']]
        $this->where_data = ($wheres == []) ? ['index' => $index] : [
            'index' => $index,
            'body' => [
                'query' => $this->forDatas($wheres, 'match')
            ]
        ];
        return $this;
    }
    public function connctionSearch(bool $show_only_data = false, bool $show_shard = false)
    {
        // If show_only_data is false, it will display the data in raw form
        // If show_shard is true, it will display the data from the _source layer

        //Select to elasticsearch
        $data_only = [];
        // $data exampel =>
            // $params = [
            //     'index' => 'my_test',
            //     'body'  => [
            //         'query' => [
            //             'match' => [
            //                 'view' => 56
            //             ]
            //         ]
            //     ]
            // ];
        try {
            $res = $this->connctionBuild()->search($this->where_data);
            if ($show_only_data) {
                if ($show_shard) {
                    foreach ($res['hits']['hits'] as $source) {
                        $data_only[] = $source['_source'];
                    }
                }
                return ($show_shard) ? $data_only : $res['hits']['hits'];
            }
        } catch (ClientResponseException $e) {
            return 'Error: '. $e->getMessage();
        }
        return $res;

    }

    public function connectionDelete(string $index, $id)
    {
        try {
            $delete = $this->connctionBuild()->delete([
                'index' => $index,
                'id' => $id
            ]);
            if ($delete) {
                return true;
            }
            return 'id: ('.$id.') '.'not found...!';
        } catch (ClientResponseException $e) {
            return $e->getMessage();
        }

    }

    public function connectionGet(string $index, int|null $id = null)
    {
        return ($id != null) ? $this->connctionBuild()->get(['index' => $index, 'id' => $id]) : $this->connctionBuild()->indices()->getmapping(['index' => $index]) ;
    }

    public function connectionUpdate(string $index, $id, array $new_datas)
    {
        try {
            $params = ['index' => $index,'id' => $id,'body'  => ['doc' => $new_datas]];

            // Update doc at /my_index/_doc/my_id
            $this->connctionBuild()->update($params);
        } catch (ClientResponseException $e) {
            return $e->getMessage();
        }
    }
}
