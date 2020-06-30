<?php

class ExtensionsInstall extends TPage
{
    private $datagrid;
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    function __construct()
    {
        parent::__construct();
    
        try 
        {
            $phpversion = substr(phpversion(), 0, 1);
            $this->adianti_target_container = 'adianti_div_content';

            $this->form = new BootstrapFormBuilder('form-download-step-1');            
            $this->form->setFormTitle(_t('Installing your application'));

            $extensions = ['PDO'=>'PDO','pgsql'=> 'PostgreSQL','mssql'=>'Microsoft Sql Server', 'mysql'=> 'MySql', 'gd'=>'GD','mbstring'=>'MBString', 'curl'=>'CURL', 'dom'=>'DOM', 'soap'=>'SOAP', 'SimpleXML'=>'SimpleXML'];
            $fields = [];
            
            $tstep = new TStep();
            $tstep->addItem(_t('PHP verification'), true, false);
            $tstep->addItem(_t('Directory verification'), false, false);
            $tstep->addItem(_t('Database configuration/creation'), false, false);
            
            $this->form->addContent([$tstep]);
            
            $separator = new TFormSeparator(_t('PHP version verification and installed extensions'));
            $separator->setFontSize('24');
            $this->form->addContent([$separator]);
            
            foreach ($extensions as $extension => $name) 
            {
                $message = "<div style='font-size:20px;'> <i class='fa fa-check green' aria-hidden='true'></i> {$name} extension({$extension}) installed </div>";
                
                if(!extension_loaded($extension))
                {
                    $message = "<div style='font-size:20px'> <i class='fa fa-times red' aria-hidden='true'></i> {$name} extension({$extension}) not installed  <br> <small style='color:grey;'> On linux at the console type: <b>apt-get install php{$phpversion}-{$extension} </b> </small> </div>";
                    
                }
                $this->form->addContent([$message]);   
            }
            
            TTransaction::close();
            
            
            $this->form->setFields($fields);
            $this->form->addAction(_t('Next'), new TAction([$this, 'nextStep']), 'fa:arrow-right green');
            
            $container = new TElement('div');
            $container->class = 'container formBuilderContainer';
            
            $container->add($this->form);
            
            parent::add($container);
        } 
        catch (Exception $e) 
        {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
    }
    
    public static function nextStep($params = null)
    {
        $form = new DatabaseInstall();
        $form->setIsWrapped(true);
        $form->show();
    }

    public function onShow()
    {
        
    }
    

}