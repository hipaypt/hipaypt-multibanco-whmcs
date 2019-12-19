<?php

function getReferenceFromWebService($url, $origin, $username, $password, $amount, $additionalInfo, $name, $address, $postCode, $city, $NIC, $externalReference, $contactPhone, $email, $IDUserBackoffice, $timeLimitDays, $sendEmailBuyer, &$reference, &$entity, &$value, &$error) {

  try {
    $wsURL = "$url/ComprafacilWS.asmx?WSDL";

    $parameters = array(
      'origin' => $origin,
      'username' => $username,
      'password' => $password,
      'amount' => $amount,
      'additionalInfo' => $additionalInfo,
      'name' => $name,
      'address' => $address,
      'postCode' => $postCode,
      'city' => $city,
      'NIC' => $NIC,
      'externalReference' => $externalReference,
      'contactPhone' => $contactPhone,
      'email' => $email,
      'IDUserBackoffice' => $IDUserBackoffice,
      'timeLimitDays' => $timeLimitDays,
      'sendEmailBuyer' => $sendEmailBuyer		
      );

    $client = new SoapClient($wsURL);
    $res = $client->getReferenceMB($parameters); 

    if ($res->getReferenceMBResult)
    {
      $entity = $res->entity;
      $value = number_format($res->amountOut, 2);
      $reference = $res->reference;
      $error = '';
      return TRUE;
    }
    else
    {
      $error = $res->error;
      return FALSE;
    }

  }
  catch (Exception $e){
    $error = $e->getMessage();
    return FALSE;
  }
}

/**
 *
 */
function testReferenceFromWebService($url, $username, $password, $ref, &$pago, &$estado, &$dataUltimoPagamento = '', &$TotalPagamentos = '', &$error = ''){

  try {
    $wsURL = "$url/ComprafacilWS.asmx?WSDL";

    $parameters = array(
      'reference' => $ref,
      'username' => $username,
      'password' => $password
    );

    $client = new SoapClient($wsURL);

    try {
      $res = $client->__call('getInfoReference', array($parameters));
    } catch (SoapFault $fault) {
      $error = 1;
    }

    if ($res->getInfoReferenceResult) {
      $pago = $res->paid;
      $estado = $res->status;
      $dataUltimoPagamento = $res->lastPaymentDate;
      $TotalPagamentos = $res->totalPayments;
      $error = '';
      return TRUE;
    } else {
      $error = $res->error;
      return FALSE;
    }
  } catch (Exception $e) {
    $error = $e->getMessage();

    return FALSE;
  }
}
