CREATE OR ALTER VIEW NX_PRODUTO(
    PDT_CODIGO,
    PDT_DESCRICAO,
    PDT_ESTOQUE,
    PDT_PRECO,
    PDT_ATIVO)
AS
select a.codigo,
       a.descricao,
       c.estdisponivel,
       b.prpraticado,
       b.ativo

from testprodutogeral a
left outer join testproduto b on(b.empresa = '01'  and
                                 b.produto = a.codigo)
left outer join testestoque c on(c.empresa = b.empresa and
                                 c.almox   = '01'      and
                                 c.produto = b.produto)
where b.empresa = '01'
;