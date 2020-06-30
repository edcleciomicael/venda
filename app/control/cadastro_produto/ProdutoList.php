<?php
/**
 * ProdutoList Listing
 * @author  <your name here>
 */
class ProdutoList extends TStandardList
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
        parent::setActiveRecord('Produto');
        parent::addFilterField('id', '=', 'id');
        parent::addFilterField('tipo_produto_id', '=', 'tipo_produto_id');
        parent::addFilterField('nome', 'like', 'nome');
        parent::addFilterField('fornecedor_id', '=', 'fornecedor_id');
        parent::addFilterField('codigo_barras', 'like', 'codigo_barras');
        parent::setDefaultOrder('id', 'desc');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('list_Produto');

        // define the form title
        $this->form->setFormTitle('Produtos');

        $id = new TEntry('id');
        $tipo_produto_id = new TDBCombo('tipo_produto_id', 'venda', 'TipoProduto', 'id', '{nome}','id asc'  );
        $nome = new TEntry('nome');
        $fornecedor_id = new TDBCombo('fornecedor_id', 'venda', 'Pessoa', 'id', '{nome}','id asc'  );
        $codigo_barras = new TEntry('codigo_barras');

        $id->setSize(100);
        $nome->setSize('100%');
        $codigo_barras->setSize('39%');
        $fornecedor_id->setSize('100%');
        $tipo_produto_id->setSize('100%');

        $this->form->addFields([new TLabel('Id:')],[$id],[new TLabel('Tipo produto:')],[$tipo_produto_id]);
        $this->form->addFields([new TLabel('Nome:')],[$nome],[new TLabel('Fornecedor:')],[$fornecedor_id]);
        $this->form->addFields([new TLabel('Codigo barras:')],[$codigo_barras]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search')->addStyleClass('btn-success');
        $this->form->addAction('Cadastrar', new TAction(['ProdutoForm', 'onClear']), 'fa:plus #69aa46');

        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';

        $column_id = new TDataGridColumn('id', 'Id', 'center' , '50');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_tipo_produto_nome = new TDataGridColumn('tipo_produto->nome', 'Tipo produto', 'left');
        $column_codigo_barras = new TDataGridColumn('codigo_barras', 'Codigo barras', 'left');
        $column_preco_venda = new TDataGridColumn('preco_venda', 'Preco venda', 'left');
        $column_qtde_estoque = new TDataGridColumn('qtde_estoque', 'Estoque', 'left');

        $column_preco_venda->setTransformer(function($value, $object, $row) 
        {
            if(!$value)
            {
                $value = 0;
            }
            return "R$ " . number_format($value, 2, ",", ".");
        });        

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_tipo_produto_nome);
        $this->datagrid->addColumn($column_codigo_barras);
        $this->datagrid->addColumn($column_preco_venda);
        $this->datagrid->addColumn($column_qtde_estoque);

        $action_onEdit = new TDataGridAction(array('ProdutoForm', 'onEdit'));
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
