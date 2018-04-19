<?php 
include (dirname('index.php') . '/../../config/config.inc.php');
include (dirname('index.php') . '/../../header.php');
include (dirname('index.php') . '/estpay.php');
//include(dirname('index.php').'/validation.php');
$estpay = new estPay();
if (!isset($_POST['HASH']) || !empty($_POST['HASH'])) {
    $domainurl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $estpay->name . '/';
    $domainurl2 = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'module/' . $estpay->name . '/';
    //begin HASH verification
    $arParams = $_POST;
    unset($arParams['HASH']);
    $hashString = "";
    foreach ($arParams as $val) {
        $hashString.= strlen($val) . $val;
    }
    $secretKey = Configuration::get('DETAILS');
    $expectedHash = hash_hmac("md5", $hashString, $secretKey);
    if ($expectedHash != $_POST["HASH"]) {
        echo "FAILED. Hash mismatch";
        die;
    }
    //end hash verification
    //Use the information below to match against your database record.
    $referans = $_POST['REFNO'];
    $tutar = $_POST['AMOUNT'];
    $kur = $_POST['CURRENCY'];
    $taksit = $_POST['INSTALLMENTS_NO'];
    $banka = $_POST['CARD_PROGRAM_NAME'];
    if ($_POST['STATUS'] == "SUCCESS") {
        $customer = new Customer((int)$cart->id_customer);
        $currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
        $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
        $estpay = new estPay();
        $estpay->validateOrder($cart->id, _PS_OS_PAYMENT_, $total, $estpay->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
        $id_order = $estpay->currentOrder;
        $insert = ('
					INSERT INTO ' . _DB_PREFIX_ . 'pstock_return
					(`id_order`, `banka`, `taksit`, `referans`, `tutar`, `kur`)
					VALUE
					("' . htmlspecialchars($id_order, ENT_QUOTES) . '",
					 "' . htmlspecialchars($banka, ENT_QUOTES) . '",
					 "' . htmlspecialchars($taksit, ENT_QUOTES) . '",
					 "' . htmlspecialchars($referans, ENT_QUOTES) . '",
					 "' . htmlspecialchars($tutar, ENT_QUOTES) . '",
					 "' . htmlspecialchars($kur, ENT_QUOTES) . '")
		');
        Db::getInstance()->Execute($insert);
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $estpay->id . '&id_order=' . $estpay->currentOrder . '&key=' . $customer->secure_key);
    } else {
        Tools::redirectLink($domainurl2 . 'payment?fail=1&amount=' . $totalAmount . '&err=' . urlencode($_POST['RETURN_MESSAGE']));
    }
} else {
    echo "FAILED2. Hash missing";
}
?>