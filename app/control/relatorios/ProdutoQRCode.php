
<?php
/**
 * ProdutoQRCode Form
 * @author  <your name here>
 */
class ProdutoQRCode extends TPage
{
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder('form_ProdutoQRCode');

        // define the form title
        $this->form->setFormTitle('QRCode para produtos');

        $id = new TEntry('id');
        $tipo_produto_id = new TDBCombo('tipo_produto_id', 'venda', 'TipoProduto', 'id', '{nome}','id asc'  );
        $nome = new TEntry('nome');
        $fornecedor_id = new TDBUniqueSearch('fornecedor_id', 'venda', 'Pessoa', 'id', 'nome','id asc'  );
        $codigo_barras = new TEntry('codigo_barras');

        $id->setSize(100);
        $nome->setSize('100%');
        $codigo_barras->setSize('39%');
        $fornecedor_id->setSize('100%');
        $tipo_produto_id->setSize('100%');
        $fornecedor_id->setMinLength(2);

        $this->form->addFields([new TLabel('Id:')],[$id],[new TLabel('Tipo produto:')],[$tipo_produto_id]);
        $this->form->addFields([new TLabel('Nome:')],[$nome],[new TLabel('Fornecedor:')],[$fornecedor_id]);
        $this->form->addFields([new TLabel('Codigo barras:')],[$codigo_barras]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $this->form->addAction('Gerar', new TAction([$this, 'onGenerate']), 'fa:cog')->addStyleClass('btn-success');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        parent::add($container);
    }

    public function onGenerate($param)
    {
        try 
        {
            TTransaction::open('venda');

            $data = $this->form->getData();
            $criteria = new TCriteria();

            if (isset($data->id) AND ($data->id)) 
            {
                $criteria->add(new TFilter('id', '=', $data->id));
            }
            if (isset($data->tipo_produto_id) AND ($data->tipo_produto_id)) 
            {
                $criteria->add(new TFilter('tipo_produto_id', '=', $data->tipo_produto_id));
            }
            if (isset($data->nome) AND ($data->nome)) 
            {
                $criteria->add(new TFilter('nome', 'like', "%{$data->nome}%"));
            }
            if (isset($data->fornecedor_id) AND ($data->fornecedor_id)) 
            {
                $criteria->add(new TFilter('fornecedor_id', '=', $data->fornecedor_id));
            }
            if (isset($data->codigo_barras) AND ($data->codigo_barras)) 
            {
                $criteria->add(new TFilter('codigo_barras', 'like', "%{$data->codigo_barras}%"));
            }

            TSession::setValue(__CLASS__.'_filter_data', $data);

            $properties = [];

            $properties['leftMargin']    = 10; // Left margin
            $properties['topMargin']     = 10; // Top margin
            $properties['labelWidth']    = 64; // Label width in mm
            $properties['labelHeight']   = 28; // Label height in mm
            $properties['spaceBetween']  = 4;  // Space between labels
            $properties['rowsPerPage']   = 10;  // Label rows per page
            $properties['colsPerPage']   = 3;  // Label cols per page
            $properties['fontSize']      = 12; // Text font size
            $properties['barcodeHeight'] = 14; // Barcode Height
            $properties['imageMargin']   = 0;

            $label  = "{id}  \n";
            $label .= "{nome} \n";
            $label .= "#qrcode#  ";

            $bcgen = new AdiantiBarcodeDocumentGenerator('p', 'A4');
            $bcgen->setProperties($properties);
            $bcgen->setLabelTemplate($label);

            $objects = Produto::getObjects($criteria);

            if ($objects)
            {
                foreach ($objects as $object)
                {
                    $bcgen->addObject($object);
                }

                $filename = 'tmp/barcode_'.uniqid().'.pdf';

                $bcgen->setBarcodeContent('{id} - {nome} ');
                $bcgen->generate();
                $bcgen->save($filename);

                parent::openFile($filename);
                new TMessage('info', _t('QR Codes successfully generated'));
            }
            else
            {
                new TMessage('error', _t('No records found'));
            }

            TTransaction::close();

            $this->form->setData($data);
        } 
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    } 
}
