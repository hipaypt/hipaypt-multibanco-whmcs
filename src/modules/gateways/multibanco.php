<?php
/**
 * Multibanco Hipay Module to WHMCS
 *
 *
 */

/**
 * Configurations
 */
function multibanco_config() {
  $configarray = array(
    'FriendlyName' => array('Type' => 'System', 'Value' => 'Pagamentos Por Multibanco'),
    'Url' => array('FriendlyName' => 'URL', 'Type' => 'text', 'Size' => '260', 'Description' => '',),
    'Username' => array('FriendlyName' => 'Username', 'Type' => 'text', 'Size' => '200', 'Description' => '',),
    'Password' => array('FriendlyName' => 'Password', 'Type' => 'text', 'Size' => '200', 'Description' => '</br>Url a colocar na backoffice do CompraFacil para notificação: '. GetUrl() .'modules/gateways/callback/multibanco.php'),
    'TimeLimitDays' => array ('FriendlyName' => 'Limite da referência', "Type" => "dropdown", "Options" => '1,3,30,90', 'Default' =>  '90', ),
  );

  return $configarray;
}

/**
 * Get system URL
 */
function GetUrl(){
  $result = select_query('tblconfiguration', 'value', array('setting' => 'SystemURL'));
  $data = mysql_fetch_array($result);
  $value = $data['value'];

  return $value;
}

/**
 * Generate multibanco text
 */
function multibanco_link($params) {
  // Load web inc
  include('soap/webservice.inc');

  $reference     = NULL;
  $entity        = NULL;
  $error         = NULL;
  $url           = $params['Url'];
  $username      = $params['Username'];
  $password      = $params['Password'];
  $value         = $params['amount'];
  $callback_url  = GetUrl() .'modules/gateways/callback/multibanco.php?id='. $params['invoiceid'];
  $time          = time();
  $information   = $params['invoiceid'];
  $TimeLimitDays = $params['TimeLimitDays'];

  $res = getReferenceFromWebService($url, $callback_url, $username, $password, $value, $information, '', '', '', '', '', $information, '', '', -1, $TimeLimitDays, false, $reference, $entity, $value, $error);

  if ($res) {
    # Enter your code submit to the gateway...
    $code = '
    <div style="width: 200px;	color: #666; font-size: 11px; line-height: 12px; padding: 10px;	border: solid 1px #222;">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
          <tr>
            <td valign="top" style="border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px;">
              <img src="'. GetUrl() .'modules/gateways/mb.gif" border="0">
            </td>
            <td valign="middle" width="100%" style="padding-left: 10px; border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px; font-size: 12px; font-family: Verdana;">
              Pagamento por Multibanco
            </td>
          </tr>
        </tbody>
      </table>
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
          <tr>
            <td valign="top" align="left" style="border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px; font-size: 11px; font-family: Verdana;">
              <strong>Entidade:</strong>
            </td>
            <td valign="top" align="right" style="border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px; font-size: 11px; font-family: Verdana;">
              '. $entity .'
            </td>
          </tr>
          <tr>
            <td valign="top" align="left" style="border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px; font-size: 11px; font-family: Verdana;">
              <strong>Refer&ecirc;ncia:</strong>
            </td>
            <td valign="top" align="right" style="border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px; font-size: 11px; font-family: Verdana;">
              '. $reference .'
            </td>
          </tr>
          <tr>
            <td valign="top" align="left" style="border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px; font-size: 11px; font-family: Verdana;">
              <strong>Valor:</strong>
            </td>
            <td valign="top" align="right" style="border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px; font-size: 11px; font-family: Verdana;">
              &euro;&nbsp;'. $value .'
            </td>
          </tr>
        </tbody>
      </table>
    </div>';

    insert_hipay_references_on_database($params['name'], $entity, $reference, $value, $params['invoiceid']);
  } else {
    $code = '';
  }

  return $code;
}

/**
 * Insert on database
 */
function insert_hipay_references_on_database($pagName, $entity, $reference, $value, $orderId) {
  check_hipay_table_exist('tblmultibanco');

  $fields = 'entity';
  $where = array('pagName' => $pagName, 'entity' => $entity, 'reference' => str_replace(' ', '', $reference), 'value' => str_replace(',', '', $value), 'orderId' => $orderId);
  $result = select_query($tabela, $fields, $where);
  $data = mysql_fetch_array($result);

  if (sizeof($data) < 2) {
    if (strlen(str_replace(' ', '', $reference)) == 9) {
      $values = array('pagName' => $pagName, 'entity' => $entity, 'reference' => str_replace(' ', '', $reference), 'value' => str_replace(',', '', $value), 'orderId' => $orderId);
      $newid = insert_query('tblmultibanco', $values);
    }
  }
}

/**
 * Create table if not exists
 */
function check_hipay_table_exist($table){
  $table_exists = mysql_num_rows(mysql_query("SHOW TABLES LIKE '". $table ."'"));

  if ($table_exists == 0) {
   $create_table = 'CREATE TABLE IF NOT EXISTS `'. $table .'` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `pagName` varchar(255) NOT NULL,
    `entity` varchar(5) NOT NULL,
    `reference` varchar(9) NOT NULL,
    `value` decimal(10,2) NOT NULL,
    `orderId` int(11) NOT NULL,
    `status` int(11) NOT NULL DEFAULT \'0\',
    `orderDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `paidDate` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';

  mysql_query($create_table) ;
  }
}

?>
