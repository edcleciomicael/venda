<?php
/**
 * ProdutoReport Listing
 * @author  <your name here>
 */
class ProdutoReport extends TPage
{
    private $form; // form
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_ProdutoReport');
        $this->form->setFormTitle('Relatório de produtos');
        
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

        // mantém o form preenchido com os valores da busca
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Gerar HTML', new TAction([$this, 'onGenerateHtml']), 'fa:code')->addStyleClass('btn-success');
        $this->form->addAction('Gerar PDF', new TAction([$this, 'onGeneratePdf']), 'fa:file-pdf-o #d44734');
        $this->form->addAction('Gerar RTF', new TAction([$this, 'onGenerateRtf']), 'fa:file-text-o #324bcc');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        parent::add($container);
    }

    /**
     * Gera relatório em HTML
     */
    public function onGenerateHtml($param = null) 
    {
        $this->onGenerate('html');
    }

    /**
     * Gera relatório em PDF
     */
    public function onGeneratePdf($param = null) 
    {
        $this->onGenerate('pdf');
    }

    /**
     * Gera relatório em RTF
     */
    public function onGenerateRtf($param = null) 
    {
        $this->onGenerate('rtf');
    }

    /**
     * Register the filter in the session
     */
    public function getFilters()
    {
        // get the search form data
        $data = $this->form->getData();
        $filters = [];

        if (isset($data->id) AND ($data->id)) 
        {
            $filters[] = new TFilter('id', '=', $data->id);// create the filter 
        }
        if (isset($data->tipo_produto_id) AND ($data->tipo_produto_id)) 
        {
            $filters[] = new TFilter('tipo_produto_id', '=', $data->tipo_produto_id);// create the filter 
        }
        if (isset($data->nome) AND ($data->nome)) 
        {
            $filters[] = new TFilter('nome', 'like', "%{$data->nome}%");// create the filter 
        }
        if (isset($data->fornecedor_id) AND ($data->fornecedor_id)) 
        {
            $filters[] = new TFilter('fornecedor_id', '=', $data->fornecedor_id);// create the filter 
        }
        if (isset($data->codigo_barras) AND ($data->codigo_barras)) 
        {
            $filters[] = new TFilter('codigo_barras', 'like', "%{$data->codigo_barras}%");// create the filter 
        }
        
        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue(__CLASS__.'_filter_data', $data);
        
        return $filters;
    }

    /**
     * Load the datagrid with data
     */
    public function onGenerate($format)
    {
        try
        {
            $filters = $this->getFilters();
            
            // open a transaction with database 'venda'
            TTransaction::open('venda');
            $param = [];
            
            // creates a repository for Produto
            $repository = new TRepository('Produto');
            
            // creates a criteria
            $criteria = new TCriteria;

            $criteria->setProperties($param);

            if ($filters)
            {
                foreach ($filters as $filter) 
                {
                    $criteria->add($filter);       
                }
            }

            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            if ($objects)
            {
                $widths = array(100,200,200,200,200,200,200,200);

                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths, 'L');
                        break;
                    case 'rtf':
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths);
                        break;
                }

                if (!empty($tr))
                {
                    // create the document styles
                    $tr->addStyle('title', 'Helvetica', '10', 'B',   '#000000', '#dbdbdb');
                    $tr->addStyle('datap', 'Arial', '10', '',    '#333333', '#f0f0f0');
                    $tr->addStyle('datai', 'Arial', '10', '',    '#333333', '#ffffff');
                    $tr->addStyle('header', 'Helvetica', '16', 'B',   '#5a5a5a', '#6B6B6B');
                    $tr->addStyle('footer', 'Helvetica', '10', 'B',  '#5a5a5a', '#A3A3A3');
                    $tr->addStyle('break', 'Helvetica', '10', 'B',  '#ffffff', '#9a9a9a');
                    $tr->addStyle('total', 'Helvetica', '10', 'I',  '#000000', '#c7c7c7');

                    // add titles row
                    $tr->addRow();
                    $tr->addCell('Id', 'left', 'title');
                    $tr->addCell('Nome', 'left', 'title');
                    $tr->addCell('Tipo produto', 'left', 'title');
                    $tr->addCell('Fornecedor id', 'left', 'title');
                    $tr->addCell('Codigo barras', 'left', 'title');
                    $tr->addCell('Preco venda', 'left', 'title');
                    $tr->addCell('Qtde estoque', 'left', 'title');
                    $tr->addCell('Total', 'left', 'title');
                    
                    $grandTotal = [];
                    $breakValue = null;
                    
                    // controls the background filling
                    $colour = false;                
                    foreach ($objects as $object)
                    {
                        $style = $colour ? 'datap' : 'datai';
                        $total_calculado = $object->evaluate('=( {preco_venda} * {qtde_estoque}  )');
                        
                        $grandTotal['qtde_estoque'][] = $object->qtde_estoque;
                        $grandTotal['total_calculado'][] = $total_calculado;
                        
                        $object->preco_venda = "R$ " . number_format($object->preco_venda, 2, ",", ".");
                        $total_calculado = "R$ " . number_format($total_calculado, 2, ",", ".");
                        
                        $tr->addRow();
                        $tr->addCell($object->id, 'left', $style);
                        $tr->addCell($object->nome, 'left', $style);
                        $tr->addCell($object->tipo_produto->nome, 'left', $style);
                        $tr->addCell($object->fornecedor->nome, 'left', $style);
                        $tr->addCell($object->codigo_barras, 'left', $style);
                        $tr->addCell($object->preco_venda, 'right', $style);
                        $tr->addCell($object->qtde_estoque, 'right', $style);
                        $tr->addCell($total_calculado, 'right', $style);

                        $colour = !$colour;
                    }

                    $tr->addRow();

                    $total_estoque = array_sum($grandTotal['qtde_estoque']);
                    $total_valor   = array_sum($grandTotal['total_calculado']);
                    $total_valor   = "R$ " . number_format($total_valor, 2, ",", ".");
                    
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell($total_estoque, 'right', 'total');
                    $tr->addCell($total_valor, 'right', 'total');
                    
                    $file = 'report_'.uniqid().".{$format}";
                    // stores the file
                    if (!file_exists("app/output/{$file}") || is_writable("app/output/{$file}"))
                    {
                        $tr->save("app/output/{$file}");
                    }
                    else
                    {
                        throw new Exception(_t('Permission denied') . ': ' . "app/output/{$file}");
                    }

                    parent::openFile("app/output/{$file}");

                    // shows the success message
                    new TMessage('info', 'Report generated. Please, enable popups.');
                }
            }
            else
            {
                new TMessage('error', _t('No records found'));
            }

            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onShow()
    {
    }
}
