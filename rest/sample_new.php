<?php
require_once 'post.php';

try
{
    $location = 'http://localhost/git/microerp/rest.php';
    $parameters = array();
    $parameters['class'] = 'ProdutoService';
    $parameters['method'] = 'store';
    $parameters['data'] = [ 'tipo_produto_id' => 1,
                            'fornecedor_id'   => 1,
                            'nome'            => 'Teste',
                            'codigo_barras'   => '123',
                            'dt_cadastro'     => '2017-10-05',
                            'preco_custo'     => 1,
                            'preco_venda'     => 2,
                            'qtde_estoque'    => 3 ];
    
    post($location, $parameters);
}
catch (Exception $e)
{
    echo 'Error: '. $e->getMessage();
}
