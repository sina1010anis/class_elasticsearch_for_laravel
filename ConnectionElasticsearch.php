<?php
namespace App\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;

class ConnectionElasticsearch
{
    protected $connetion_data;

    public function __construct()
    {

        $this->connetion_data = ClientBuilder::create()
            ->setBasicAuthentication(env('ELASTICSEARCH_USERNAME'), env('ELASTICSEARCH_PASSWORD'));

    }

    public function connction()
    {

        return $this->connetion_data;

    }

    public function connctionBuild()
    {

        return $this->connetion_data->build();

    }

    public function connctionVersion()
    {

        return $this->connctionBuild()->info()['version']['number'];

    }

    public function connctionIndex(array $data)
    {

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

    public function connctionSearch(array $data, bool $show_only_data = false)
    {

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

        $res = $this->connctionBuild()->search($data);
        if ($show_only_data) {
            return $res['hits']['hits'];
        }
        return $res;

    }

}
