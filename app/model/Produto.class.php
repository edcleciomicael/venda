<?php

class Produto extends TRecord
{
    const TABLENAME  = 'produto';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}
    
    use SystemChangeLogTrait;
    
    private $fornecedor;
    private $tipo_produto;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo_produto_id');
        parent::addAttribute('fornecedor_id');
        parent::addAttribute('nome');
        parent::addAttribute('codigo_barras');
        parent::addAttribute('dt_cadastro');
        parent::addAttribute('preco_custo');
        parent::addAttribute('preco_venda');
        parent::addAttribute('qtde_estoque');
        parent::addAttribute('obs');
    }

    /**
     * Method set_pessoa
     * Sample of usage: $var->pessoa = $object;
     * @param $object Instance of Pessoa
     */
    public function set_fornecedor(Pessoa $object)
    {
        $this->fornecedor = $object;
        $this->fornecedor_id = $object->id;
    }
    
    /**
     * Method get_fornecedor
     * Sample of usage: $var->fornecedor->attribute;
     * @returns Pessoa instance
     */
    public function get_fornecedor()
    {
        
        // loads the associated object
        if (empty($this->fornecedor))
            $this->fornecedor = new Pessoa($this->fornecedor_id);
        
        // returns the associated object
        return $this->fornecedor;
    }
    /**
     * Method set_tipo_produto
     * Sample of usage: $var->tipo_produto = $object;
     * @param $object Instance of TipoProduto
     */
    public function set_tipo_produto(TipoProduto $object)
    {
        $this->tipo_produto = $object;
        $this->tipo_produto_id = $object->id;
    }
    
    /**
     * Method get_tipo_produto
     * Sample of usage: $var->tipo_produto->attribute;
     * @returns TipoProduto instance
     */
    public function get_tipo_produto()
    {
        
        // loads the associated object
        if (empty($this->tipo_produto))
            $this->tipo_produto = new TipoProduto($this->tipo_produto_id);
        
        // returns the associated object
        return $this->tipo_produto;
    }
}

