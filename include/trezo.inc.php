<?php

$mois_fr = array('01'=>'Janvier',
                 '02'=>'Février',
                 '03'=>'Mars',
                 '04'=>'Avril',
                 '05'=>'Mai',
                 '06'=>'Juin',
                 'O7'=>'Juillet',
                 '08'=>'Août',
                 '09'=>'Septembre',
                 '10'=>'Octobre',
                 '11'=>'Novembre',
                 '12'=>'Decembre');


$trim_fr = array('01'=>'Janvier-Mars',
		 '04'=>'Avril-Juin',
		 '07'=>'Juillet-Septembre',
		 '10'=>'Octobre-Decembre');


function isDate($date)
{
  list($d, $m, $y) = split('[/.-]', $date);
  $dummy = date("d/m/Y", mktime (0,0,0,$m,$d,$y));
  $date = ereg_replace('-', '/', $date);
  if ($dummy != $date)
    return false;
  else
    return true;
}


function solde_until($date='')
{
    $sql = "select sum(credit)-sum(debit) from trezo.operations";
    if(!empty($date))
        $sql .= " where date <= '$date'";
    $res = $globals->db->query($sql);
    list($mysolde) = mysql_fetch_row($res);
    return $mysolde;
}

?>

