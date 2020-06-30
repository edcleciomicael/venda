

    <?php

class ETESTEPagelist extends TPage
    {
        private $form;
        private $datagrid;
        private $loaded;

        public function __construct()
        {
            parent::__construct();

            $this->form = new BootstrapFormWrapper(new TQuickForm('list_produto'));
            //$this->form->setFormTitle( "Listagem de Fornecedores" );
            // $this->form->class = "tform";

            $opcao = new TCombo( "opcao" );
            $dados = new TEntry( "dados" );

            $opcao->setDefaultOption( "..::SELECIONE::.." );
            $dados->setProperty ( "title", "Informe os dados referentes a opção" );

            $opcao->setSize( "38%" );
            $dados->setSize( "38%" );


            $opcao->addItems([
                "nomeproduto" => "Nome do produto"
            ]);

            $opcao->setValue( "nomeproduto" );//unedit

            $this->form->addQuickField( 'Opção de busca', $opcao, '50%' );
            $this->form->addQuickField( 'Dados à buscar', $dados, '50%' );


            $find_button = $this->form->addQuickAction( 'Buscar', new TAction(array($this, 'onSearch')), 'fa:search');
            $find_button->class = 'btn btn-sm btn-primary';

            $new_button = $this->form->addQuickAction( 'Novo', new TAction(array('ProdutoForm', 'onEdit')), 'fa:file');
            $new_button->class = 'btn btn-sm btn-primary';

            // INICIO DATAGRID --------------------------------------------------------------------------------------------


            $this->datagrid = new TDatagridTables();

            $column_nomeproduto = new TDataGridColumn( "nomeproduto", "Nome ", "left" );
            $column_codbarra    = new TDataGridColumn( "codbarra", "Código", "left");
            $column_valorproduto     = new TDataGridColumn( "valorproduto", "valor", "left");



            $this->datagrid->addColumn( $column_nomeproduto );
            $this->datagrid->addColumn( $column_codbarra );
            $this->datagrid->addColumn( $column_valorproduto );




            $action_edit = new TDatagridTablesAction ( [ "ProdutoForm", "onEdit" ] );
            $action_edit->setButtonClass ( "btn btn-default" );
            $action_edit->setLabel ( "Editar Registro" );
            $action_edit->setImage ( "fa:pencil-square-o blue fa-lg" );
            $action_edit->setField ( "id" );
            $this->datagrid->addAction ( $action_edit );

            $action_del = new TDatagridTablesAction( [ $this, "onDelete" ] );
            $action_del->setButtonClass( "btn btn-default" );
            $action_del->setLabel( "Deletar Registro" );
            $action_del->setImage( "fa:trash-o red fa-lg" );
            $action_del->setField( "id" );
            $this->datagrid->addAction( $action_del );

            $this->datagrid->createModel();

            $container = new TVBox();
            $container->style = "width: 100%";
            $container->add( TPanelGroup::pack( 'Listagem de Produtos', $this->form ) );
            //$container->add( $this->form );
            $container->add( TPanelGroup::pack( NULL, $this->datagrid ) );

            parent::add( $container ); //stop here
        }

        public function onReload( $param = NULL ){

            try {

                TTransaction::open( "database" );

                $repository = new TRepository( "ProdutoRecord" );

                $properties = [
                    "order" => "nomeproduto",
                    "direction" => "asc"
                ];

                $criteria = new TCriteria();
                $criteria->setProperties( $properties );

                $objects = $repository->load( $criteria, FALSE );

                $this->datagrid->clear();

                if ( !empty( $objects ) ) {
                    foreach ( $objects as $object ) {
                        $this->datagrid->addItem( $object );
                    }
                }

                $criteria->resetProperties();

                TTransaction::close();

                $this->loaded = true;

            } catch ( Exception $ex ) {

                TTransaction::rollback();

                new TMessage( "error", $ex->getMessage() );

            }
        }

        public function onSearch()
        {
            try {

                $data = $this->form->getData();

                if( !empty( $data->opcao ) && !empty( $data->dados ) ) {

                    TTransaction::open( "database" );

                    $repository = new TRepository( "ProdutoRecord" );

                    $properties = [
                        "order" => "nomeproduto",
                        "direction" => "asc"
                    ];

                    $criteria = new TCriteria();
                    $criteria->setProperties( $properties );

                    switch( $data->opcao ) {

                        case "nomeproduto":
                            $criteria->add( new TFilter( $data->opcao, "LIKE", $data->dados . "%" ) );
                            break;
    /*
                        case "numerocpf":
                            $criteria->add( new TFilter( $data->opcao, "LIKE", $data->dados . "%" ) );
                            break;
    */
                        default:
                            if ( is_numeric( $data->dados ) ) {
                                $criteria->add( new TFilter( $data->opcao, "=", $data->dados ) );
                            } else {
                                new TMessage( "erro", "Para a opção selecionada, informe apenas valores numéricos." );
                            }

                    }

                    $objects = $repository->load( $criteria, FALSE );

                    $this->datagrid->clear();

                    if ( $objects ) {
                        foreach ( $objects as $object ) {
                            $this->datagrid->addItem( $object );
                        }
                    } else {
                      new TMessage( "info", "Não há dados cadastrados!" );
                    }

                    $criteria->resetProperties();

                    TTransaction::close();

                    $this->form->setData( $data );

                    $this->loaded = true;

                } else {

                    $this->onReload();

                    $this->form->setData( $data );

                    new TMessage( "erro", "Selecione uma opção e informe os dados à buscar corretamente!" );
                }

            } catch ( Exception $ex ) {

                TTransaction::rollback();

                new TMessage( "erro", $ex->getMessage() );

            }
        }

        public function onDelete( $param = NULL )
        {
            if( isset( $param[ "key" ] ) ) {

                $action1 = new TAction( [ $this, "Delete" ] );
                $action2 = new TAction( [ $this, "onReload" ] );

                $action1->setParameter( "key", $param[ "key" ] );

                new TQuestion( "Deseja realmente apagar o registro?", $action1, $action2 );
            }
        }

        function Delete( $param = NULL )
        {
            try {

                TTransaction::open( "database" );

                $object = new FornecedorRecord( $param[ "key" ] );
                $object->delete();

                TTransaction::close();

                $this->onReload();

                new TMessage( "info", "O Registro foi apagado com sucesso!" );

            } catch ( Exception $ex ) {

                TTransaction::rollback();

                new TMessage( "erro", $ex->getMessage() );
            }
        }

        public function show()
        {
            $this->onReload();

            parent::show();
        }

    }
