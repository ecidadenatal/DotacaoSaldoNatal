<?
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

class db_dotacaosaldonatal {

  function db_dotacaosaldonatal(){
  }

  function db_dotacaosaldo_natal($nivel = 8, $tipo_nivel = 1, $tipo_saldo = 2, $descr = true, $where = '', $anousu = null, $dataini = null, $datafim = null, $primeiro_fim = 8, $segundo_inicio = 0, $retsql = false, $tipo_balanco = 1, $desmembra_segundo_inicio = true, $subelemento = 'nao') {

  if ($anousu == null)
    $anousu = db_getsession("DB_anousu");

  if ($dataini == null)
    $dataini = date('Y-m-d', db_getsession('DB_datausu'));

  if ($datafim == null)
    $datafim = date('Y-m-d', db_getsession('DB_datausu'));

  if ($where != '') {
    $condicao = " and ".$where;
  } else {
    $condicao = "";
  }

  if ($tipo_balanco == 1) {
    $tipo_pa = 'dot_ini';
  }elseif ($tipo_balanco == 2) {
    $tipo_pa = 'empenhado - anulado';
  } elseif ($tipo_balanco == 3) {
    $tipo_pa = 'liquidado';
  } else {
    $tipo_pa = 'pago';
  }

  //#00#//db_dotacaosaldo
  //#10#//Esta funcao retorna o recordset do saldo das dotações
  //#15#//db_dotacaosaldo($nivel=8, $tipo_nivel=1, $tipo_saldo=2, $descr=true, $where='', $anousu=null, $dataini=null, $datafim=null)
  //#20#//$nivel      : Até qual o nível será apurado o saldo, pode ser:
  //#20#//              1 - órgão
  //#20#//              2 - unidade
  //#20#//              3 - função
  //#20#//              4 - subfuncao
  //#20#//              5 - programa
  //#20#//              6 - projeto de atividade
  //#20#//              7 - elemento
  //#20#//              8 - recurso
  //#20#//
  //#20#//              ex. quando solicitar nivel=8 usar tipo_nivel=2 para evitar duplicação de valores
  //#20#//
  //#20#//$tipo_nivel : especifica a maneira de como será apurado o resultado, pode ser:
  //#20#//              1 - traz a árvore de elementos até o nível solicitado
  //#20#//                  Ex.: 01                  300
  //#20#//                       01.01               100
  //#20#//                       01.01.01             50
  //#20#//              2 - traz o saldo do nível escolhido
  //#20#//                  Ex.: 01.01.01             50
  //#20#//              3 - totaliza o saldo pelo nível escolhido
  //#20#//                  Ex.: 00.00.01           1000
  //#20#//
  //#20#//
  //#20#//$tipo_saldo : 1 - dotação inicial
  //#20#//              2 - saldo no mes da dataini
  //#20#//              3 - saldo por período
  //#20#//              4 - saldo por período + acumulado do período
  //#20#//
  //#20#//$descr      : retorna o record set com as descrições ou não, o default é 'true'
  //#20#//
  //#20#//$where      : condição
  //#20#//
  //#20#//$anousu     : ano do orçamento
  //#20#//
  //#20#//$dataini    : data inicial do intervalo
  //#20#//
  //#20#//$datafim    : data final do intervalo
  //#20#//
  //#20#//$subelemento : algumas funcoes do pad usam esse parametro como "sim",para orcamentos no desdobramento
  //#20#//
  //#20#//
  //#20#//
  //#99#//
  //#99#//  dot_ini   			: datacao inicial (valor do orcamento)
  //#99#//  saldo_anterior		: saldo anterior ao intervalo de tempo
  //#99#//  empenhado			: empenhado no intervalo
  //#99#//  anulado			: anulado no intervalo
  //#99#//  liquidado			: liquidado no intervalo
  //#99#//  pago				: pago no intervalo
  //#99#//  suplementado			: suplementado no intervalo
  //#99#//  reduzido			: reduzido no intervalo
  //#99#//  atual				: saldo atual
  //#99#//  reservado			: reservado
  //#99#//  atual_menos_reservado		: saldo atual menos o reservado
  //#99#//  atual_a_pagar			:
  //#99#//  atual_a_pagar_liquidado
  //#99#//  empenhado_acumulado
  //#99#//  anulado_acumulado
  //#99#//  liquidado_acumulado
  //#99#//  pago_acumulado
  //#99#//  suplementado_acumulado
  //#99#//  reduzido_acumulado
  //#99#//
  //#99#//
  //#99#//
  //#99#//
  //#99#//
  //#99#//
  // funcao para gerar work
  // db_query('begin');
  //   substr(o56_elemento,1,7) as o58_elemento,
  //   9999999 as o58_coddot,

  db_query("drop table if exists work_dotacao;");
  $sql = "
     CREATE TEMP TABLE IF NOT EXISTS work_dotacao (
       o58_anousu integer,
       o58_orgao integer,
       o58_unidade integer,
       o58_funcao integer,
       o58_subfuncao integer,
       o58_programa integer,
       o58_projativ integer,
       o58_codele integer,
       o58_coddot integer,
       o58_elemento character varying,
       o58_codigo integer,
       o58_localizadorgastos integer,
       o58_concarpeculiar character varying,
       dot_ini double precision,
       saldo_anterior double precision,
       empenhado double precision,
       anulado double precision,
       liquidado double precision,
       pago double precision,
       suplementado double precision,
       reduzido double precision,
       atual double precision,
       reservado double precision,
       atual_menos_reservado double precision,
       atual_a_pagar double precision,
       atual_a_pagar_liquidado double precision,
       empenhado_acumulado double precision,
       anulado_acumulado double precision,
       liquidado_acumulado double precision,
       pago_acumulado double precision,
       suplementado_acumulado double precision,
       reduzido_acumulado double precision,
       suplemen double precision,
       suplemen_acumulado double precision,
       especial double precision,
       especial_acumulado double precision,
       transfsup double precision,
       transfsup_acumulado double precision,
       transfred double precision,
       transfred_acumulado double precision,
       reservado_manual_ate_data double precision,
       reservado_automatico_ate_data double precision,
       reservado_ate_data double precision,
       o55_tipo integer,
       o15_tipo integer,
       proj double precision,
       ativ double precision,
       oper double precision,
       ordinario double precision,
       vinculado double precision
     );
     TRUNCATE work_dotacao;
   ";

  $sql.="INSERT INTO work_dotacao
    select *,
    (case when o55_tipo  = 1 then $tipo_pa else 0 end) as proj,
    (case when o55_tipo  = 2 then $tipo_pa else 0 end) as ativ,
    (case when o55_tipo  = 3 then $tipo_pa else 0 end) as oper,
    (case when o15_tipo  =  1 then $tipo_pa else 0 end) as ordinario,
    (case when o15_tipo  <> 1 then $tipo_pa else 0 end) as vinculado
    from
    (select o58_anousu,
    o58_orgao,
    o58_unidade,
    o58_funcao,
    o58_subfuncao,
    o58_programa,
    o58_projativ,
    o56_codele as o58_codele,
    case when '$subelemento'='sim' then
    9999999
    else o58_coddot
    end as o58_coddot,
    case when '$subelemento'='sim'  then
    substr(o56_elemento,1,7)
    else o56_elemento
    end as o58_elemento,
    o58_codigo,
    o58_localizadorgastos,
    o58_concarpeculiar,
    substr(fc_dotacaosaldo,3,12)::float8   as dot_ini,
    substr(fc_dotacaosaldo,16,12)::float8  as saldo_anterior,
    substr(fc_dotacaosaldo,29,12)::float8  as empenhado,
    substr(fc_dotacaosaldo,42,12)::float8  as anulado,
    substr(fc_dotacaosaldo,55,12)::float8  as liquidado,
    substr(fc_dotacaosaldo,68,12)::float8  as pago,
    substr(fc_dotacaosaldo,81,12)::float8  as suplementado,
    substr(fc_dotacaosaldo,094,12)::float8 as reduzido,
    substr(fc_dotacaosaldo,107,12)::float8 as atual,
    substr(fc_dotacaosaldo,120,12)::float8 as reservado,
    substr(fc_dotacaosaldo,133,12)::float8 as atual_menos_reservado,
    substr(fc_dotacaosaldo,146,12)::float8 as atual_a_pagar,
    substr(fc_dotacaosaldo,159,12)::float8 as atual_a_pagar_liquidado,
    substr(fc_dotacaosaldo,172,12)::float8 as empenhado_acumulado,
    substr(fc_dotacaosaldo,185,12)::float8 as anulado_acumulado,
    substr(fc_dotacaosaldo,198,12)::float8 as liquidado_acumulado,
    substr(fc_dotacaosaldo,211,12)::float8 as pago_acumulado,
    substr(fc_dotacaosaldo,224,12)::float8 as suplementado_acumulado,
    substr(fc_dotacaosaldo,237,12)::float8 as reduzido_acumulado,
    substr(fc_dotacaosaldo,250,12)::float8 as suplemen,
    substr(fc_dotacaosaldo,263,12)::float8 as suplemen_acumulado,
    substr(fc_dotacaosaldo,276,12)::float8 as especial,
    substr(fc_dotacaosaldo,289,12)::float8 as especial_acumulado,
		substr(fc_dotacaosaldo,303,12)::float8 as transfsup,
		substr(fc_dotacaosaldo,316,12)::float8 as transfsup_acumulado,
		substr(fc_dotacaosaldo,329,12)::float8 as transfred,
		substr(fc_dotacaosaldo,342,12)::float8 as transfred_acumulado,
		substr(fc_dotacaosaldo,355,12)::float8 as reservado_manual_ate_data,
		substr(fc_dotacaosaldo,368,12)::float8 as reservado_automatico_ate_data,
		substr(fc_dotacaosaldo,381,12)::float8 as reservado_ate_data,
		o55_tipo,
    o15_tipo
    from(select *, fc_dotacaosaldo($anousu,o58_coddot,$tipo_saldo,'$dataini','$datafim')
    from orcdotacao w
    inner join orcelemento e   on w.o58_codele   = e.o56_codele
                              and e.o56_anousu = w.o58_anousu
                              and e.o56_orcado is true
    inner join orcprojativ ope on w.o58_projativ = ope.o55_projativ
                              and ope.o55_anousu = w.o58_anousu
    inner join orctiporec      on orctiporec.o15_codigo = w.o58_codigo

    where o58_anousu = $anousu
    $condicao
    order by
    o58_orgao,
    o58_unidade,
    o58_funcao,
    o58_subfuncao,
    o58_programa,
    o58_projativ,
    o56_codele,
    o56_elemento,
    o58_coddot,
    o58_codigo
    ) as x
    ) as xxx
    ";

  $result1 = db_query($sql);

  /////// nivel 8 ///////////////

  if (8 <= $primeiro_fim || ($desmembra_segundo_inicio == true && $segundo_inicio <= 8 || $desmembra_segundo_inicio == false && $segundo_inicio == 8)) {
    $xnivel8 = '';
    if ($nivel >= 8) {
      if ($tipo_nivel == 1 || $tipo_nivel == 2) {
        $xnivel8 ="o58_orgao,
          o58_unidade,
          o58_funcao,
          o58_subfuncao,
          o58_programa,
          o58_projativ,
          o58_codele,
          o58_elemento,
          o58_coddot, ";
      }
      elseif ($tipo_nivel == 3) {
        $xnivel8 = "   -1 as o58_orgao,
          -1 as o58_unidade,
          -1 as o58_funcao,
          -1 as o58_subfuncao,
          -1 as o58_programa,
          -1 as o58_projativ,
          -1 as o58_codele,
          ''::varchar as o58_elemento,
          -1 as o58_coddot, ";
      }
    }
    $nivel8 = "select
      $xnivel8
      o58_codigo,
      o58_localizadorgastos,
      o58_concarpeculiar,
      dot_ini,
      saldo_anterior,
      empenhado,
      anulado,
      liquidado,
      pago,
      suplementado,
      reduzido,
      atual,
      reservado,
      atual_menos_reservado,
      atual_a_pagar,
      atual_a_pagar_liquidado,
      empenhado_acumulado,
      anulado_acumulado,
      liquidado_acumulado,
      pago_acumulado,
      suplementado_acumulado,
      reduzido_acumulado,
      proj,
      ativ,
      oper,
      ordinario,
      vinculado,
      suplemen,
      suplemen_acumulado,
      especial,
      especial_acumulado,
      reservado_manual_ate_data,
      reservado_automatico_ate_data,
      reservado_ate_data
      from (
      select
      $xnivel8
      o58_codigo,
      o58_localizadorgastos,
      o58_concarpeculiar,
      sum(dot_ini)																		as dot_ini,
      sum(saldo_anterior)															as saldo_anterior,
      sum(empenhado)																	as empenhado,
      sum(anulado)																		as anulado,
      sum(liquidado)																	as liquidado,
      sum(pago)																				as pago,
      sum(suplementado+transfsup)											as suplementado,
      sum(reduzido+transfred)													as reduzido,
      sum(atual)																			as atual,
      sum(reservado)																	as reservado,
      sum(atual_menos_reservado)											as atual_menos_reservado,
      sum(atual_a_pagar)															as atual_a_pagar,
      sum(atual_a_pagar_liquidado)										as atual_a_pagar_liquidado,
      sum(empenhado_acumulado)												as empenhado_acumulado,
      sum(anulado_acumulado)													as anulado_acumulado,
      sum(liquidado_acumulado)												as liquidado_acumulado,
      sum(pago_acumulado)															as pago_acumulado,
      sum(suplementado_acumulado+transfsup_acumulado) as suplementado_acumulado,
      sum(reduzido_acumulado+transfred_acumulado)			as reduzido_acumulado,
      sum(proj)																				as proj,
      sum(ativ)																				as ativ,
      sum(oper)																				as oper,
      sum(ordinario)																	as ordinario,
      sum(vinculado)																	as vinculado,
      sum(suplemen+transfsup)													as suplemen,
      sum(suplemen_acumulado+transfsup_acumulado)		  as suplemen_acumulado,
      sum(especial)																		as especial,
      sum(especial_acumulado)													as especial_acumulado,
      sum(reservado_manual_ate_data)									as reservado_manual_ate_data,
      sum(reservado_automatico_ate_data)							as reservado_automatico_ate_data,
      sum(reservado_ate_data)                         as reservado_ate_data
      from work_dotacao
      group by ";
    if ($tipo_nivel != 3) {
      $nivel8 .= "o58_orgao,
        o58_unidade,
        o58_funcao,
        o58_subfuncao,
        o58_programa,
        o58_projativ,
        o58_codele,
        o58_elemento,
        o58_coddot ,";
    }
    $nivel8 .= "o58_codigo,
      o58_localizadorgastos,
      o58_concarpeculiar
      ) as i";

  } else {
    $nivel8 = '';
  }

  /////// nivel 7 ///////////////

  if (7 <= $primeiro_fim || ($desmembra_segundo_inicio == true && $segundo_inicio <= 7 || $desmembra_segundo_inicio == false && $segundo_inicio == 7)) {
    $xnivel7 = '';
    if ($nivel >= 7) {
      if ($tipo_nivel == 1 || $tipo_nivel == 2) {
        $xnivel7 = " o58_orgao,
          o58_unidade,
          o58_funcao,
          o58_subfuncao,
          o58_programa,
          o58_projativ,";
      }
      elseif ($tipo_nivel == 3) {
        $xnivel7 = "   -1 as o58_orgao,
          -1 as o58_unidade,
          -1 as o58_funcao,
          -1 as o58_subfuncao,
          -1 as o58_programa,
          -1 as o58_projativ,";
      }
    }
    $nivel7 = "select
      $xnivel7
      o58_codele,
      o58_elemento,
      0 as o58_coddot,
      0 as o58_codigo,
      0 as o58_localizadorgastos,
      ''::varchar as o58_concarpeculiar,
      dot_ini,
      saldo_anterior,
      empenhado,
      anulado,
      liquidado,
      pago,
      suplementado,
      reduzido,
      atual,
      reservado,
      atual_menos_reservado,
      atual_a_pagar,
      atual_a_pagar_liquidado,
      empenhado_acumulado,
      anulado_acumulado,
      liquidado_acumulado,
      pago_acumulado,
      suplementado_acumulado,
      reduzido_acumulado,
      proj,
      ativ,
      oper,
      ordinario,
      vinculado,
      suplemen,
      suplemen_acumulado,
      especial,
      especial_acumulado,
      reservado_manual_ate_data,
      reservado_automatico_ate_data,
      reservado_ate_data
      from (
      select
      $xnivel7
      o58_codele,
      o58_elemento,
      sum(dot_ini)																		as dot_ini,
      sum(saldo_anterior)															as saldo_anterior,
      sum(empenhado)																	as empenhado,
      sum(anulado)																		as anulado,
      sum(liquidado)																	as liquidado,
      sum(pago)																				as pago,
      sum(suplementado+transfsup)											as suplementado,
      sum(reduzido+transfred)													as reduzido,
      sum(atual)																			as atual,
      sum(reservado)																	as reservado,
      sum(atual_menos_reservado)											as atual_menos_reservado,
      sum(atual_a_pagar)															as atual_a_pagar,
      sum(atual_a_pagar_liquidado)										as atual_a_pagar_liquidado,
      sum(empenhado_acumulado)												as empenhado_acumulado,
      sum(anulado_acumulado)													as anulado_acumulado,
      sum(liquidado_acumulado)												as liquidado_acumulado,
      sum(pago_acumulado)															as pago_acumulado,
      sum(suplementado_acumulado+transfsup_acumulado) as suplementado_acumulado,
      sum(reduzido_acumulado+transfred_acumulado)		  as reduzido_acumulado,
      sum(proj)																				as proj,
      sum(ativ)																				as ativ,
      sum(oper)																				as oper,
      sum(ordinario)																	as ordinario,
      sum(vinculado)																	as vinculado,
      sum(suplemen+transfsup)												  as suplemen,
      sum(suplemen_acumulado+transfsup_acumulado)			as suplemen_acumulado,
      sum(especial)																		as especial,
      sum(especial_acumulado)													as especial_acumulado,
      sum(reservado_manual_ate_data)									as reservado_manual_ate_data,
      sum(reservado_automatico_ate_data)              as reservado_automatico_ate_data,
      sum(reservado_ate_data)                         as reservado_ate_data
      from work_dotacao
      group by ";
    if ($tipo_nivel != 3) {
      $nivel7 .= "o58_orgao,
        o58_unidade,
        o58_funcao,
        o58_subfuncao,
        o58_programa,
        o58_projativ,";
    }
    $nivel7 .= "   o58_codele,
      o58_elemento
      ) as g";

  } else {
    $nivel7 = '';
  }

  /////// nivel 6 ///////////////

  if (6 <= $primeiro_fim || ($desmembra_segundo_inicio == true && $segundo_inicio <= 6 || $desmembra_segundo_inicio == false && $segundo_inicio == 6)) {
    $xnivel6 = '';
    if ($nivel >= 6) {
      if ($tipo_nivel == 1 || $tipo_nivel == 2) {
        $xnivel6 = " o58_orgao,
          o58_unidade,
          o58_funcao,
          o58_subfuncao,
          o58_programa,";
      }
      elseif ($tipo_nivel == 3) {
        $xnivel6 = "-1 as o58_orgao,
          -1 as o58_unidade,
          -1 as o58_funcao,
          -1 as o58_subfuncao,
          -1 as o58_programa,";
      }
    }
    $nivel6 = "select
      $xnivel6
      o58_projativ,
      -1 as o58_codele,
      ''::varchar as o58_elemento,
      -1 as o58_coddot,
      -1 as o58_codigo,
      -1 as o58_localizadorgastos,
      ''::varchar as o58_concarpeculiar,
      dot_ini,
      saldo_anterior,
      empenhado,
      anulado,
      liquidado,
      pago,
      suplementado,
      reduzido,
      atual,
      reservado,
      atual_menos_reservado,
      atual_a_pagar,
      atual_a_pagar_liquidado,
      empenhado_acumulado,
      anulado_acumulado,
      liquidado_acumulado,
      pago_acumulado,
      suplementado_acumulado,
      reduzido_acumulado,
      proj,
      ativ,
      oper,
      ordinario,
      vinculado,
      suplemen,
      suplemen_acumulado,
      especial,
      especial_acumulado,
      reservado_manual_ate_data,
      reservado_automatico_ate_data,
      reservado_ate_data
      from (
      select
      $xnivel6
      o58_projativ,
      sum(dot_ini)                       as dot_ini,
      sum(saldo_anterior)                as saldo_anterior,
      sum(empenhado)                     as empenhado,
      sum(anulado)                       as anulado,
      sum(liquidado)                     as liquidado,
      sum(pago)                          as pago,
      sum(suplementado)                  as suplementado,
      sum(reduzido)                      as reduzido,
      sum(atual)                         as atual,
      sum(reservado)                     as reservado,
      sum(atual_menos_reservado)         as atual_menos_reservado,
      sum(atual_a_pagar)                 as atual_a_pagar,
      sum(atual_a_pagar_liquidado)       as atual_a_pagar_liquidado,
      sum(empenhado_acumulado)           as empenhado_acumulado,
      sum(anulado_acumulado)             as anulado_acumulado,
      sum(liquidado_acumulado)           as liquidado_acumulado,
      sum(pago_acumulado)                as pago_acumulado,
      sum(suplementado_acumulado)        as suplementado_acumulado,
      sum(reduzido_acumulado)            as reduzido_acumulado,
      sum(proj)                          as proj,
      sum(ativ)                          as ativ,
      sum(oper)                          as oper,
      sum(ordinario)                     as ordinario,
      sum(vinculado)                     as vinculado,
      sum(suplemen)                      as suplemen,
      sum(suplemen_acumulado)            as suplemen_acumulado,
      sum(especial)                      as especial,
      sum(especial_acumulado)            as especial_acumulado,
      sum(reservado_manual_ate_data)     as reservado_manual_ate_data,
      sum(reservado_automatico_ate_data) as reservado_automatico_ate_data,
      sum(reservado_ate_data)            as reservado_ate_data
      from work_dotacao
      group by ";
    if ($tipo_nivel != 3) {
      $nivel6 .= "o58_orgao,
        o58_unidade,
        o58_funcao,
        o58_subfuncao,
        o58_programa, ";
    }
    $nivel6 .= "   o58_projativ
      ) as f";

  } else {
    $nivel6 = '';
  }

  /////// nivel 5 ///////////////

  if (5 <= $primeiro_fim || ($desmembra_segundo_inicio == true && $segundo_inicio <= 5 || $desmembra_segundo_inicio == false && $segundo_inicio == 5)) {
    $xnivel5 = '';
    if ($nivel >= 5) {
      if ($tipo_nivel == 1 || $tipo_nivel == 2) {
        $xnivel5 = "o58_orgao,
          o58_unidade,
          o58_funcao,
          o58_subfuncao,";
      }
      elseif ($tipo_nivel == 3) {
        $xnivel5 ="-1 as o58_orgao,
          -1 as o58_unidade,
          -1 as o58_funcao,
          -1 as o58_subfuncao,";
      }
    }
    $nivel5 = "select
      $xnivel5
      o58_programa,
      -1 as o58_projativ,
      -1 as o58_codele,
      ''::varchar as o58_elemento,
      -1 as o58_coddot,
      -1 as o58_codigo,
      -1 as o58_localizadorgastos,
      ''::varchar as o58_concarpeculiar,
      dot_ini,
      saldo_anterior,
      empenhado,
      anulado,
      liquidado,
      pago,
      suplementado,
      reduzido,
      atual,
      reservado,
      atual_menos_reservado,
      atual_a_pagar,
      atual_a_pagar_liquidado,
      empenhado_acumulado,
      anulado_acumulado,
      liquidado_acumulado,
      pago_acumulado,
      suplementado_acumulado,
      reduzido_acumulado,
      proj,
      ativ,
      oper,
      ordinario,
      vinculado,
      suplemen,
      suplemen_acumulado,
      especial,
      especial_acumulado,
      reservado_manual_ate_data,
      reservado_automatico_ate_data,
      reservado_ate_data
      from (
      select
      $xnivel5
      o58_programa,
      sum(dot_ini)                       as dot_ini,
      sum(saldo_anterior)                as saldo_anterior,
      sum(empenhado)                     as empenhado,
      sum(anulado)                       as anulado,
      sum(liquidado)                     as liquidado,
      sum(pago)                          as pago,
      sum(suplementado)                  as suplementado,
      sum(reduzido)                      as reduzido,
      sum(atual)                         as atual,
      sum(reservado)                     as reservado,
      sum(atual_menos_reservado)         as atual_menos_reservado,
      sum(atual_a_pagar)                 as atual_a_pagar,
      sum(atual_a_pagar_liquidado)       as atual_a_pagar_liquidado,
      sum(empenhado_acumulado)           as empenhado_acumulado,
      sum(anulado_acumulado)             as anulado_acumulado,
      sum(liquidado_acumulado)           as liquidado_acumulado,
      sum(pago_acumulado)                as pago_acumulado,
      sum(suplementado_acumulado)        as suplementado_acumulado,
      sum(reduzido_acumulado)            as reduzido_acumulado,
      sum(proj)                          as proj,
      sum(ativ)                          as ativ,
      sum(oper)                          as oper,
      sum(ordinario)                     as ordinario,
      sum(vinculado)                     as vinculado,
      sum(suplemen)                      as suplemen,
      sum(suplemen_acumulado)            as suplemen_acumulado,
      sum(especial)                      as especial,
      sum(especial_acumulado)            as especial_acumulado,
      sum(reservado_manual_ate_data)     as reservado_manual_ate_data,
      sum(reservado_automatico_ate_data) as reservado_automatico_ate_data,
      sum(reservado_ate_data)            as reservado_ate_data
      from work_dotacao
      group by ";
    if ($tipo_nivel != 3) {
      $nivel5 .= "o58_orgao,
        o58_unidade,
        o58_funcao,
        o58_subfuncao, ";
    }
    $nivel5 .= " o58_programa
      ) as e";

  } else {
    $nivel5 = '';
  }

  /////// nivel 4 ///////////////

  if (4 <= $primeiro_fim || ($desmembra_segundo_inicio == true && $segundo_inicio <= 4 || $desmembra_segundo_inicio == false && $segundo_inicio == 4)) {
    $xnivel4 = '';
    if ($nivel >= 4) {
      if ($tipo_nivel == 1 || $tipo_nivel == 2) {
        $xnivel4 = "o58_orgao,
          o58_unidade,
          o58_funcao,";
      }
      elseif ($tipo_nivel == 3) {
        $xnivel4 = "-1 as o58_orgao,
          -1 as o58_unidade,
          -1 as o58_funcao,";
      }
    }
    $nivel4 = "select
      $xnivel4
      o58_subfuncao,
      -1 as o58_programa,
      -1 as o58_projativ,
      -1 as o58_codele,
      ''::varchar as o58_elemento,
      -1 as o58_coddot,
      -1 as o58_codigo,
      -1 as o58_localizadorgastos,
      ''::varchar as o58_concarpeculiar,
      dot_ini,
      saldo_anterior,
      empenhado,
      anulado,
      liquidado,
      pago,
      suplementado,
      reduzido,
      atual,
      reservado,
      atual_menos_reservado,
      atual_a_pagar,
      atual_a_pagar_liquidado,
      empenhado_acumulado,
      anulado_acumulado,
      liquidado_acumulado,
      pago_acumulado,
      suplementado_acumulado,
      reduzido_acumulado,
      proj,
      ativ,
      oper,
      ordinario,
      vinculado,
      suplemen,
      suplemen_acumulado,
      especial,
      especial_acumulado,
      reservado_manual_ate_data,
      reservado_automatico_ate_data,
      reservado_ate_data
      from (
      select
      $xnivel4
      o58_subfuncao,
      sum(dot_ini)                       as dot_ini,
      sum(saldo_anterior)                as saldo_anterior,
      sum(empenhado)                     as empenhado,
      sum(anulado)                       as anulado,
      sum(liquidado)                     as liquidado,
      sum(pago)                          as pago,
      sum(suplementado)                  as suplementado,
      sum(reduzido)                      as reduzido,
      sum(atual)                         as atual,
      sum(reservado)                     as reservado,
      sum(atual_menos_reservado)         as atual_menos_reservado,
      sum(atual_a_pagar)                 as atual_a_pagar,
      sum(atual_a_pagar_liquidado)       as atual_a_pagar_liquidado,
      sum(empenhado_acumulado)           as empenhado_acumulado,
      sum(anulado_acumulado)             as anulado_acumulado,
      sum(liquidado_acumulado)           as liquidado_acumulado,
      sum(pago_acumulado)                as pago_acumulado,
      sum(suplementado_acumulado)        as suplementado_acumulado,
      sum(reduzido_acumulado)            as reduzido_acumulado,
      sum(proj)                          as proj,
      sum(ativ)                          as ativ,
      sum(oper)                          as oper,
      sum(ordinario)                     as ordinario,
      sum(vinculado)                     as vinculado,
      sum(suplemen)                      as suplemen,
      sum(suplemen_acumulado)            as suplemen_acumulado,
      sum(especial)                      as especial,
      sum(especial_acumulado)            as especial_acumulado,
      sum(reservado_manual_ate_data)     as reservado_manual_ate_data,
      sum(reservado_automatico_ate_data) as reservado_automatico_ate_data,
      sum(reservado_ate_data)            as reservado_ate_data
      from work_dotacao
      group by ";
    if ($tipo_nivel != 3) {
      $nivel4 .= "o58_orgao,
        o58_unidade,
        o58_funcao, ";
    }
    $nivel4 .= "o58_subfuncao
      ) as d";

  } else {
    $nivel4 = '';
  }

  if (3 <= $primeiro_fim || ($desmembra_segundo_inicio == true && $segundo_inicio <= 3 || $desmembra_segundo_inicio == false && $segundo_inicio == 3)) {

    $xnivel3 = '';
    if ($nivel >= 3) {
      if ($tipo_nivel == 1 || $tipo_nivel == 2) {
        $xnivel3 = " o58_orgao,
          o58_unidade,";
      }
      elseif ($tipo_nivel == 3) {
        $xnivel3 = " -1 as o58_orgao,
          -1 as o58_unidade,";
      }
    }
    $nivel3 = "select
      $xnivel3
      o58_funcao,
      -1 as o58_subfuncao,
      -1 as o58_programa,
      -1 as o58_projativ,
      -1 as o58_codele,
      ''::varchar as o58_elemento,
      -1 as o58_coddot,
      -1 as o58_codigo,
      -1 as o58_localizadorgastos,
      ''::varchar as o58_concarpeculiar,
      dot_ini,
      saldo_anterior,
      empenhado,
      anulado,
      liquidado,
      pago,
      suplementado,
      reduzido,
      atual,
      reservado,
      atual_menos_reservado,
      atual_a_pagar,
      atual_a_pagar_liquidado,
      empenhado_acumulado,
      anulado_acumulado,
      liquidado_acumulado,
      pago_acumulado,
      suplementado_acumulado,
      reduzido_acumulado,
      proj,
      ativ,
      oper,
      ordinario,
      vinculado,
      suplemen,
      suplemen_acumulado,
      especial,
      especial_acumulado,
      reservado_manual_ate_data,
      reservado_automatico_ate_data,
      reservado_ate_data
      from (
      select
      $xnivel3
      o58_funcao,
      sum(dot_ini)                       as dot_ini,
      sum(saldo_anterior)                as saldo_anterior,
      sum(empenhado)                     as empenhado,
      sum(anulado)                       as anulado,
      sum(liquidado)                     as liquidado,
      sum(pago)                          as pago,
      sum(suplementado)                  as suplementado,
      sum(reduzido)                      as reduzido,
      sum(atual)                         as atual,
      sum(reservado)                     as reservado,
      sum(atual_menos_reservado)         as atual_menos_reservado,
      sum(atual_a_pagar)                 as atual_a_pagar,
      sum(atual_a_pagar_liquidado)       as atual_a_pagar_liquidado,
      sum(empenhado_acumulado)           as empenhado_acumulado,
      sum(anulado_acumulado)             as anulado_acumulado,
      sum(liquidado_acumulado)           as liquidado_acumulado,
      sum(pago_acumulado)                as pago_acumulado,
      sum(suplementado_acumulado)        as suplementado_acumulado,
      sum(reduzido_acumulado)            as reduzido_acumulado,
      sum(proj)                          as proj,
      sum(ativ)                          as ativ,
      sum(oper)                          as oper,
      sum(ordinario)                     as ordinario,
      sum(vinculado)                     as vinculado,
      sum(suplemen)                      as suplemen,
      sum(suplemen_acumulado)            as suplemen_acumulado,
      sum(especial)                      as especial,
      sum(especial_acumulado)            as especial_acumulado,
      sum(reservado_manual_ate_data)     as reservado_manual_ate_data,
      sum(reservado_automatico_ate_data) as reservado_automatico_ate_data,
      sum(reservado_ate_data)            as reservado_ate_data
      from work_dotacao
      group by ";
    if ($tipo_nivel != 3) {
      $nivel3 .= "      o58_orgao,
        o58_unidade,";
    }
    $nivel3 .= "	      o58_funcao
      ) as c";

  } else {
    $nivel3 = '';
  }

  /////// nivel 2 ///////////////

  if (2 <= $primeiro_fim || ($desmembra_segundo_inicio == true && $segundo_inicio <= 2 || $desmembra_segundo_inicio == false && $segundo_inicio == 2)) {

    $nivel2 = "  select ";
    $nivel2 .= "   o58_orgao,
      o58_unidade,";
    $nivel2 .= "
      -1 as o58_funcao,
      -1 as o58_subfuncao,
      -1 as o58_programa,
      -1 as o58_projativ,
      -1 as o58_codele,
      ''::varchar as o58_elemento,
      -1 as o58_coddot,
      -1 as o58_codigo,
      -1 as o58_localizadorgastos,
      ''::varchar as o58_concarpeculiar,
      dot_ini,
      saldo_anterior,
      empenhado,
      anulado,
      liquidado,
      pago,
      suplementado,
      reduzido,
      atual,
      reservado,
      atual_menos_reservado,
      atual_a_pagar,
      atual_a_pagar_liquidado,
      empenhado_acumulado,
      anulado_acumulado,
      liquidado_acumulado,
      pago_acumulado,
      suplementado_acumulado,
      reduzido_acumulado,
      proj,
      ativ,
      oper,
      ordinario,
      vinculado,
      suplemen,
      suplemen_acumulado,
      especial,
      especial_acumulado,
      reservado_manual_ate_data,
      reservado_automatico_ate_data,
      reservado_ate_data
      from (
      select
      o58_orgao,
      o58_unidade,
      sum(dot_ini)                       as dot_ini,
      sum(saldo_anterior)                as saldo_anterior,
      sum(empenhado)                     as empenhado,
      sum(anulado)                       as anulado,
      sum(liquidado)                     as liquidado,
      sum(pago)                          as pago,
      sum(suplementado)                  as suplementado,
      sum(reduzido)                      as reduzido,
      sum(atual)                         as atual,
      sum(reservado)                     as reservado,
      sum(atual_menos_reservado)         as atual_menos_reservado,
      sum(atual_a_pagar)                 as atual_a_pagar,
      sum(atual_a_pagar_liquidado)       as atual_a_pagar_liquidado,
      sum(empenhado_acumulado)           as empenhado_acumulado,
      sum(anulado_acumulado)             as anulado_acumulado,
      sum(liquidado_acumulado)           as liquidado_acumulado,
      sum(pago_acumulado)                as pago_acumulado,
      sum(suplementado_acumulado)        as suplementado_acumulado,
      sum(reduzido_acumulado)            as reduzido_acumulado,
      sum(proj)                          as proj,
      sum(ativ)                          as ativ,
      sum(oper)                          as oper,
      sum(ordinario)                     as ordinario,
      sum(vinculado)                     as vinculado,
      sum(suplemen)                      as suplemen,
      sum(suplemen_acumulado)            as suplemen_acumulado,
      sum(especial)                      as especial,
      sum(especial_acumulado)            as especial_acumulado,
      sum(reservado_manual_ate_data)     as reservado_manual_ate_data,
      sum(reservado_automatico_ate_data) as reservado_automatico_ate_data,
      sum(reservado_ate_data)            as reservado_ate_data
      from work_dotacao
      group by
      o58_orgao,
      o58_unidade
      ) as b";

  } else {
    $nivel2 = '';
  }

  ///////  nivel 1  /////////////////

  if (1 <= $primeiro_fim || ($desmembra_segundo_inicio == true && $segundo_inicio <= 1 || $desmembra_segundo_inicio == false && $segundo_inicio == 1)) {
    $nivel1 = " select ";
    $nivel1.= "  o58_orgao,
      -1 as o58_unidade,
      -1 as o58_funcao,
      -1 as o58_subfuncao,
      -1 as o58_programa,
      -1 as o58_projativ,
      -1 as o58_codele,
      ''::varchar as o58_elemento,
      -1 as o58_coddot,
      -1 as o58_codigo,
      -1 as o58_localizadorgastos,
      ''::varchar as o58_concarpeculiar,
      dot_ini,
      saldo_anterior,
      empenhado,
      anulado,
      liquidado,
      pago,
      suplementado,
      reduzido,
      atual,
      reservado,
      atual_menos_reservado,
      atual_a_pagar,
      atual_a_pagar_liquidado,
      empenhado_acumulado,
      anulado_acumulado,
      liquidado_acumulado,
      pago_acumulado,
      suplementado_acumulado,
      reduzido_acumulado,
      proj,
      ativ,
      oper,
      ordinario,
      vinculado,
      suplemen,
      suplemen_acumulado,
      especial,
      especial_acumulado,
      reservado_manual_ate_data,
      reservado_automatico_ate_data,
      reservado_ate_data
      from (
      select o58_orgao,
      sum(dot_ini)                       as dot_ini,
      sum(saldo_anterior)                as saldo_anterior,
      sum(empenhado)                     as empenhado,
      sum(anulado)                       as anulado,
      sum(liquidado)                     as liquidado,
      sum(pago)                          as pago,
      sum(suplementado)                  as suplementado,
      sum(reduzido)                      as reduzido,
      sum(atual)                         as atual,
      sum(reservado)                     as reservado,
      sum(atual_menos_reservado)         as atual_menos_reservado,
      sum(atual_a_pagar)                 as atual_a_pagar,
      sum(atual_a_pagar_liquidado)       as atual_a_pagar_liquidado,
      sum(empenhado_acumulado)           as empenhado_acumulado,
      sum(anulado_acumulado)             as anulado_acumulado,
      sum(liquidado_acumulado)           as liquidado_acumulado,
      sum(pago_acumulado)                as pago_acumulado,
      sum(suplementado_acumulado)        as suplementado_acumulado,
      sum(reduzido_acumulado)            as reduzido_acumulado,
      sum(proj)                          as proj,
      sum(ativ)                          as ativ,
      sum(oper)                          as oper,
      sum(ordinario)                     as ordinario,
      sum(vinculado)                     as vinculado,
      sum(suplemen)                      as suplemen,
      sum(suplemen_acumulado)            as suplemen_acumulado,
      sum(especial)                      as especial,
      sum(especial_acumulado)            as especial_acumulado,
      sum(reservado_manual_ate_data)     as reservado_manual_ate_data,
      sum(reservado_automatico_ate_data) as reservado_automatico_ate_data,
      sum(reservado_ate_data)            as reservado_ate_data
      from work_dotacao
      group by o58_orgao) as a ";
  } else {
    $nivel1 = '';
  }
  $sql = '';

  if ($nivel >= 1) {
    if ($nivel1 != '') {
      $sql .= $nivel1;
      if ($tipo_nivel > 1)
        $sql = $nivel1;
    }
  }

  if ($nivel >= 2) {
    if ($nivel2 != '') {
      if ($sql != '')
        $sql .= " union all ";
      $sql .= $nivel2;
      if ($tipo_nivel > 1)
        $sql = $nivel2;
    }
  }

  if ($nivel >= 3) {
    if ($nivel3 != '') {
      if ($sql != '')
        $sql .= " union all ";
      $sql .= $nivel3;
      if ($tipo_nivel > 1)
        $sql = $nivel3;
    }
  }
  if ($nivel >= 4) {
    if ($nivel4 != '') {
      if ($sql != '')
        $sql .= " union all ";
      $sql .= $nivel4;
      if ($tipo_nivel > 1)
        $sql = $nivel4;
    }
  }
  if ($nivel >= 5) {
    if ($nivel5 != '') {
      if ($sql != '')
        $sql .= " union all ";
      $sql .= $nivel5;
      if ($tipo_nivel > 1)
        $sql = $nivel5;
    }
  }
  if ($nivel >= 6) {
    if ($nivel6 != '') {
      if ($sql != '')
        $sql .= " union all ";
      $sql .= $nivel6;
      if ($tipo_nivel > 1)
        $sql = $nivel6;
    }
  }
  if ($nivel >= 7) {
    if ($nivel7 != '') {
      if ($sql != '')
        $sql .= " union all ";
      $sql .= $nivel7;
      if ($tipo_nivel > 1)
        $sql = $nivel7;
    }
  }
  if ($nivel >= 8) {
    if ($nivel8 != '') {
      if ($sql != '')
        $sql .= " union all ";
      $sql .= $nivel8;
      if ($tipo_nivel > 1)
        $sql = $nivel8;
    }

  }

  $sql .= " order by
    o58_orgao,
    o58_unidade,
    o58_funcao,
    o58_subfuncao,
    o58_programa,
    o58_projativ,
    o58_elemento,
    o58_coddot
    ";

  //$sql = " select * from ( $sql ) as l $condicao ";
  //echo $sql;
  //$result = db_query($sql);
  //db_criatabela($result);exit;

  $xordem = '';
  $junta = '';
  // pesquisa as despesas
  if ($primeiro_fim >= 1) {
    $junta .= "case when o58_orgao = -1 then 0 else o58_orgao end as o58_orgao,o40_descr,";
    $xordem .= "o58_orgao,o40_descr,";
  }
  if ($primeiro_fim >= 2) {
    $junta .= "case when o58_unidade = -1 then 0 else o58_unidade end as o58_unidade,o41_descr,";
    $xordem .= "o58_unidade,o41_descr,";
  }
  if ($primeiro_fim >= 3) {
    $junta .= "case when o58_funcao = -1 then 0 else o58_funcao end as o58_funcao,o52_descr,";
    $xordem .= "o58_funcao,o52_descr,";
  }
  if ($primeiro_fim >= 4) {
    $junta .= "case when o58_subfuncao = -1 then 0 else o58_subfuncao end as o58_subfuncao,o53_descr,";
    $xordem .= "o58_subfuncao,o53_descr,";
  }
  if ($primeiro_fim >= 5) {
    $junta .= "case when o58_programa = -1 then 0 else o58_programa end as o58_programa,o54_descr,";
    $xordem .= "o58_programa,o54_descr,";
  }
  if ($primeiro_fim >= 6) {
    $junta .= "case when o58_projativ = -1 then 0 else o58_projativ end as o58_projativ,o55_descr,o55_finali,";
    $xordem .= "o58_projativ,o55_descr,o55_finali,";
  }
  if ($primeiro_fim >= 7) {
    $junta .= "o58_elemento,o56_descr,";
    $xordem .= "o58_elemento,o56_descr,";
  }
  if ($primeiro_fim >= 8) {
    $junta .= "case when o58_coddot = -1 then 0 else o58_coddot end as o58_coddot,case when o58_codigo = -1 then 0 else o58_codigo end as o58_codigo, o58_localizadorgastos, o58_concarpeculiar, o15_descr,";
    $xordem .= "o58_codigo,o15_descr,o58_coddot, o58_localizadorgastos, o58_concarpeculiar";
  }

  $virg = '';
  if ($primeiro_fim < 8) {
    $tu_para = false;
    if ($segundo_inicio <= 1) {
      $junta .= $virg."case when o58_orgao = -1 then 0 else o58_orgao end::integer as o58_orgao,o40_descr";
      $xordem .= "o58_orgao,o40_descr";
      if ($desmembra_segundo_inicio == false) {
        $tu_para = true;
      }
      $virg = ',';
    }
    if ($segundo_inicio <= 2 && $tu_para == false) {
      $junta .= $virg."case when o58_unidade = -1 then 0 else o58_unidade end::integer as o58_unidade,o41_descr";
      $xordem .= $virg."o58_unidade,o41_descr";
      if ($desmembra_segundo_inicio == false) {
        $tu_para = true;
      }
      $virg = ',';

    }
    if ($segundo_inicio <= 3 && $tu_para == false) {
      $junta .= $virg."case when o58_funcao = -1 then 0 else o58_funcao end::integer as o58_funcao,o52_descr";
      $xordem .= $virg."o58_funcao,o52_descr";
      if ($desmembra_segundo_inicio == false) {
        $tu_para = true;
      }
      $virg = ',';

    }
    if ($segundo_inicio <= 4 && $tu_para == false) {
      $junta .= $virg."case when o58_subfuncao = -1 then 0 else o58_subfuncao end::integer as o58_subfuncao,o53_descr";
      $xordem .= $virg."o58_subfuncao,o53_descr";
      if ($desmembra_segundo_inicio == false) {
        $tu_para = true;
      }
      $virg = ',';

    }
    if ($segundo_inicio <= 5 && $tu_para == false) {
      $junta .= $virg."case when o58_programa = -1 then 0 else o58_programa end::integer as o58_programa,o54_descr";
      $xordem .= $virg."o58_programa,o54_descr";
      if ($desmembra_segundo_inicio == false) {
        $tu_para = true;
      }
      $virg = ',';

    }
    if ($segundo_inicio <= 6 && $tu_para == false) {
      $junta .= $virg."case when o58_projativ = -1 then 0 else o58_projativ end::integer as o58_projativ,o55_descr,o55_finali";
      $xordem .= $virg."o58_projativ,o55_descr,o55_finali";
      if ($desmembra_segundo_inicio == false) {
        $tu_para = true;
      }
      $virg = ',';

    }
    if ($segundo_inicio <= 7 && $tu_para == false) {
      $junta .= $virg."o58_elemento,o56_descr";
      $xordem .= $virg."o58_elemento,o56_descr";
      if ($desmembra_segundo_inicio == false) {
        $tu_para = true;
      }
      $virg = ',';
    }
    if ($segundo_inicio <= 8 && $tu_para == false) {
      $junta .= $virg."case when o58_coddot = -1 then 0 else o58_coddot end::integer as o58_coddot,case when o58_codigo = -1 then 0 else o58_codigo end::integer as o58_codigo, o58_localizadorgastos, o58_concarpeculiar, o15_descr,";
      $xordem .= $virg."o58_codigo,o15_descr,o58_coddot, o58_localizadorgastos, o58_concarpeculiar ";
      if ($desmembra_segundo_inicio == false) {
        $tu_para = true;
      }
      $virg = ',';
    }

  }
  $junta .= $virg;

  $sql2 = "select ".$junta."
    sum(dot_ini)                       as dot_ini,
    sum(saldo_anterior)                as saldo_anterior,
    sum(empenhado)                     as empenhado,
    sum(anulado)                       as anulado,
    sum(liquidado)                     as liquidado,
    sum(pago)                          as pago,
    sum(suplementado)                  as suplementado,
    sum(reduzido)                      as reduzido,
    sum(atual)                         as atual,
    sum(reservado)                     as reservado,
    sum(atual_menos_reservado)         as atual_menos_reservado,
    sum(atual_a_pagar)                 as atual_a_pagar,
    sum(atual_a_pagar_liquidado)       as atual_a_pagar_liquidado,
    sum(empenhado_acumulado)           as empenhado_acumulado,
    sum(anulado_acumulado)             as anulado_acumulado,
    sum(liquidado_acumulado)           as liquidado_acumulado,
    sum(pago_acumulado)                as pago_acumulado,
    sum(suplementado_acumulado)        as suplementado_acumulado,
    sum(reduzido_acumulado)            as reduzido_acumulado,
    sum(proj)                          as proj,
    sum(ativ)                          as ativ,
    sum(oper)                          as oper,
    sum(ordinario)                     as ordinario,
    sum(vinculado)                     as vinculado,
    sum(suplemen)                      as suplemen,
    sum(suplemen_acumulado)            as suplemen_acumulado,
    sum(especial)                      as especial,
    sum(especial_acumulado)            as especial_acumulado,
    sum(reservado_manual_ate_data)     as reservado_manual_ate_data,
    sum(reservado_automatico_ate_data) as reservado_automatico_ate_data,
    sum(reservado_ate_data)            as reservado_ate_data
    from( ( $sql ) as xx
    left  outer join orcorgao      o on o40_anousu 	 = $anousu and o.o40_orgao = o58_orgao
    left  outer join orcunidade    u on o41_anousu 	 = $anousu and u.o41_orgao = o58_orgao and u.o41_unidade= o58_unidade
    left  outer join orcfuncao     f on f.o52_funcao 	 = o58_funcao
    left  outer join orcsubfuncao  s on o53_subfuncao 	 = o58_subfuncao
    left  outer join orcprograma   p on o54_anousu 	 = $anousu and o54_programa = o58_programa
    left  outer join orcprojativ  pa on o55_anousu 	 = $anousu and o55_projativ = o58_projativ
    left  outer join orcelemento  oe on oe.o56_elemento = o58_elemento and
    oe.o56_anousu = $anousu
    left  outer join orctiporec  otr on o15_codigo   	 = o58_codigo
    ) as x
    group by ".$xordem."
    order by ".$xordem;
    
  if ($descr == true) {
    if ($retsql == false) {
      $resultdotacao = db_query($sql2);
    } else {
      $resultdotacao = $sql2;
    }
  } else {
    if ($retsql == false) {
      $resultdotacao = db_query($sql);
    } else {
      $resultdotacao = $sql;
    }
  }
  return $resultdotacao;
}

}

?>