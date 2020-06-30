-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 10-Mar-2019 às 20:35
-- Versão do servidor: 10.1.32-MariaDB
-- PHP Version: 7.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `venda`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `agenda`
--

CREATE TABLE `agenda` (
  `id` int(11) NOT NULL,
  `pessoa_id` int(11) NOT NULL,
  `horario_inicial` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `horario_final` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `titulo` text,
  `cor` text,
  `observacao` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `agenda`
--

INSERT INTO `agenda` (`id`, `pessoa_id`, `horario_inicial`, `horario_final`, `titulo`, `cor`, `observacao`) VALUES
(2, 7, '2019-02-05 11:00:00', '2019-02-05 12:00:00', 'financeiro', '#3a87ad', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `contato`
--

CREATE TABLE `contato` (
  `id` int(11) NOT NULL,
  `pessoa_id` int(11) NOT NULL,
  `descricao` text,
  `valor` text,
  `obs` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `estoque`
--

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `qtde` double DEFAULT NULL,
  `lote` text,
  `local` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `estoque`
--

INSERT INTO `estoque` (`id`, `produto_id`, `qtde`, `lote`, `local`) VALUES
(1, 0, 30, 'teste', 'teste2');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedido`
--

CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `dt_pedido` date DEFAULT NULL,
  `valor_total` float DEFAULT NULL,
  `obs` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `pedido`
--

INSERT INTO `pedido` (`id`, `cliente_id`, `dt_pedido`, `valor_total`, `obs`) VALUES
(3, 7, '2019-02-20', 170, 'Nenhum');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedido_item`
--

CREATE TABLE `pedido_item` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` float DEFAULT NULL,
  `valor` float DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `pedido_item`
--

INSERT INTO `pedido_item` (`id`, `pedido_id`, `produto_id`, `quantidade`, `valor`) VALUES
(2, 3, 1, 5, 25),
(3, 3, 1, 3, 15);

-- --------------------------------------------------------

--
-- Estrutura da tabela `pessoa`
--

CREATE TABLE `pessoa` (
  `id` int(11) NOT NULL,
  `nome` text,
  `documento` varchar(20) DEFAULT NULL,
  `fone` text,
  `email` text,
  `rua` text,
  `numero` int(11) DEFAULT NULL,
  `bairro` text,
  `complemento` text,
  `cep` text,
  `obs` text,
  `cidade_id` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `dt_nascimento` date DEFAULT NULL,
  `uf` varchar(2) DEFAULT NULL,
  `rg` varchar(12) DEFAULT NULL,
  `qtd_dias` varchar(11) DEFAULT NULL,
  `insc_estadual` varchar(11) DEFAULT NULL,
  `situacao` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `pessoa`
--

INSERT INTO `pessoa` (`id`, `nome`, `documento`, `fone`, `email`, `rua`, `numero`, `bairro`, `complemento`, `cep`, `obs`, `cidade_id`, `dt_nascimento`, `uf`, `rg`, `qtd_dias`, `insc_estadual`, `situacao`) VALUES
(10, 'Edclecio micael gomes de araujo', '5656262.56', '996831769', 'edclecio-micael@outlook.com', 'Rubens Mariz', 2911, 'NOSSA SENHORA DE  NAZARÉ', NULL, '59062180', NULL, 'Natal', '2019-03-08', 'RN', '2620146', '20', '55151654', '1'),
(9, 'Edclecio micael gomes de araujo', '5656262.56', '996831769', 'edclecio-micael@outlook.com', 'Rubens Mariz', 2911, 'NOSSA SENHORA DE  NAZARÉ', NULL, '59062180', NULL, 'Natal', '2019-03-08', 'RN', '2620146', '20', '55151654', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `produto`
--

CREATE TABLE `produto` (
  `id` int(11) NOT NULL,
  `tipo_produto_id` int(11) NOT NULL,
  `fornecedor_id` int(11) NOT NULL,
  `nome` text,
  `codigo_barras` text,
  `dt_cadastro` date DEFAULT NULL,
  `preco_custo` float DEFAULT NULL,
  `preco_venda` float DEFAULT NULL,
  `qtde_estoque` float DEFAULT NULL,
  `obs` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `produto`
--

INSERT INTO `produto` (`id`, `tipo_produto_id`, `fornecedor_id`, `nome`, `codigo_barras`, `dt_cadastro`, `preco_custo`, `preco_venda`, `qtde_estoque`, `obs`) VALUES
(1, 2, 7, 'Azul', '56496456416', '2019-02-09', 15, 25, 50, 'Não há');

-- --------------------------------------------------------

--
-- Estrutura da tabela `situacao`
--

CREATE TABLE `situacao` (
  `id` int(11) NOT NULL,
  `situacao` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `situacao`
--

INSERT INTO `situacao` (`id`, `situacao`) VALUES
(1, 'ATIVO'),
(2, 'INATIVO');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipo_produto`
--

CREATE TABLE `tipo_produto` (
  `id` int(11) NOT NULL,
  `nome` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `tipo_produto`
--

INSERT INTO `tipo_produto` (`id`, `nome`) VALUES
(2, 'Tecido');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contato`
--
ALTER TABLE `contato`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pedido_item`
--
ALTER TABLE `pedido_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pessoa`
--
ALTER TABLE `pessoa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `situacao`
--
ALTER TABLE `situacao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipo_produto`
--
ALTER TABLE `tipo_produto`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contato`
--
ALTER TABLE `contato`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pedido_item`
--
ALTER TABLE `pedido_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pessoa`
--
ALTER TABLE `pessoa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `produto`
--
ALTER TABLE `produto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `situacao`
--
ALTER TABLE `situacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tipo_produto`
--
ALTER TABLE `tipo_produto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
