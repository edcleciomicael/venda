<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class ETESTEPage extends TPage
{

    private $form;

    public function __construct()
    {

        parent::__construct();

        $this->form = new TQuickForm();

$this->form = new BootstrapFormWrapper(new TQuickForm('form_produto'));

$this->form->setFormTitle( 'Cadastro de Produtos' );
$this->form->class = 'form_produto';

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


        parent::add($this->form);

    }

    public static function onComplete($param)
    {
        new TMessage('info', 'Upload completo: '.$param['nomearquivo']);

        // refresh photo_frame
        TScript::create("$('#photo_frame').html('')");
        TScript::create("$('#photo_frame').append(\"<img style='width:100%' src='tmp/{$param['nomearquivo']}'>\");");
    }


    function onSave()
    {

        try {

            TTransaction::open('database');

            $this->form->validate();

            //$cadastro = $this->form->getData('ProdutoRecord');

            //$cadastro->usuarioalteracao = $_SESSION['usuario'];
            //$cadastro->dataalteracao = date("d/m/Y H:i:s");

            $cadastro->store();

            TTransaction::close();

            $action_ok = new TAction( [ 'ProdutoList', "onReload" ] );

            new TMessage( "info", "Registro salvo com sucesso!", $action_ok );

        } catch (Exception $e) {

            new TMessage('error', $e->getMessage());

            TTransaction::rollback();

        }



    }

    function onEdit($param)
    {

        try {

            if (isset($param['key'])) {

                $key = $param['key'];

                TTransaction::open('database');

                $object = new ProdutoRecord($key);

                $this->form->setData($object);

                TTransaction::close();

            }

        } catch (Exception $e) {


            new TMessage('error', '<b>Error</b> ' . $e->getMessage() . "<br/>");

            TTransaction::rollback();

        }

    }


}
?>
