<?php
require_once 'post.php';

try
{
    $location = 'http://localhost/git/microerp/rest.php';
    $parameters = array();
    $parameters['class'] = 'ProdutoService';
    $parameters['method'] = 'store';
    $parameters['data'] = ['id' => 3, 'obs' => 'alterado.'];
    post($location, $parameters);
}
catch (Exception $e)
{
    echo 'Error: '. $e->getMessage();
}
