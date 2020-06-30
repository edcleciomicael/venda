<?php
/**
 * PedidoFormView Form
 * @author  <your name here>
 */
class PessoaFormView extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();

        TTransaction::open('venda');
        $this->form = new BootstrapFormBuilder('form_Pessoa');
        $this->form->setFormTitle('Pessoa');

        $label1 = new TLabel('Id:', '#333333', '12px', '');
        $label2 = new TLabel('Situação:', '#333333', '12px', '');
        $label3 = new TLabel('Cliente:', '#333333', '12px', '');
        $label4 = new TLabel('Documento:', '#333333', '12px', '');
        $label5 = new TLabel('Inscrição Estadual:', '#333333', '12px', '');
        $label6 = new TLabel('Fone:', '#333333', '12px', '');
    
        $label7 = new TLabel('Email:', '#333333', '12px', '');
        $label8 = new TLabel('Rua:', '#333333', '12px', '');
        $label9 = new TLabel('Numero:', '#333333', '12px', '');
        $label10 = new TLabel('Complemento:', '#333333', '12px', '');
        $label11 = new TLabel('Bairro:', '#333333', '12px', '');
        $label12 = new TLabel('Cep:', '#333333', '12px', '');

        $label13 = new TLabel('Cidade:', '#333333', '12px', '');
        $label14 = new TLabel('UF:', '#333333', '12px', '');
        $label15 = new TLabel('RG:', '#333333', '12px', '');
        $label16 = new TLabel('Data de Nascimento:', '#333333', '12px', '');
        $label17 = new TLabel('Quantidade de Dias:', '#333333', '12px', '');
        $label18 = new TLabel('Obs:', '#333333', '12px', '');
        $pessoa = new Pessoa($param['key']);
        
        $text1  = new TTextDisplay($pessoa->id, '#333333', '12px', '');
        $text2  = new TTextDisplay($pessoa->situacao, '#333333', '12px', '');
        $text3  = new TTextDisplay($pessoa->nome, '#333333', '12px', '');
        $text4  = new TTextDisplay($pessoa->documento, '#333333', '12px', '');
        $text5  = new TTextDisplay($pessoa->insc_estadual, '#333333', '12px', '');
        /*$text5  = new TTextDisplay(number_format($pessoa->valor_total, '2', ',', '.'), '#333333', '12px', '');*/
         $text6  = new TTextDisplay($pessoa->fone, '#333333', '12px', '');

        $text7  = new TTextDisplay($pessoa->email, '#333333', '12px', '');

        $text8  = new TTextDisplay($pessoa->rua, '#333333', '12px', '');
        $text9  = new TTextDisplay($pessoa->numero, '#333333', '12px', '');
        $text10  = new TTextDisplay($pessoa->complemento, '#333333', '12px', '');
        $text11  = new TTextDisplay($pessoa->bairro, '#333333', '12px', '');
        $text12  = new TTextDisplay($pessoa->cep, '#333333', '12px', '');
        $text13  = new TTextDisplay($pessoa->cidade_id, '#333333', '12px', '');
        $text14  = new TTextDisplay($pessoa->uf, '#333333', '12px', '');
        $text15  = new TTextDisplay($pessoa->rg, '#333333', '12px', '');
        $text16  = new TTextDisplay(TDate::convertToMask($pessoa->dt_nascimento, 'yyyy-mm-dd', 'dd/mm/yyyy'), '#333333', '12px', '');
        $text17  = new TTextDisplay($pessoa->qtd_dias, '#333333', '12px', '');
        $text18  = new TTextDisplay($pessoa->obs, '#333333', '12px', '');
        

        
        $this->form->addFields([$label1],[$text1]);
        $this->form->addFields([$label2],[$text2]);
        $this->form->addFields([$label3],[$text3]);
        $this->form->addFields([$label4],[$text4]);
        $this->form->addFields([$label5],[$text5]);
        $this->form->addFields([$label6],[$text6]);
        $this->form->addFields([$label7],[$text7]);
        $this->form->addFields([$label8],[$text8]);
        $this->form->addFields([$label9],[$text9]);
        $this->form->addFields([$label10],[$text10]);
        $this->form->addFields([$label11],[$text11]);
        $this->form->addFields([$label12],[$text12]);
        $this->form->addFields([$label13],[$text13]);
        $this->form->addFields([$label14],[$text14]);
        $this->form->addFields([$label15],[$text15]);
        $this->form->addFields([$label16],[$text16]);
        $this->form->addFields([$label17],[$text17]);
        $this->form->addFields([$label18],[$text18]);
        
        /*$this->pedido_item_list = new TQuickGrid;
        $this->pedido_item_list->style = 'width:100%';
        $this->pedido_item_list->disableDefaultClick();
        
        $this->pedido_item_list->addQuickColumn('Produto', 'produto->nome', 'left');
        $this->pedido_item_list->addQuickColumn('Quantidade', 'quantidade', 'center');
        $this->pedido_item_list->addQuickColumn('Valor', 'valor', 'right');
        $column_total = $this->pedido_item_list->addQuickColumn('Total', '=( {quantidade} * {valor}  )', 'right');
        
        $column_total->setTotalFunction( function($values) { 
            return array_sum((array) $values); 
        }); 

        $column_total->setTransformer(function($value, $object, $row) {
            if (!$value)
            {
                $value = 0;
            }
            return "R$ " . number_format($value, 2, ",", ".");
        });
        
        $this->pedido_item_list->createModel();
        
        $items = PedidoItem::where('pedido_id', '=', $pedido->id)->load();
        $this->pedido_item_list->addItems($items);
        
        $panel = new TPanelGroup('Itens', '#f5f5f5');
        $panel->add(new BootstrapDatagridWrapper($this->pedido_item_list));
        
        $this->form->addContent([$panel]);*/
        
        $this->form->addHeaderAction('Imprimir', new TAction(['PessoaFormView', 'onPrint'],['key'=>$pessoa->id]), 'fa:file-pdf-o red');
        $this->form->addHeaderAction('Editar', new TAction(['PessoaForm', 'onEdit'],['key'=>$pessoa->id]), 'fa:pencil-square-o green');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'formView-container'; 
        $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaList'));
        $container->add($this->form);

        TTransaction::close();

        parent::add($container);
    }

    /**
     * Imprime o pedido
     */
    public function onPrint($param)
    {
        try
        {
            TTransaction::open('venda');
            
            $object = Pedido::find($param['key']);
            
            if ($object)
            {
                $html = new AdiantiHTMLDocumentParser('app/documents/PedidoDocument.html', 'A4', 'portrait');
                $html->setMaster($object);
    
                $objects = PedidoItem::where('pedido_id', '=', $object->id)->load();
                $html->setDetail('PedidoItem', $objects);
    
                $html->process();
                $output = $html->getContents();
                
                $document = 'tmp/'.uniqid().'.pdf'; 
                $html = AdiantiHTMLDocumentParser::newFromString($output);
                $html->saveAsPDF($document);
                
                parent::openFile($document);
                new TMessage('info', _t('Document successfully generated'));
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        }
    }
    
    public function onShow()
    {      
    } 
}
