<?php
require_once 'post.php';

try
{
    $location = 'http://localhost/git/microerp/rest.php';
    $parameters = array();
    $parameters['class']   = 'ProdutoService';
    $parameters['method']  = 'loadAll';
    $parameters['filters'] = [ ['id', '>', '1'],
                               [ 'dt_cadastro', '=', '2017-10-05'] ];
    var_dump(post($location, $parameters));
}
catch (Exception $e)
{
    echo 'Error: '. $e->getMessage();
}
