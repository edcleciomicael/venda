<?php
require_once 'post.php';

try
{
    $location = 'http://localhost/git/microerp/rest.php';
    $parameters = array();
    $parameters['class']  = 'ProdutoService';
    $parameters['method'] = 'delete';
    $parameters['id']     = 3;
    post($location, $parameters);
}
catch (Exception $e)
{
    echo 'Error: '. $e->getMessage();
}
