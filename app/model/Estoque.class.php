<?php
/**
 * Estoque Active Record
 * @author  <your-name-here>
 */
class Estoque extends TRecord
{
    const TABLENAME = 'estoque';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('produto_id');
        parent::addAttribute('qtde');
        parent::addAttribute('lote');
        parent::addAttribute('local');
    }


}
