<?php
/**
 * ProdutoForm Form
 * @author  <your name here>
 */
class ProdutoForm extends TPage
{
    protected $form; // form
    protected $estoques;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Produto');
        // define the form title
        $this->form->setFormTitle('Produtos');

        $id = new TEntry('id');
        $tipo_produto_id = new TDBCombo('tipo_produto_id', 'venda', 'TipoProduto', 'id', '{nome}','id asc'  );
        $nome = new TEntry('nome');
        $fornecedor_id = new TDBUniqueSearch('fornecedor_id', 'venda', 'Pessoa', 'id', 'nome','id asc'  );
        $codigo_barras = new TEntry('codigo_barras');
        $dt_cadastro = new TDate('dt_cadastro');
        $preco_custo = new TNumeric('preco_custo', '2', ',', '.' );
        $preco_venda = new TNumeric('preco_venda', '2', ',', '.' );
        $qtde_estoque = new TNumeric('qtde_estoque', '2', ',', '.' );
        $obs = new TText('obs');

        $tipo_produto_id->addValidation('Tipo produto id', new TRequiredValidator()); 
        $fornecedor_id->addValidation('Fornecedor id', new TRequiredValidator()); 

        $id->setEditable(false);
        $fornecedor_id->setMinLength(2);
        $dt_cadastro->setDatabaseMask('yyyy-mm-dd');

        $fornecedor_id->setMask('{nome}');
        $dt_cadastro->setMask('dd/mm/yyyy');

        $id->setSize(100);
        $nome->setSize('72%');
        $obs->setSize('89%', 68);
        $dt_cadastro->setSize(100);
        $preco_custo->setSize('72%');
        $preco_venda->setSize('72%');
        $qtde_estoque->setSize('28%');
        $fornecedor_id->setSize('70%');
        $codigo_barras->setSize('72%');
        $tipo_produto_id->setSize('72%');
        $id->setEditable(FALSE);
        
        $this->form->addFields([new TLabel('Id:')],[$id],[new TLabel('Tipo produto:', '#ff0000')],[$tipo_produto_id]);
        $this->form->addFields([new TLabel('Nome do Produto:', '#ff0000')],[$nome],[new TLabel('Fornecedor:', '#ff0000')],[$fornecedor_id]);
        $this->form->addFields([new TLabel('Codigo barras:')],[$codigo_barras],[new TLabel('Dt cadastro:')],[$dt_cadastro]);
        $this->form->addFields([new TLabel('Preco custo:')],[$preco_custo],[new TLabel('Preco venda:')],[$preco_venda]);
        $this->form->addFields([new TLabel('Qtde estoque:')],[$qtde_estoque]);
        $this->form->addFields([new TLabel('Obs:')],[$obs]);
        
        $estoque_lote  = new TEntry('estoque_lote[]');
        $estoque_local = new TEntry('estoque_local[]');
        $estoque_qtde  = new TEntry('estoque_qtde[]');
        
        $this->estoques = new TFieldList;
        $this->estoques->addField( '<b>Lote</b>',  $estoque_lote);
        $this->estoques->addField( '<b>Local</b>', $estoque_local);
        $this->estoques->addField( '<b>Qtde</b>',  $estoque_qtde);
        
        $this->form->addField($estoque_lote);
        $this->form->addField($estoque_local);
        $this->form->addField($estoque_qtde);
        
        $this->form->addContent([new TLabel('Estoques')], [$this->estoques]);
        
        // create the form actions
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:floppy-o')->addStyleClass('btn-success');
        $this->form->addAction('Limpar formulário', new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');
        $this->form->addAction(_t('Back'),new TAction(array('ProdutoList','onReload')),'fa:arrow-circle-o-left green');
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(new TXMLBreadCrumb('menu.xml', 'ProdutoList'));
        $container->add($this->form);

        parent::add($container);

    }
    
    /**
     * method onSave
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {
        try
        {
            // open a transaction with database 'samples'
            TTransaction::open('venda');
            $param['preco_custo'] = str_replace(',', '.', str_replace('.', '', $param['preco_custo']));
            $param['preco_venda'] = str_replace(',', '.', str_replace('.', '', $param['preco_venda']));
            $param['dt_cadastro'] = TDate::convertToMask($param['dt_cadastro'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            
            $produto = new Produto;
            $produto->fromArray( $param );
            $produto->store();
            
            Estoque::where('produto_id', '=', $produto->id)->delete();
            
            if( !empty($param['estoque_lote']) AND is_array($param['estoque_lote']) )
            {
                foreach( $param['estoque_lote'] as $row => $estoque_lote)
                {
                    if ($estoque_lote)
                    {
                        $estoque = new Estoque;
                        $estoque->lote  = $estoque_lote;
                        $estoque->local = $param['estoque_local'][$row];
                        $estoque->qtde  = $param['estoque_qtde'][$row];
                        $estoque->produto_id = $produto->id;
                        $estoque->store();
                    }
                }
            }
            
            $data = new stdClass;
            $data->id = $produto->id;
            TForm::sendData('form_Produto', $data);
            
            new TMessage('info', 'Registro salvo');
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit
     * Load the record to the screen
     */
    public function onEdit($param)
    {
        try
        {
            if (isset($param['id']))
            {
                TTransaction::open('venda');
                
                $produto = new Produto($param['id']);
                $estoques = $produto->hasMany('Estoque', 'produto_id');
                
                if ($estoques)
                {
                    $this->estoques->addHeader();
                    
                    foreach ($estoques as $estoque)
                    {
                        $estoque_detail = new stdClass;
                        $estoque_detail->estoque_lote  = $estoque->lote;
                        $estoque_detail->estoque_local = $estoque->local;
                        $estoque_detail->estoque_qtde  = $estoque->qtde;
                        
                        $this->estoques->addDetail($estoque_detail);
                    }
                    
                    $this->estoques->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                $this->form->setData($produto);
                TTransaction::close();
            }
            else
            {
                $this->onClear($param);
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->form->clear();
        
        $this->estoques->addHeader();
        $this->estoques->addDetail( new stdClass );
        $this->estoques->addCloneAction();
    }
    
    public function onShow()
    {
    } 
}
