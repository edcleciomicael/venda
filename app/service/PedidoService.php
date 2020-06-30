<?php
class PedidoService extends AdiantiRecordService
{
    const DATABASE = 'microerp';
    const ACTIVE_RECORD = 'Pedido';
    
    public function getTotalPedidosPessoa($param)
    {
        TTransaction::open('microerp');
        
        $cliente_id = $param['cliente_id'];
        $ano        = $param['ano'];
        
        $total = Pedido::getTotalPedidosPessoa($cliente_id, $ano);
        
        TTransaction::close();
        return $total;
    }
}