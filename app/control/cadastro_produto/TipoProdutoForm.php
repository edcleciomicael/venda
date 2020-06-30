<?php
/**
 * TipoProdutoForm Form
 * @author  <your name here>
 */
class TipoProdutoForm extends TStandardForm
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setDatabase('venda');
        parent::setActiveRecord('TipoProduto');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_TipoProduto');
        $this->form->setFormTitle('Tipos de produto');

        $id = new TEntry('id');
        $nome = new TEntry('nome');

        $id->setEditable(false);
        $id->setSize(100);
        $nome->setSize('70%');
        $id->setEditable(FALSE);
        
        $this->form->addFields([new TLabel('Id:')],[$id]);
        $this->form->addFields([new TLabel('Nome:')],[$nome]);

        // create the form actions
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:floppy-o')->addStyleClass('btn-success');
        $this->form->addAction('Limpar formulário', new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');
        $this->form->addAction(_t('Back'),new TAction(array('TipoProdutoList','onReload')),'fa:arrow-circle-o-left green');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(new TXMLBreadCrumb('menu.xml', 'TipoProdutoList'));
        $container->add($this->form);

        parent::add($container);
    } 
}