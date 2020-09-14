# Multibanco WHMCS

1. Extract the files to the root of WHMCS
2. Enable the Gateaway:
  * Setup -> Payments -> Payment Gateaways
  * All Payment Gateaways
  * Click on Pagamentos Por Multibanco
  * Insert the following value on URL field, for production "https://hm.comprafacil.pt/SIBSClick2/webservice/" or for test / sandbox "https://hm.comprafacil.pt/SIBSClick2teste/webservice/"
  * Insert your username on Username field
  * Insert your password on Password field
  * Save Changes


##### Add the Multibanco reference to Invoice PDF

1. Edit the file of your template invoicepdf.tpl
2. Add to these file the following code at the end

```
#HiPay CompraFacil multibanco
$result = select_query('tblmultibanco', 'entity, reference, value', array('orderId' => $invoicenum, 'pagName' => $paymentmethod));
$data = mysql_fetch_array($result);

if (sizeof($data) > 2 && $status != 'Paid') {
  $entity = $data['entity'];
  $reference = $data['reference'];
  $value = $data['value'];

  $pdf->Ln(5);
  $tblhtml = '<div>
    <table border="0" cellpadding="0" cellspacing="0" width="110px;" >
      <tbody>
        <tr>
          <td valign="top" style="border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px;"><img src="[YOUR URL]/modules/gateways/mb.gif" border="0"></td>
          <td valign="middle" width="100%" style="padding-left: 10px; border-bottom: solid 1px #222; padding-top: 5px; padding-bottom: 5px; "><strong>Multibanco</strong></td>
        </tr>
        <tr>
          <td valign="top" align="left" style="border-bottom: solid 1px #222; padding-top: 2px; padding-bottom: 2px;"><strong>Entidade:</strong></td>
          <td valign="top" align="right" style="border-bottom: solid 1px #222; padding-top: 2px; padding-bottom: 2px; ">'.$entity.'</td>
        </tr>
        <tr>
          <td valign="top" align="left" style="border-bottom: solid 1px #222; padding-top: 2px; padding-bottom: 2px;"><strong>Refer&ecirc;ncia:</strong></td>
          <td valign="top" align="right" style="border-bottom: solid 1px #222; padding-top: 2px; padding-bottom: 2px;">'.substr($reference, 0, 3).' '.substr($reference, 3, 3).' '.substr($reference, 6, 3).'</td>
        </tr>
        <tr>
          <td valign="top" align="left" style="border-bottom: solid 1px #222; padding-top: 2px; padding-bottom: 2px; "><strong>Valor:</strong></td>
          <td valign="top" align="right" style="border-bottom: solid 1px #222; padding-top: 2px; padding-bottom: 2px; ">&euro;&nbsp;'.$value.'</td>
        </tr>
      </tbody>
    </table>
  </div>';

  $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

```

and replace [YOUR URL]
