<?php
/**
 * EstadoPedidoList Listing
 * @author  <your name here>
 */
class SituacaoList extends TStandardList
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

        parent::setDatabase('micro');
        parent::setActiveRecord('Situacao');
        parent::addFilterField('id', '=', 'id');
        parent::addFilterField('situacao', 'like', 'situacao');
        parent::setDefaultOrder('id', 'desc');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('list_Situacao');

        // define the form title
        $this->form->setFormTitle('Listagem de estado pedidos');

        $id = new TEntry('id');
        $situacao = new TEntry('situacao');
        $id->setSize(100);
        $situacao->setSize('70%');

        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Situacao:')],[$situacao]);

        // mantém form preenchido com valores de busca
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-success');
        $this->form->addAction('Cadastrar', new TAction(['SituacaoForm', 'onEdit']), 'fa:plus #69aa46');

        // cria a datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        
        $column_id   = new TDataGridColumn('id',   'Id',   'center' , '50');
        $column_situacao = new TDataGridColumn('situacao', 'Situacao', 'left');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_situacao);
        
        $action_onEdit = new TDataGridAction(array('EstadoPedidoForm', 'onEdit'));
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel('Editar');
        $action_onEdit->setImage('fa:pencil-square-o green');
        $action_onEdit->setField('id');

        $this->datagrid->addAction($action_onEdit);

        $action_onDelete = new TDataGridAction(array($this, 'onDelete'));
        $action_onDelete->setButtonClass('btn btn-default btn-sm');
        $action_onDelete->setLabel('Excluir');
        $action_onDelete->setImage('fa:trash-o red');
        $action_onDelete->setField('id');

        $this->datagrid->addAction($action_onDelete);

        $this->datagrid->createModel();

        // navegador
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
    }
}
