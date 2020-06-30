<?php
/**
 * PedidoList Listing
 * @author  <your name here>
 */
class PedidoList extends TStandardList
{
    protected $form; // form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        parent::setDatabase('venda');
        parent::setActiveRecord('Pedido');
        parent::addFilterField('id', '=', 'id');
        parent::addFilterField('cliente_id', '=', 'cliente_id');
        parent::addFilterField('dt_pedido', '>=', 'dt_pedido_ini');
        parent::addFilterField('dt_pedido', '<=', 'dt_pedido_ate');
        parent::setDefaultOrder('id', 'desc');
        
        $this->form = new BootstrapFormBuilder('form_Pedido');
        $this->form->setFormTitle('Pedidos');
        
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', 'venda', 'Pessoa', 'id', 'nome','nome asc'  );
        
        $dt_pedido_ini = new TDate('dt_pedido_ini');
        $dt_pedido_ate = new TDate('dt_pedido_ate');

        $cliente_id->setMinLength(2);
        $dt_pedido_ini->setDatabaseMask('yyyy-mm-dd');
        $dt_pedido_ate->setDatabaseMask('yyyy-mm-dd');
        $cliente_id->setMask('{nome}');
        $dt_pedido_ini->setMask('dd/mm/yyyy');
        $dt_pedido_ate->setMask('dd/mm/yyyy');
        
        $id->setSize(100);
        $cliente_id->setSize('70%');
        $dt_pedido_ini->setSize(130);
        $dt_pedido_ate->setSize(140);
       //$estado_pedido_id->setSize('70%');

        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Cliente:')],[$cliente_id]);
        //$this->form->addFields([new TLabel('Estado pedido:')],[$estado_pedido_id]);
        $this->form->addFields([new TLabel('Dt pedido (de):')],[$dt_pedido_ini],[new TLabel('Dt pedido (até):')],[$dt_pedido_ate]);

        // mantém o form preenhido com os valores buscados
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-success');
        $this->form->addAction('Cadastrar', new TAction(['PedidoForm', 'onClear']), 'fa:plus #69aa46');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        
        $column_id           = new TDataGridColumn('id', 'Id', 'left' , '124px');
        $column_cliente_nome = new TDataGridColumn('cliente_id->nome', 'Cliente', 'left');
        $column_dt_pedido    = new TDataGridColumn('dt_pedido', 'Dt pedido', 'center');
        $column_valor_total  = new TDataGridColumn('valor_total', 'Valor total', 'right');
        
        $column_valor_total->setTransformer(function($value, $object, $row) {
            if (!$value) {
                $value = 0;
            }
            return "R$ " . number_format($value, 2, ",", ".");
        });
        
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_cliente_nome);
       // $this->datagrid->addColumn($column_estado_pedido_nome);
        $this->datagrid->addColumn($column_dt_pedido);
        $this->datagrid->addColumn($column_valor_total);

        $action_edit = new TDataGridAction(array('PedidoFormView', 'onShow'));
        $action_edit->setButtonClass('btn btn-default btn-sm');
        $action_edit->setLabel('Visualizar');
        $action_edit->setImage('fa:search green');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        $action_delete = new TDataGridAction(array('PedidoList', 'onDelete'));
        $action_delete->setButtonClass('btn btn-default btn-sm');
        $action_delete->setLabel('Excluir');
        $action_delete->setImage('fa:trash-o #dd5a43');
        $action_delete->setField('id');
        $this->datagrid->addAction($action_delete);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
    }
}
