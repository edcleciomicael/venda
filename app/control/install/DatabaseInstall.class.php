<?php

class DatabaseInstall extends TPage
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
            $this->adianti_target_container = 'adianti_div_content';
            
            $configs = [];
            $configs['permission'] = parse_ini_file('app/config/permission.ini');
            $configs['communication'] = parse_ini_file('app/config/communication.ini');
            $configs['log'] = parse_ini_file('app/config/log.ini');
            
            $myDatabases = [];
            $myDatabases['permission'] = 'app/database/permission.sql';
            $myDatabases['communication'] = 'app/database/comnunication.sql';
            $myDatabases['log'] = 'app/database/log.sql';
            
            $installIni = parse_ini_file('app/config/install.ini');

            foreach ($installIni['databases'] as $database) 
            {
                $configs[$database] = parse_ini_file('app/config/'.$database.'.ini');
                
                $myDatabases[$database] = "app/database/{$database}-{$configs[$database]['type']}";
            }

            $this->form = new BootstrapFormBuilder('form-download-step-1');
            $this->form->setFormTitle(_t('Installing your application'));
            
            $tstep = new TStep();
            $tstep->addItem(_t('PHP verification'), false, true);
            $tstep->addItem(_t('Directory verification'), false, true);
            $tstep->addItem(_t('Database configuration/creation'), true, false);
            
            $this->form->addContent([$tstep]);
            
            $database_types = ['mysql'=>'MySql', 'pgsql'=> 'Postgres'];

            foreach ($myDatabases as $databaseName => $database) 
            {
                $portValue = isset($configs[$databaseName]['port']) ? $configs[$databaseName]['port'] : '';
                
                $name           = new TEntry("name[]");
                $port           = new TEntry("port[]");
                $host           = new TEntry("host[]");
                $username       = new TEntry("username[]");
                $root_user      = new TEntry("root_user[]");
                $password       = new TEntry("password[]");
                $root_password  = new TEntry("root_password[]");
                $databaseType   = new TCombo("database_type[]");
                $database_name   = new THidden('database_name[]');

                $databaseType->addItems($database_types);
                
                $databaseType->setValue($configs[$databaseName]['type']);
                $port->setValue($portValue);
                $host->setValue($configs[$databaseName]['host']);
                $username->setValue($configs[$databaseName]['user']);
                $password->setValue($configs[$databaseName]['pass']);
                $name->setValue($configs[$databaseName]['name']);
                $database_name->setValue($databaseName);

                $this->form->addContent([new TFormSeparator(_t('Database').": {$databaseName}")]);
                $this->form->addFields([new TLabel(_t('Database type').':','#FF0000;')], [$databaseType],[new TLabel(_t('Database name').':','#FF0000;')],[$name]);
                $this->form->addFields([new TLabel(_t('Admin user').':','#FF0000;')], [$root_user],[new TLabel(_t('Admin password').':','#FF0000;')],[$root_password]);
                $this->form->addFields([new TLabel('Host:','#FF0000;')], [$host],[new TLabel(_t('Port').':')], [$port]);
                $this->form->addFields([new TLabel(_t('User').':','#FF0000;')], [$username],[new TLabel(_t('Password').':', '#FF0000;')], [$password, $database_name]);
                
            }
            
            TTransaction::close();
            
            $this->form->addAction(_t('Install'), new TAction([$this, 'install']), 'fa:cogs green');
            
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
    
    public static function validate($post)
    {
        $obl = [];

        $obl['name']           = _t('Database name');
        $obl['host']           = 'Host';
        $obl['username']       = _t('User');
        $obl['root_user']      = _t('Admin user');
        $obl['password']       = _t('Password');
        $obl['root_password']  = _t('Admin password');
        $obl['database_type']  = _t('Database type');
        $obl['database_name']  = _t('Database name');
        
        
        foreach ($obl as $field => $fieldName) 
        {
            foreach ($post[$field] as $key => $value) 
            {
                if(!trim($value))
                {
                    throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', '"'.$fieldName.'"').'. '._t('Of database:').$post['database_name'][$key]);
                }
            }    
        }        
    }
    
    public static function install($params)
    {
        try 
        {
            self::validate($params);
            
            $installIni = parse_ini_file('app/config/install.ini');
            $installIniText = file_get_contents('app/config/install.ini');
            
            if(isset($installIni['installed']) && $installIni['installed'])
            {
                throw new Exception(_t('Databases have already been installed'));
            }
            
            foreach ($params['name'] as $key => $name) 
            {
                self::testConnection($params['host'][$key], $params['name'][$key], $params['root_user'][$key], $params['root_password'][$key], $params['database_type'][$key], $params['port'][$key]);
            }
            
            foreach ($params['name'] as $key => $name) 
            {
                $ini = [
                    'host' => $params['host'][$key],
                    'user' => $params['root_user'][$key],
                    'pass' => $params['root_password'][$key],
                    'type' => $params['database_type'][$key],
                    'port' => $params['port'][$key]
                ];
                
                $databaseName = $params['database_name'][$key];
                
                if($ini['type'] == 'pgsql')
                {
                    $ini['name'] = 'postgres';
                }
                elseif($ini['type'] == 'mysql')
                {
                    $ini['name'] = '';
                }
                
                $conn = TConnection::openArray($ini);
                
                if(!self::userExists($params['username'][$key], $ini['type'], $conn))
                {
                    self::createUser($params['username'][$key], $params['password'][$key], $ini['type'], $conn, $params['name'][$key], $params['host'][$key]);
                }
                
                $ini['name'] = $params['name'][$key];
                
                $exisits = self::databaseExists($ini);
                
                if($exisits !== true && ($exisits->getCode() == 7 || $exisits->getCode() == 1049))
                {
                    self::createDB($params['name'][$key], $params['username'][$key], $ini['type'], $conn);
                    
                    $ini['user'] = $params['username'][$key];
                    $ini['pass'] = $params['password'][$key];
                    $ini['name'] = $params['name'][$key];
                    
                    TTransaction::open(null, $ini);
                
                    if(!file_exists("app/database/{$databaseName}.sql"))
                    {
                        self::createTables("app/database/{$databaseName}-{$ini['type']}.sql", $name, $ini['type'], $databaseName);
                    }
                    else
                    {
                        self::createTables("app/database/{$databaseName}.sql", $name, $ini['type'], $databaseName);
                    }
                    
                    TTransaction::close();
                }
                else
                {
                    TTransaction::close();
                }
                
                TTransaction::close();
                
                self::updateIniFile("app/config/{$databaseName}.ini", $ini);
                
                if($params['database_name'][$key] == 'permission')
                {
                    self::insertPermissions($params['host'][$key], $params['name'][$key], $params['username'][$key], $params['password'][$key], $params['database_type'][$key], $params['port'][$key], $params['database_name'][$key]);
                }
                elseif(is_file("app/database/{$params['database_name'][$key]}-inserts.sql"))
                {
                    self::insertDefaultData($params['host'][$key], $params['name'][$key], $params['username'][$key], $params['password'][$key], $params['database_type'][$key], $params['port'][$key], $params['database_name'][$key]);   
                }
            }
            
            new TMessage('info', _t('Databases successfully installed'));
            
            file_put_contents('app/config/install.ini', $installIniText."\ninstalled = 1");
        } 
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());    
        }
    }
    
    public static function insertDefaultData($host, $name, $user, $pass, $type, $port, $databaseName)
    {
        try 
        {            
            $ini = [
                'host' => $host,
                'name' => $name,
                'user' => $user,
                'pass' => $pass,
                'type' => $type,
                'port' => $port
            ];
            
            $inserted = parse_ini_file('app/config/installed.ini');
            
            if(!isset($inserted["{$databaseName}_{$ini['name']}_inserts"]) || (isset($inserted["{$databaseName}_{$ini['name']}_inserts"]) && $inserted["{$databaseName}_{$ini['name']}_inserts"] == 0 ))
            {
                TTransaction::open(null, $ini);
                $sql = file_get_contents("app/database/{$databaseName}-inserts.sql");
                $commands = explode(';', $sql);
                
                $conn = TTransaction::get();
                
                foreach ($commands as $sql) 
                {
                    if(trim($sql))
                        $conn->query("{$sql};");
                }
                
                $inserted["{$databaseName}_{$ini['name']}_inserts"] = 1;
                TTransaction::close();
                
            }
            
            if($inserted)
            {
                $insertedTxt = '';
                foreach ($inserted as $key => $value) 
                {
                    $insertedTxt .= "{$key} = {$value} \n";
                }
                
                file_put_contents('app/config/installed.ini', $insertedTxt);
            }  
            
            return true;
        } 
        catch (Exception $e) 
        {
            TTransaction::rollback();
            throw new Exception($e->getMessage());
        }
    }
    
    public static function insertPermissions($host, $name, $user, $pass, $type, $port, $databaseName)
    {
        try 
        {            
            $ini = [
                'host' => $host,
                'name' => $name,
                'user' => $user,
                'pass' => $pass,
                'type' => $type,
                'port' => $port
            ];
            
            $inserted = parse_ini_file('app/config/installed.ini');
            
            if(!isset($inserted["{$databaseName}_{$ini['name']}_inserts"]) || (isset($inserted["{$databaseName}_{$ini['name']}_inserts"]) && $inserted["{$databaseName}_{$ini['name']}_inserts"] == 0 ))
            {
                TTransaction::open(null, $ini);
                $sql = file_get_contents("app/database/inserts.sql");
                $commands = explode(';', $sql);
                
                $conn = TTransaction::get();
                
                echo '<pre>';
                foreach ($commands as $sql) 
                {
                    if(trim($sql))
                        $conn->query("{$sql};");
                }
                
                $inserted["{$databaseName}_{$ini['name']}_inserts"] = 1;
                TTransaction::close();
                return true;
            }
            
            if($inserted)
            {
                $insertedTxt = '';
                foreach ($inserted as $key => $value) 
                {
                    $insertedTxt .= "{$key} = {$value} \n";
                }
                
                file_put_contents('app/config/installed.ini', $insertedTxt);
            }  
        } 
        catch (Exception $e) 
        {
            TTransaction::rollback();
            throw new Exception($e->getMessage());
        }
    }
    
    public static function updateConfig($params)
    {
        try 
        {
            self::verifyRequiredFields($params);
            
            $databaseName = $params['key'];
            $ini = [
                    'host' => $params["host_{$databaseName}"],
                    'name' => $params["name_{$databaseName}"],
                    'user' => $params["username_{$databaseName}"],
                    'pass' => $params["password_{$databaseName}"],
                    'type' => $params["database_type_{$databaseName}"],
                ];
            
            self::updateIniFile("app/config/{$databaseName}.ini", $ini);
            
            new TMessage('info', _t('Configuration file: ^1 updated successfully', "app/config/{$databaseName}.ini"));    
        } 
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public static function updateIniFile($fileName, $ini)
    {
        $fileContents = '';
        foreach ($ini as $key => $value) 
        {
            $fileContents .= "{$key} = {$value} \n";
        }
        
        file_put_contents($fileName, $fileContents);
    }
    
    public static function createDatabase($params = null)
    {
        try
        {
            self::verifyRequiredFields($params);
            self::verifyAdminRequiredFields($params);
            
            $databaseName = $params['key'];
            $ini = [
                'host' => $params["host_{$databaseName}"],
                'name' => $params["name_{$databaseName}"],
                'user' => $params["root_user_{$databaseName}"],
                'pass' => $params["root_password_{$databaseName}"],
                'type' => $params["database_type_{$databaseName}"],
            ];
            
            if($params["port_{$databaseName}"])
            {
                $ini['port'] = $params["port_{$databaseName}"];
            }
            
            $user = $params["username_{$databaseName}"];
            $name = $params["name_{$databaseName}"];
            $pass = $params["password_{$databaseName}"];
            $host = $params["host_{$databaseName}"];
            
            $exisits = self::databaseExists($ini);
            
            if($exisits === true)
            {
                if($ini['type'] == 'pgsql')
                {
                    $ini['name'] = 'postgres';
                }
                elseif($ini['type'] == 'mysql')
                {
                    $ini['name'] = '';
                }
                $conn = TConnection::openArray($ini);
                if(!self::userExists($user, $ini['type'], $conn))
                {
                    self::createDatabaseUser($user, $pass, $ini['type'], $conn, $name, $host);
                }
                TTransaction::close();  
            }
            elseif($exisits->getCode() == 7 || $exisits->getCode() == 1049)
            {
                if($ini['type'] == 'pgsql')
                {
                    $ini['name'] = 'postgres';   
                    
                    $conn = TConnection::openArray($ini);
                       
                    self::createDatabaseUser($user, $pass, $ini['type'], $conn);
                    self::createDB($name, $user, $ini['type'], $conn);   
                    
                    TTransaction::close();             
                }
                if($ini['type'] == 'mysql')
                {
                    $ini['name'] = '';
                    $conn = TConnection::openArray($ini);
                    
                    self::createDB($name, $user, $ini['type'], $conn);
                    self::createDatabaseUser($user, $pass, $ini['type'], $conn, $name, $host); 
                    
                    TTransaction::close();
                }
            }
            else
            {
                throw new Exception($exisits->getMessage());          
            }
            
            return true;
        } 
        catch (Exception $e) 
        {
            throw new Exception("Ocorreu um erro ao criar a base dados ou usuário");
            TTransaction::rollback();
        }
    }
    
    public static function createDB($name, $user, $databaseType, $conn)
    {
        if($databaseType == 'pgsql')
        {
            $sql = "create database {$name} owner {$user};"; 
        }
        elseif($databaseType == 'mysql')
        {
            $sql  = "CREATE DATABASE IF NOT EXISTS {$name} DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";            
        }
        
        $result = $conn->query($sql);
    }
    
    public static function createTables($file, $databaseName, $databaseType, $iniName)
    {
        
        $sql = file_get_contents($file);
        $commands = explode(';', $sql);
        
        foreach ($commands as $command) 
        {
            if(!$command)
                continue;
            
            if(preg_match_all( '!CREATE TABLE (.*+)!i', $command, $match ) > 0)
            {
                foreach ($match[1] as $key => $table) 
                {
                    $tables[trim(explode('(',$table)[0])] = $command.';';   
                }
            }
        }
        
        $conn = TTransaction::get();
        
        $fks = [];
        $inserts = [];
        $idxs = [];
        
        foreach ($commands as $command) 
        {
            if(!$command)
                continue;
            
            if(preg_match_all( '!ADD CONSTRAINT (.+[ ?])!i', $command, $match ) > 0)
            {
                foreach ($match[1] as $key => $table) 
                {
                    $fks[trim(explode(' ',$table)[0])] = $command;
                }
            }
            
            if(preg_match("/insert into/i", $command))
                $inserts[] = $command;
            
            if(preg_match_all( '!create index (.+[ ?])!i', $command, $match ) > 0)
            {
                foreach ($match[1] as $key => $table) 
                {
                    $idxs[trim(explode(' ',$table)[0])] = $command;
                }
            }
        }
             
        foreach ($tables as $tableName => $createTableSql) 
        {
            if(!self::isTableCreated($tableName, $databaseName, $databaseType, $conn))
            {
                $result = $conn->query($createTableSql);
            }
        }
        
        if($fks)
        {
            foreach ($fks as $fkName => $createFkSql) 
            {
                if(!self::isFkCreated($fkName, $databaseName, $databaseType, $conn))
                {
                    $result = $conn->query($createFkSql);
                }
            }
        }
        
        if($idxs)
        {
            foreach ($idxs as $idxName => $createIdxSql) 
            {
                if(!self::isIndexCreated($idxName, $databaseName, $databaseType, $conn))
                {
                    $result = $conn->query($createIdxSql);
                }
            }
        }
        
        $inserted = parse_ini_file('app/config/installed.ini');

        if($inserts)
        {
            if(!isset($inserted["{$iniName}_{$databaseName}"]) || (isset($inserted["{$iniName}_{$databaseName}"]) && $inserted["{$iniName}_{$databaseName}"] == 0))
            {
                foreach ($inserts as $insertSql) 
                {
                    if(trim($insertSql))
                        $result = $conn->query($insertSql);
                }
                
                $inserted["{$iniName}_{$databaseName}"] = '1';
            }
        }
        else
        {
            $inserted["{$iniName}_{$databaseName}"] = '0';
        }
         
        if($inserted)
        {
            $insertedTxt = '';
            foreach ($inserted as $key => $value) 
            {
                $insertedTxt .= "{$key} = {$value} \n";
            }
            
            file_put_contents('app/config/installed.ini', $insertedTxt);
        }   
    }
    
    public static function isIndexCreated($idxName, $databaseName, $databaseType, $conn)
    {
        if($databaseType == 'pgsql')
        {
            $sql = "select * from pg_catalog.pg_indexes where indexname = '{$idxName}';";
        }
        elseif($databaseType == 'mysql')
        {
            $sql = "select * from information_schema.statistics where table_schema = '{$databaseName}' and index_name = '{$idxName}'";
        }
        
        $result = $conn->query($sql);
        $objs = $result->fetchAll(PDO::FETCH_CLASS, "stdClass");
        
        if($objs)
            return true;
        
        return false;
    }
    
    public static function isTableCreated($tableName, $databaseName, $databaseType, $conn)
    {
        if($databaseType == 'pgsql')
        {
            $sql = "select * from information_schema.tables where table_name = '{$tableName}' and table_catalog = '{$databaseName}';";    
        }
        elseif($databaseType == 'mysql')
        {
            $sql = "select * from information_schema.tables where table_name = '{$tableName}' and table_schema = '{$databaseName}'";
        }
        
        $result = $conn->query($sql);
        $objs = $result->fetchAll(PDO::FETCH_CLASS, "stdClass");
        
        if($objs)
            return true;
        
        return false;
    }
    
    public static function isFkCreated($fkName, $databaseName, $databaseType, $conn)
    {
        if($databaseType == 'pgsql')
        {
            $sql = "select * from information_schema.table_constraints where constraint_name = '{$fkName}'";
        }
        elseif($databaseType == 'mysql')
        {
            $sql = "select * from information_schema.table_constraints where constraint_name = '{$fkName}' and constraint_schema = '{$databaseName}';";
        }
        
        $result = $conn->query($sql);
        $objs = $result->fetchAll(PDO::FETCH_CLASS, "stdClass");
        
        if($objs)
            return true;
        
        return false;
    }
        
    public static function createUser($user, $password, $databaseType, $conn, $databaseName = null, $host = null)
    {
        if($databaseType == 'pgsql')
        {        
            $sql = "select * from pg_roles where rolname = '{$user}' "; 
            $result = $conn->query($sql);
            $objs = $result->fetchAll(PDO::FETCH_CLASS, "stdClass");
            
            if(!$objs)
            {
                $sql = "create user {$user} with encrypted password '{$password}';"; 
                $result = $conn->query($sql);
            }
        }
        elseif($databaseType == 'mysql')
        {
            $sql = '';
            $sql .= "GRANT ALL PRIVILEGES ON {$databaseName}.* TO '{$user}'@'{$host}' IDENTIFIED BY '{$password}' WITH GRANT OPTION;";
            $sql .= "GRANT ALL PRIVILEGES ON {$databaseName}.* TO '{$user}'@'{$host}';";

            $result = $conn->query($sql);
        }
            
    }
    
    public static function userExists($user, $databaseType, $conn)
    {
        try 
        {
            if($databaseType == 'mysql')
            {
                $sql = "SELECT * FROM mysql.user WHERE user = '{$user}' "; 
                $result = $conn->query($sql);
                $objs = $result->fetchAll(PDO::FETCH_CLASS, "stdClass");
                
                if(!$objs)
                {
                    return false;
                }
                
                return true;
            }
        } 
        catch (Exception $e) 
        {
            return $e;
        }
    }
    
    public static function databaseExists($ini)
    {
        try 
        {
            TTransaction::open(null, $ini);    
            TTransaction::close();
            return true;
        } 
        catch (Exception $e) 
        {
            return $e;
        }
    }
    
    public static function testConnection($host, $name, $user, $pass, $type, $port)
    {
        try
        {
            $ini = [
                'host' => $host,
                'name' => $name,
                'user' => $user,
                'pass' => $pass,
                'type' => $type,
                'port' => $port
            ];
            
            if($ini['type'] == 'pgsql'){
                $ini['name'] = 'postgres';
            }
            elseif($ini['type'] == 'mysql'){
                $ini['name'] = '';
            }
                
            TTransaction::open(null, $ini);
            
            TTransaction::close();
            
            return true;
        } 
        catch (Exception $e) 
        {
            throw new Exception(_t("Connecton to database ^1 failed", '"'.$name.'"'));
        }
    }
    
    public function onShow()
    {
        
    }
    

}