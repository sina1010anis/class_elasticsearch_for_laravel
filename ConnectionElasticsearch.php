<?php
namespace App\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;

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

    public function connctionIndex(array $data)
    {

        //Save to elasticsearch

        // $data exampel =>
            // $params = [
            //     'body' => [
            //         'title' => 'this is Elasticsearch',
            //         'body' => 'this is Elasticsearch Body In page',
            //         'view' => 56,
            //     ],
            //     'index' => 'my_test',
            //     'type' => 'my_test',
            // ];
        return $this->connctionBuild()->index($data);

    }

    public function connectionWhere(array $wheres = [], string $index = '_all')
    {
        $push_where = [];
        foreach ($wheres as $where) {
            $push_where['match'] = $where;
        }
        $this->where_data = ($where == []) ? ['index' => $index] : [
            'index' => $index,
            'body' => [
                'query' => $push_where
            ]
        ];
        return $this;
    }
    public function connctionSearch(bool $show_only_data = false)
    {

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

        $res = $this->connctionBuild()->search($this->where_data);
        if ($show_only_data) {
            foreach ($res['hits']['hits'] as $source) {
                $data_only[] = $source['_source'];
            }
            return $data_only;
        }
        return $res;

    }

}
