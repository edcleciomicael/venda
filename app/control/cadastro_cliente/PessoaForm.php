<?php
/**
 * PessoaForm Form
 * @author  <your name here>
 */
class PessoaForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Pessoa');
        // define the form title
        
        $this->form->setFormTitle('Pessoas');

        $id = new TEntry('id');
        $situacao = new TDBCombo('situacao', 'venda', 'Situacao', 'id', '{situacao}','id asc'  );
        $nome = new TEntry('nome');
        $documento = new TNumeric('documento','2', '-', '.','.');
        $fone = new TEntry('fone');
        $email = new TEntry('email');
        $rua = new TEntry('rua');
        $numero = new TEntry('numero');
        $complemento = new TEntry('complemento');
        $bairro = new TEntry('bairro');
        $cep = new TEntry('cep');
        $cidade_id = new TEntry('cidade_id');
        $obs = new TText('obs');
        $dt_nascimento = new TDate('dt_nascimento');
        $uf = new TEntry('uf');  
        $rg = new TNumeric('rg','0', '.', '.'); 
        $qtd_dias = new TEntry('qtd_dias');
        $insc_estadual = new TNumeric('insc_estadual','0', '.','.');

        $nome->addValidation('Nome', new TRequiredValidator()); 
        $cidade_id->addValidation('Cidade id', new TRequiredValidator()); 

        //$pessoa_grupos->setLayout('horizontal');
        $id->setEditable(false);
              $dt_nascimento->setDatabaseMask('yyyy-mm-dd');
        $dt_nascimento->setMask('dd/mm/yyyy');

        $id->setSize(100);
        $rua->setSize('72%');
        $cep->setSize('72%');
        $nome->setSize('89%');
        $fone->setSize('72%');
        $email->setSize('72%');
        $numero->setSize('72%');
        $bairro->setSize('72%');
        $obs->setSize('89%', 68);
        $documento->setSize('72%');
        $cidade_id->setSize('72%');
        $complemento->setSize('72%');


        $dt_nascimento->setSize('72%');
        $uf->setSize('20%');
        $rg->setSize('72%');
        $qtd_dias->setSize('20%');
        $insc_estadual->setSize('72%');
        $situacao->setSize('72%');

        //$pessoa_grupos->setSize(200);
        $id->setEditable(FALSE);
        
        $this->form->addFields([new TLabel('Id:')],[$id],[new TLabel('Situação:')],[$situacao]);
        $this->form->addFields([new TLabel('Nome:', '#ff0000')],[$nome]);
        $this->form->addFields([new TLabel('Documento:')],[$documento],[new TLabel('Inscrição Estadual')],[$insc_estadual]);
        $this->form->addFields([new TLabel('Fone:')],[$fone],[new TLabel('Email:')],[$email]);
        $this->form->addFields([new TLabel('Rua:')],[$rua],[new TLabel('Numero:')],[$numero]);
        $this->form->addFields([new TLabel('Complemento:')],[$complemento],[new TLabel('Bairro:')],[$bairro]);

        $this->form->addFields([new TLabel('Cep:')],[$cep],[new TLabel('Cidade:', '#ff0000')],[$cidade_id]);
        $this->form->addFields([new TLabel('UF:')],[$uf],[new TLabel('RG:', '#ff0000')],[$rg]);
        

        $this->form->addFields([new TLabel('Data de Nascimento:')],[$dt_nascimento],[new TLabel('Quantidade de Dias:', '#ff0000')],[$qtd_dias]);

        $this->form->addFields([new TLabel('Obs:')],[$obs]);

        // create the form actions
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:floppy-o')->addStyleClass('btn-success');
        $this->form->addAction('Limpar formulário', new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');
         $this->form->addAction(_t('Back'),new TAction(array('PessoaList','onReload')),'fa:arrow-circle-o-left green');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaForm'));
        $container->add($this->form);

        parent::add($container);

    }

    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open('venda'); // abre transação
            
            $this->form->validate(); // valida dados
            
            $data = $this->form->getData(); // dados do form
            
            $object = new Pessoa(); // create an empty object
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

           /* PessoaGrupo::where('pessoa_id', '=', $object->id)->delete();
            
            if ($data->pessoa_grupos)
            {
                foreach ($data->pessoa_grupos as $pessoa_grupo_id)
                {
                    $pessoa_grupo = new PessoaGrupo;

                    $pessoa_grupo->grupo_id = $pessoa_grupo_id;
                    $pessoa_grupo->pessoa_id = $object->id;
                    $pessoa_grupo->store();
                }*/
            //}

            //$data->id = $object->id; 

            //$this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() );
            TTransaction::rollback();
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear();

    }  

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['id']))
            {
                $id = $param['id'];  // get the parameter $id
                TTransaction::open('venda'); // open a transaction

                $object = new Pessoa($id); // instantiates the Active Record 

                //$object->pessoa_grupos = PessoaGrupo::where('pessoa_id', '=', $object->id)->getIndexedArray('grupo_id', 'grupo_id');

                $this->form->setData($object); // fill the form 

                TTransaction::close(); // close the transaction 
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }


 /**
     * Exibe a página
     * @param $param Request
     */
    /*public function show() 
    { 
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') ) 
        { 
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }*/


}

