<?php
require_once 'post.php';

try
{
    $location = 'http://localhost/git/microerp/rest.php';
    $parameters = array();
    $parameters['class']      = 'PedidoService';
    $parameters['method']     = 'getTotalPedidosPessoa';
    $parameters['cliente_id'] = '1';
    $parameters['ano']        = '2017';
    
    var_dump(post($location, $parameters));
}
catch (Exception $e)
{
    echo 'Error: '. $e->getMessage();
}
