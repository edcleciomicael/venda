<?php
/**
 * PedidoItemBatchDocument Form
 * @author  <your name here>
 */
class PedidoDocument extends TPage
{
    protected $form;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder('form_PedidoDocument');
        $this->form->setFormTitle('Documento do Pedido');
        
        $id               = new TEntry('id');
        $cliente_id       = new TDBUniqueSearch('cliente_id', 'venda', 'Pessoa', 'id', 'nome','nome asc'  );
        $dt_pedido_ini    = new TDate('dt_pedido_ini');
        $dt_pedido_ate    = new TDate('dt_pedido_ate');
        
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

        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Cliente:')],[$cliente_id]);
        $this->form->addFields([new TLabel('Dt pedido (de):')],[$dt_pedido_ini],[new TLabel('Dt pedido (até):')],[$dt_pedido_ate]);

        // mantém o formulário preenchido com os últimos valores buscados
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // botão de ação
        $this->form->addAction('Gerar', new TAction([$this, 'onGenerate']), 'fa:cog')->addStyleClass('btn-success');
        
        // container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        parent::add($container);
    }
    
    /**
     * Gera documento
     */
    public function onGenerate($param)
    {
        try 
        {
            TTransaction::open('venda');
            
            // obtém dados do form
            $data = $this->form->getData();
            
            // cria critério de filto
            $criteria = new TCriteria();

            if (isset($data->id) AND ($data->id)) 
            {
                $criteria->add(new TFilter('id', '=', $data->id));
            }
            if (isset($data->cliente_id) AND ($data->cliente_id)) 
            {
                $criteria->add(new TFilter('cliente_id', '=', $data->cliente_id));
            }
    
            }
            if (isset($data->dt_pedido_ini) AND ($data->dt_pedido_ini)) 
            {
                $criteria->add(new TFilter('dt_pedido', '>=', $data->dt_pedido_ini));
            }
            if (isset($data->dt_pedido_ate) AND ($data->dt_pedido_ate)) 
            {
                $criteria->add(new TFilter('dt_pedido', '<=', $data->dt_pedido_ate));
            }

            TSession::setValue(__CLASS__.'_filter_data', $data);

            // carrega pedidos
            $objects = Pedido::getObjects($criteria, FALSE);
            
            if ($objects)
            {
                $output = '';
                $count = 1;
                $count_records = count($objects);
                
                // percorre pedidos
                foreach ($objects as $object)
                {
                    $html = new AdiantiHTMLDocumentParser('app/documents/PedidoDocument.html', 'A4', 'portrait');
                    $html->setMaster($object);

                    $objects = PedidoItem::where('pedido_id', '=', $object->id)->load();
                    $html->setDetail('PedidoItem', $objects);
                    $html->process();
                    
                    if ($count < $count_records)
                    {
                        $html->addPageBreak();
                    }
                    
                    // concatena resultado do documento
                    $output .= $html->getContents();
                    
                    $count ++;
                }

                $document = 'tmp/'.uniqid().'.pdf';
                
                // convete para PDF 
                $html = AdiantiHTMLDocumentParser::newFromString($output);
                $html->saveAsPDF($document);

                // abre arquivo
                parent::openFile($document);
                new TMessage('info', _t('Document successfully generated'));
            }
            else
            {
                new TMessage('error', 'No records found');
            }

            TTransaction::close();
            
            // mantém form preenchido
            $this->form->setData($data);

        } 
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    } 
}
