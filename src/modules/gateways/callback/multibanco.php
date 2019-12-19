<?php

/**
 *
 *
 */

include('../../../init.php');
include('../../../includes/functions.php');
include('../../../includes/gatewayfunctions.php');
include('../../../includes/invoicefunctions.php');
include('../soap/webservice.inc');

$gatewaymodule = 'multibanco';

$GATEWAY = getGatewayVariables($gatewaymodule);
if (!$GATEWAY['type']) die('Module Not Activated');

$status = 0;

$invoiceid = '';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
}

if (isset($_GET['ref'])) {
  $ref = $_GET['ref'];
}

if (isset($_GET['paycount'])) {
  $paycount = $_GET['paycount'];
}

if (isset($ref) && isset($id) && isset($paycount)) {

  $date = date('Y-m-d H:i:s');
  $where = array('reference' => str_replace(' ', '', $ref), 'orderId' => $id, 'status' => 0);
  $result = select_query('tblmultibanco', 'orderId, value', $where, 'id', 'DESC', '0,1');
  $data = mysql_fetch_array($result);
  
  if (sizeof($data) > 1) {
    $transid = str_replace(' ', '', $ref) . $id;
    $amount = $data['value'];
    $fee = '0';

    $invoiceid = checkCbInvoiceID($id, $GATEWAY['name']); 
    checkCbTransID($transid);
    

    $paid = null;
    $status = null;
    $lastPayment = null;
    $totalPayments = null;

    $res = testReferenceFromWebService($GATEWAY['Url'], $GATEWAY['Username'], $GATEWAY['Password'], $ref, $paid, $status, $lastPayment, $totalPayments, $error);
    if ($res) {
      if ($paid == 1) {
        # Successful
        addInvoicePayment($id, $transid, $amount, $fee, $gatewaymodule);
        logTransaction($GATEWAY['name'], array($origem, $ref, $paycount), 'Sucesso: pagamento realizado com sucesso');
        update_query('tblmultibanco', array('status' => 1, 'paidDate' => $date), array('reference' => str_replace(' ', '', $ref), 'orderId' => $origem, 'status' => 0));
      } else {
        logTransaction($GATEWAY['name'], $res, 'Erro: Ou nao existe na base de dados ou ja foi pago.');
      }
    } else {
      logTransaction($GATEWAY['name'], $res, 'Erro: Ou nao existe na base de dados ou ja foi pago.');
    }
    
  } else {
    # Unsuccessful
    logTransaction($GATEWAY['name'], array(), 'Erro: Ou nao existe na base de dados ou ja foi pago.');
  }
}
?>
