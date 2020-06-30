<?php
/**
 * PedidosMesChartView
 * @author  <your name here>
 */
class DashboardView extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        
        $div = new TElement('div');
        $div->add( $a=new PedidosMesChartView(false) );
        $div->add( $b=new PedidosEstadoChartView(false) );
        
        $a->style = 'width:50%;float:left;padding:10px';
        $b->style = 'width:50%;float:left;padding:10px';
        
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($div);
        
        parent::add($vbox);
    }
}
