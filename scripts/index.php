<?php
//index repositories in elasticsearch

namespace phorkie;
set_include_path(
    __DIR__ . '/../src/'
    . PATH_SEPARATOR . get_include_path()
);
spl_autoload_register(
    function ($class) {
        $file = str_replace(array('\\', '_'), '/', $class) . '.php';
        $hdl = @fopen($file, 'r', true);
        if ($hdl !== false) {
            fclose($hdl);
            require $file;
        }
    }
);
require_once __DIR__ . '/../data/config.default.php';
if (file_exists(__DIR__ . '/../data/config.php')) {
    require_once __DIR__ . '/../data/config.php';
}
if ($GLOBALS['phorkie']['cfg']['setupcheck']) {
    SetupCheck::run();
}

//delete all repos
$r = new \HTTP_Request2(
    $GLOBALS['phorkie']['cfg']['elasticsearch'] . 'repo/_query',
    \HTTP_Request2::METHOD_DELETE
);
$r->setBody(
    json_encode(
        (object)array(
            'match_all' => (object)array()
        )
    )
);
$r->send();
$r = new \HTTP_Request2(
    $GLOBALS['phorkie']['cfg']['elasticsearch'] . 'file/_query',
    \HTTP_Request2::METHOD_DELETE
);
$r->setBody(
    json_encode(
        (object)array(
            'match_all' => (object)array()
        )
    )
);
$r->send();

//create mapping
$r = new \HTTP_Request2(
    $GLOBALS['phorkie']['cfg']['elasticsearch'] . 'file/_mapping',
    \HTTP_Request2::METHOD_PUT
);
$r->setBody(
    json_encode(
        (object)array(
            'file' => (object)array(
                '_parent' => (object)array(
                    'type' => 'repo'
                )
            )
        )
    )
);
$r->send();



//FIXME: define schema
$rs = new Repositories();
foreach ($rs->getList(0, 10000) as $repo) {
    echo 'Indexing ' . $repo->id . "\n";
    $r = new \HTTP_Request2(
        $GLOBALS['phorkie']['cfg']['elasticsearch'] . 'repo/' . $repo->id,
        \HTTP_Request2::METHOD_PUT
    );
    $r->setBody(
        json_encode(
            (object)array(
                'id' => $repo->id,
                'description' => $repo->getDescription(),
            )
        )
    );
    $res = $r->send();

    foreach ($repo->getFiles() as $file) {
        $r = new \HTTP_Request2(
            $GLOBALS['phorkie']['cfg']['elasticsearch'] . 'file/?parent=' . $repo->id,
            \HTTP_Request2::METHOD_POST
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'name'      => $file->getFilename(),
                    'extension' => $file->getExt(),
                    'content'   => $file->isText() ? $file->getContent() : '',
                )
            )
        );
        $r->send();
    }
}
?>
