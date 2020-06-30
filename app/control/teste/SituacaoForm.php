<?php
/**
 * EstadoPedidoForm Form
 * @author  <your name here>
 */
class SituacaoForm extends TStandardForm
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setDatabase('micro');
        parent::setActiveRecord('Situacao');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Situacao');
        $this->form->setFormTitle('Situacao');

        $id = new TEntry('id');
        $situacao = new TEntry('situacao');

        $id->setEditable(false);
        $id->setSize(100);
        $situacao->setSize('70%');
        $id->setEditable(FALSE);
        
        $this->form->addFields([new TLabel('Id:')], [$id]);
        $this->form->addFields([new TLabel('Situacao:')], [$situacao]);
        
        // create the form actions
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:floppy-o')->addStyleClass('btn-success');
        $this->form->addAction('Limpar formulário', new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');
         $this->form->addAction(_t('Back'),new TAction(array('SituacaoList','onReload')),'fa:arrow-circle-o-left green');
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        //$container->add(new TXMLBreadCrumb('menu.xml', 'SituacaoList'));
        $container->add($this->form);

        parent::add($container);
    }
    
    public function onShow()
    {
    
    }
}