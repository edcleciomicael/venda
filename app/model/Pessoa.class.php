<?php

class Pessoa extends TRecord
{
    const TABLENAME  = 'pessoa';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('documento');
        parent::addAttribute('fone');
        parent::addAttribute('email');
        parent::addAttribute('rua');
        parent::addAttribute('numero');
        parent::addAttribute('bairro');
        parent::addAttribute('complemento');
        parent::addAttribute('cep');
        parent::addAttribute('obs');
        parent::addAttribute('cidade_id');
		parent::addAttribute('dt_nascimento');
		parent::addAttribute('uf');
		parent::addAttribute('rg');
		parent::addAttribute('qtd_dias');
		parent::addAttribute('insc_estadual');
        parent::addAttribute('situacao');
    }

}

