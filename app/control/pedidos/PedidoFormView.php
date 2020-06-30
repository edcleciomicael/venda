<?php
/**
 * PedidoFormView Form
 * @author  <your name here>
 */
class PedidoFormView extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Pedido');
        $this->form->setFormTitle('Pedido');

        $label1 = new TLabel('Id:', '#333333', '12px', '');
        $label2 = new TLabel('Cliente:', '#333333', '12px', '');
        /*$label3 = new TLabel('Estado pedido:', '#333333', '12px', '');*/
        $label4 = new TLabel('Dt pedido:', '#333333', '12px', '');
        $label5 = new TLabel('Valor total:', '#333333', '12px', '');
        $label6 = new TLabel('Obs:', '#333333', '12px', '');
        
        $pedido = new Pedido($param['key']);
        
        $text1  = new TTextDisplay($pedido->id, '#333333', '12px', '');
        $text2  = new TTextDisplay($pedido->cliente->nome, '#333333', '12px', '');
        /*$text3  = new TTextDisplay($pedido->estado_pedido_id, '#333333', '12px', '');*/
        $text4  = new TTextDisplay(TDate::convertToMask($pedido->dt_pedido, 'yyyy-mm-dd', 'dd/mm/yyyy'), '#333333', '12px', '');
        $text5  = new TTextDisplay(number_format($pedido->valor_total, '2', ',', '.'), '#333333', '12px', '');
        $text6  = new TTextDisplay($pedido->obs, '#333333', '12px', '');
        
        $this->form->addFields([$label1],[$text1]);
        $this->form->addFields([$label2],[$text2]);
        /*$this->form->addFields([$label3],[$text3]);*/
        $this->form->addFields([$label4],[$text4]);
        $this->form->addFields([$label5],[$text5]);
        $this->form->addFields([$label6],[$text6]);
        
        $this->pedido_item_list = new TQuickGrid;
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
        
        $this->form->addContent([$panel]);
        
        $this->form->addHeaderAction('Imprimir', new TAction(['PedidoFormView', 'onPrint'],['key'=>$pedido->id]), 'fa:file-pdf-o red');
        $this->form->addHeaderAction('Editar', new TAction(['PedidoForm', 'onEdit'],['key'=>$pedido->id]), 'fa:pencil-square-o green');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'formView-container'; 
        $container->add(new TXMLBreadCrumb('menu.xml', 'PedidoList'));
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
