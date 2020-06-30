<?php

class PedidoItem extends TRecord
{
    const TABLENAME  = 'pedido_item';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}
    
    
    private $produto;
    private $pedido;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pedido_id');
        parent::addAttribute('produto_id');
        parent::addAttribute('quantidade');
        parent::addAttribute('valor');
    }

    /**
     * Method set_produto
     * Sample of usage: $var->produto = $object;
     * @param $object Instance of Produto
     */
    public function set_produto(Produto $object)
    {
        $this->produto = $object;
        $this->produto_id = $object->id;
    }
    
    /**
     * Method get_produto
     * Sample of usage: $var->produto->attribute;
     * @returns Produto instance
     */
    public function get_produto()
    {
        
        // loads the associated object
        if (empty($this->produto))
            $this->produto = new Produto($this->produto_id);
        
        // returns the associated object
        return $this->produto;
    }
    /**
     * Method set_pedido
     * Sample of usage: $var->pedido = $object;
     * @param $object Instance of Pedido
     */
    public function set_pedido(Pedido $object)
    {
        $this->pedido = $object;
        $this->pedido_id = $object->id;
    }
    
    /**
     * Method get_pedido
     * Sample of usage: $var->pedido->attribute;
     * @returns Pedido instance
     */
    public function get_pedido()
    {
        
        // loads the associated object
        if (empty($this->pedido))
            $this->pedido = new Pedido($this->pedido_id);
        
        // returns the associated object
        return $this->pedido;
    }
    
}

