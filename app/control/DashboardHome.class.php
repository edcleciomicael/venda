<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

/**
 * DashboardHome
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class DashboardHome extends TPage
{
    public function __construct()
    {
        parent::__construct();
        //parent::add(new TLabel('Common page'));

        $iframe = new TElement('iframe');
        $iframe->id = 'inframe_extenal'; 
        $iframe->src = 'app/resources/dash/index.html'; 
        $iframe->frameborder = '0'; 
        $iframe->scrolling = 'no'; 
        $iframe->width = '100%';
        $iframe->height = '2500px';

        parent::add($iframe); 
    }
}
