<?php
//search

namespace phorkie;
require_once __DIR__ . '/../src/phorkie/autoload.php';
require_once __DIR__ . '/../data/config.default.php';
if (file_exists(__DIR__ . '/../data/config.php')) {
    require_once __DIR__ . '/../data/config.php';
}
if ($GLOBALS['phorkie']['cfg']['setupcheck']) {
    SetupCheck::run();
}

$r = new Database_Adapter_Elasticsearch_HTTPRequest(
    $GLOBALS['phorkie']['cfg']['elasticsearch'] . 'repo/_search',
    \HTTP_Request2::METHOD_GET
);
$r->setBody(
    json_encode(
        (object)array(
            'from' => 0,
            'size' => 2,
            'query' => (object)array(
                'bool' => (object)array(
                    'should' => array(
                        (object)array(
                            'query_string' => (object)array(
                                'query' => 'test'
                            ),
                        ),
                        (object)array(
                            'has_child' => (object)array(
                                'type'         => 'file',
                                'query' => (object)array(
                                    'query_string' => (object)array(
                                        'query' => 'test'
                                    )
                                )
                            )
                        )
                    )
                ),
            )
        )
    )
);
$res = $r->send();
echo $res->getBody() . "\n";
?>
