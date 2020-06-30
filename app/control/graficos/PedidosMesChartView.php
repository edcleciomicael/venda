<?php
/**
 * PedidosMesChartView
 * @author  <your name here>
 */
class PedidosMesChartView extends TPage
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
            $html = new THtmlRenderer('app/resources/google_bar_chart.html');
            
            $meses = [ 1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',     4 => 'Abril',    5 => 'Maio',      6 => 'Junho',
                       7 => 'Julho',   8 => 'Agosto',    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro' ];
            
            $data = array();
            $data[] = [ 'Mês', 'Valor' ];
            
            TTransaction::open('microerp');
            $pedidos_mes = Pedido::getPedidosMes( date('Y') );
            TTransaction::close();
            
            foreach ($pedidos_mes as $mes => $valor)
            {
                $data[] = [ $meses[ (int)$mes], $valor ];
            }
            
            $panel = new TPanelGroup('Pedidos / mês - ' . date('Y'));
            $panel->style = 'width:100%';
            $panel->add($html);
            
            // replace the main section variables
            $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'title'  => 'Pedidos por mês',
                                               'ytitle' => 'Pedidos',
                                               'xtitle' => 'Mês'));
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
