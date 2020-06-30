<?php
/**
 * PedidosEstadoChartView
 * @author  <your name here>
 */
class PedidosEstadoChartView extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct( $show_breadcrumb )
    {
        parent::__construct();
        
        try
        {
            $html = new THtmlRenderer('app/resources/google_pie_chart.html');
            
            $data = array();
            $data[] = [ 'Estado', 'Valor' ];
            
            TTransaction::open('microerp');
            $pedidos = Pedido::getPedidosEstado( date('Y') );
            TTransaction::close();
            
            foreach ($pedidos as $estado => $valor)
            {
                $data[] = [ $estado, (float) $valor ];
            }
            
            $panel = new TPanelGroup('Pedidos / estado - ' . date('Y'));
            $panel->style = 'width:100%';
            $panel->add($html);
            
            // replace the main section variables
            $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'title'  => 'Pedidos por estado',
                                               'ytitle' => 'Pedidos',
                                               'xtitle' => 'Estado'));
            $container = new TVBox;
            $container->style = 'width: 100%';
            if ($show_breadcrumb)
            {
                $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            }
            $container->add($panel);
            parent::add($container);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
