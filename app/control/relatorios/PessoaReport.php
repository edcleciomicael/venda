<?php
/**
 * PessoaReport Listing
 * @author  <your name here>
 */
class PessoaReport extends TPage
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

        $this->form = new BootstrapFormBuilder('form_PessoaReport');
        $this->form->setFormTitle('Relatório de Pessoas');
        
        $id        = new TEntry('id');
        $nome      = new TEntry('nome');
        $documento = new TEntry('documento');
        $fone      = new TEntry('fone');
        $email     = new TEntry('email');
        $bairro    = new TEntry('bairro');
        $cidade_id = new TEntry('cidade_id' );
        
        $id->setSize(100);
        $nome->setSize('72%');
        $fone->setSize('72%');
        $email->setSize('72%');
        $bairro->setSize('72%');
        $documento->setSize('72%');
        $cidade_id->setSize('72%');
        
        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Nome:')],[$nome],[new TLabel('Documento:')],[$documento]);
        $this->form->addFields([new TLabel('Fone:')],[$fone],[new TLabel('Email:')],[$email]);
        $this->form->addFields([new TLabel('Bairro:')],[$bairro],[new TLabel('Cidade:')],[$cidade_id]);

        // mantém o form preenchido com os valores da busca
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Gerar HTML', new TAction([$this, 'onGenerateHtml']), 'fa:code')->addStyleClass('btn-success');
        $this->form->addAction('Gerar PDF',  new TAction([$this, 'onGeneratePdf']), 'fa:file-pdf-o #d44734');
        $this->form->addAction('Gerar RTF',  new TAction([$this, 'onGenerateRtf']), 'fa:file-text-o #324bcc');
        
        // cria o container
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
     * registra os filtros aplicados na sessão
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
        if (isset($data->nome) AND ($data->nome)) 
        {
            $filters[] = new TFilter('nome', 'like', "%{$data->nome}%");// create the filter 
        }
        if (isset($data->documento) AND ($data->documento)) 
        {
            $filters[] = new TFilter('documento', 'like', "%{$data->documento}%");// create the filter 
        }
        if (isset($data->fone) AND ($data->fone)) 
        {
            $filters[] = new TFilter('fone', 'like', "%{$data->fone}%");// create the filter 
        }
        if (isset($data->email) AND ($data->email)) 
        {
            $filters[] = new TFilter('email', 'like', "%{$data->email}%");// create the filter 
        }
        if (isset($data->bairro) AND ($data->bairro)) 
        {
            $filters[] = new TFilter('bairro', 'like', "%{$data->bairro}%");// create the filter 
        }
        if (isset($data->cidade_id) AND ($data->cidade_id)) 
        {
            $filters[] = new TFilter('cidade_id', '=', $data->cidade_id);// create the filter 
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
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
            
            // creates a repository for Pessoa
            $repository = new TRepository('Pessoa');
            
            // creates a criteria
            $criteria = new TCriteria;

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
                $widths = array(100,200,200,200,200,200);

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
                    $tr->addCell('Fone', 'left', 'title');
                    $tr->addCell('Email', 'left', 'title');
                    $tr->addCell('Cep', 'left', 'title');
                    $tr->addCell('Cidade', 'left', 'title');

                    $grandTotal = [];

                    // controls the background filling
                    $colour = false;                
                    foreach ($objects as $object)
                    {
                        $style = $colour ? 'datap' : 'datai';
                        
                        $grandTotal['cidade_id'][] = $object->cidade_id;
                        
                        $tr->addRow();
                        $tr->addCell($object->id, 'left', $style);
                        $tr->addCell($object->nome, 'left', $style);
                        $tr->addCell($object->fone, 'left', $style);
                        $tr->addCell($object->email, 'left', $style);
                        $tr->addCell($object->cep, 'left', $style);
                        //$tr->addCell($object->cidade->nome, 'left', $style);

                        $colour = !$colour;
                    }

                    $tr->addRow();

                    $total_cidade_id = count($grandTotal['cidade_id']);
                    
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell('', '', 'total');
                    $tr->addCell('Qtde: '.$total_cidade_id, 'left', 'total');

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
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onShow()
    {
    }
}
