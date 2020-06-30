<?php
/**
 * LoginMultiUnitForm Registration
 * @author  <your name here>
 */
class LoginMultiUnitForm extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();

        $table = new TTable;
        $table->width = '100%';
        // creates the form
        $this->form = new BootstrapFormBuilder('form_login');
        
        $this->form->setFormTitle('{login_form_title}');
        //login_panel_color
        // add the notebook inside the form
        

        // create the form fields
        $login = new TEntry('login');
        $password = new TPassword('password');
        $system_unit_id = new TCombo('system_unit_id');
        
        $login->addValidation('Login', new TRequiredValidator()); 
        $password->addValidation('Senha', new TRequiredValidator()); 
        $system_unit_id->addValidation('Unidade', new TRequiredValidator()); 
        
        $login->setExitAction(new TAction([$this, 'onExitLogin']));
        
        // define the sizes
        $login->setSize('80%', 40);
        $password->setSize('80%', 40);
        $system_unit_id->setSize('80%', 40);

        $login->style = 'height:35px; font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
        $password->style = 'height:35px;font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
        $system_unit_id->style = 'height:35px;font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';

        $login->placeholder = _t('User');
        $password->placeholder = _t('Password');

        $user = '<span style="float:left;width:35px;margin-left:25px;height:35px;" class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>';
        $locker = '<span style="float:left;width:35px;margin-left:25px;height:35px;" class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>';
        $factory = '<span style="float:left;width:35px;margin-left:25px;height:35px;" class="input-group-addon"><i class="fa fa-industry" aria-hidden="true"></i></span>';
        
        $container1 = new TElement('div');
        $container1->add($user);
        $container1->add($login);

        $container2 = new TElement('div');
        $container2->add($locker);
        $container2->add($password);
        
        $container3 = new TElement('div');
        $container3->add($factory);
        $container3->add($system_unit_id);
        
        $this->form->addContent([$container1]);
        $this->form->addContent([$container2]);
        $this->form->addContent([$container3]);
        $loginButton = $this->form->addAction('{login_button_text}', new TAction(array($this, 'onLogin')), '');
        $loginButton->class = 'btn btn-sm {login_button_color}';
        
        $this->form->setFields(array($login, $password, $system_unit_id ));
        
        $container = new TElement('div');
        $container->style = 'max-width:600px; margin:auto; margin-top:190px;';
        $h3 = new TElement('h1');
        $h3->style = 'text-align:center;';
        $h3->add('{login_title}');
        
        $container->add($h3);
        
        $div = new TElement('div');
        $container->add($div);
        
        $div->add($this->form);
        $div->style = 'max-width: 450px; margin:auto;';
        $div->class = 'login-form';

        // add the form to the page
        parent::add($container);
    }
    
    public static function onExitLogin($param)
    {
        try 
        {
            TTransaction::open('permission');
            $systemUser = SystemUser::newFromLogin($param['login']);
            if($systemUser)
            {
                $units = $systemUser->getSystemUserUnits();
                
                if($units)
                {
                    $itens = [];
                    foreach ($units as $unit) 
                    {
                        $itens[$unit->id] = $unit->name; 
                    }
                    TCombo::reload('form_login', 'system_unit_id', $itens, false);
                }
                else
                {
                    TCombo::clearField('form_login', 'system_unit_id');
                }
            }
            else
            {
                TCombo::clearField('form_login', 'system_unit_id');
            }
            TTransaction::close();    
        } 
        catch (Exception $e) 
        {
            new TMessage('error',$e->getMessage());
        }
    }
    
    /**
     * Authenticate the User
     */
    public function onLogin()
    {
        try
        {
            TTransaction::open('permission');
            $data = $this->form->getData('StdClass');
            $this->form->validate();
            $user = SystemUser::authenticate( $data->login, $data->password );
            if ($user)
            {
                TSession::regenerate();
                $programs = $user->getPrograms();
                $programs['LoginMultiUnitForm'] = TRUE;
                
                if (!empty($user->unit))
                {
                    TSession::setValue('userunitid',$user->unit->id);
                }
                
                TSession::setValue('login_unit_id', $data->system_unit_id);
                TSession::setValue('user_unit_ids', $user->getSystemUserUnitIds());
                
                TSession::setValue('logged', TRUE);
                TSession::setValue('login', $data->login);
                TSession::setValue('userid', $user->id);
                TSession::setValue('usergroupids', $user->getSystemUserGroupIds());
                TSession::setValue('username', $user->name);
                TSession::setValue('frontpage', '');
                TSession::setValue('programs',$programs);
                
                $frontpage = $user->frontpage;
                SystemAccessLog::registerLogin();
                if ($frontpage instanceof SystemProgram AND $frontpage->controller)
                {
                    AdiantiCoreApplication::gotoPage($frontpage->controller); // reload
                    TSession::setValue('frontpage', $frontpage->controller);
                }
                else
                {
                    AdiantiCoreApplication::gotoPage('EmptyPage'); // reload
                    TSession::setValue('frontpage', 'EmptyPage');
                }
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error',$e->getMessage());
            TSession::setValue('logged', FALSE);
            TTransaction::rollback();
        }
    }
    
    /** 
     * Reload permissions
     */
    public static function reloadPermissions()
    {
        try
        {
            TTransaction::open('permission');
            $user = SystemUser::newFromLogin( TSession::getValue('login') );
            if ($user)
            {
                $programs = $user->getPrograms();
                $programs['LoginMultiUnitForm'] = TRUE;
                TSession::setValue('programs', $programs);
                
                $frontpage = $user->frontpage;
                if ($frontpage instanceof SystemProgram AND $frontpage->controller)
                {
                    TApplication::gotoPage($frontpage->controller); // reload
                }
                else
                {
                    TApplication::gotoPage('EmptyPage'); // reload
                }
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Logout
     */
    public static function onLogout()
    {
        SystemAccessLog::registerLogout();
        TSession::freeSession();
        AdiantiCoreApplication::gotoPage('LoginMultiUnitForm', '');
    }
}
