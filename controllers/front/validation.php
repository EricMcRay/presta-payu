<?php 

class estPayValidationModuleFrontController extends ModuleFrontController {
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess() {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) Tools::redirect('index.php?controller=order&step=1');
        $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
        $estpay = new estPay();
        $domainurl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/';
        $domainurl2 = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'module/' . $this->module->name . '/';
        global $cookie;
        $currency = new CurrencyCore($cookie->id_currency);
        if (!empty($_POST["animKeypad"]) && $_POST["banka"] != '' && !empty($_POST["cardHolder"]) && !empty($_POST["cvv2"]) && !empty($_POST["eMonth"]) && !empty($_POST["eYear"])) {
            if ($estpay->bankArray != '') {
                $evalMyArray = '$storedArray = ' . $estpay->bankArray . '';
                eval($evalMyArray);
            }
            if ($_POST["due"] == 0) {
                $useBank = $storedArray[0];
                $due = '';
                $totalAmount = round($total, 2);
            } else {
                $useBank = $storedArray[intval($_POST["banka"]) ];
                $due = intval($_POST["due"]);
                $totalAmount = round(($total + ($total * ($storedArray[intval($_POST["banka"]) ]["DUES"][intval($_POST["due"]) ] / 100))), 2);
            }
            //yeni-----
            $customer = new Customer((int)$cart->id_customer);
            if (!Validate::isLoadedObject($customer)) Tools::redirectLink(__PS_BASE_URI__ . 'order.php?step=1');
            $url = "https://secure.payu.com.tr/order/alu.php";
            $delivery = new Address($cart->id_address_delivery);
            $user = $delivery->getFields();
            $secretKey = Configuration::get('DETAILS');
            $arParams = array("MERCHANT" => Configuration::get('ACCOUNT'), "ORDER_REF" => rand(1000, 9999), "ORDER_DATE" => gmdate('Y-m-d H:i:s'),);
            $say = 0;
            if ($cart->getCartRules()) {
                $price1 = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
                $price = round($price1, 3);
                if ($price1 > $price) $price+= 0.001;
                $arParams['ORDER_PNAME'][0] = 'Sepet ' . $cart->id;
                $arParams['ORDER_PCODE'][0] = $cart->id;
                $arParams['ORDER_PINFO'][0] = 'indirimli alisveris';
                $arParams['ORDER_PRICE'][0] = $price;
                $arParams['ORDER_QTY'][0] = 1;
                $arParams['ORDER_VAT'][0] = 0;
                $say = $say + 1;
            } else {
                foreach ($cart->getProducts() as $key => $item) {
                    $price = round($item['price'], 3);
                    if ($item['price'] > $price) $price+= 0.001;
                    $arParams['ORDER_PNAME'][$key] = $item['name'];
                    $arParams['ORDER_PCODE'][$key] = $item['id_product'];
                    $arParams['ORDER_PINFO'][$key] = $item['name'];
                    $arParams['ORDER_PRICE'][$key] = $price;
                    $arParams['ORDER_QTY'][$key] = $item['quantity'];
                    $arParams['ORDER_VAT'][$key] = $item['rate'];
                    $say = $key + 1;
                }
            }
            if ((int)Configuration::get('PS_GIFT_WRAPPING') && $cart->gift) {
                $wrapping_fees_tax_inc = $cart->getGiftWrappingPrice();
                $arParams['ORDER_PNAME'][$say] = 'Hediye Paketi creti';
                $arParams['ORDER_PCODE'][$say] = $cart->id;
                $arParams['ORDER_PINFO'][$say] = 'Hediye Paketi creti';
                $arParams['ORDER_PRICE'][$say] = $wrapping_fees_tax_inc;
                $arParams['ORDER_QTY'][$say] = 1;
                $arParams['ORDER_VAT'][$say] = 0;
            }
            if (empty($user['phone_mobile'])) {
                $tel = $user['phone'];
            } else {
                $tel = $user['phone_mobile'];
            };
            $arParams+= array("PRICES_CURRENCY" => $currency->iso_code, "PAY_METHOD" => "CCVISAMC", "SELECTED_INSTALLMENTS_NUMBER" => $due, "CC_NUMBER" => $_POST["animKeypad"], "EXP_MONTH" => $_POST["eMonth"], "EXP_YEAR" => $_POST["eYear"], "CC_CVV" => $_POST["cvv2"], "CC_OWNER" => $_POST["cardHolder"], 'ORDER_SHIPPING' => $cart->getTotalShippingCost(),
            //Return URL on the Merchant webshop side that will be used in case of 3DS enrolled cards authorizations.
            'BACK_REF' => $domainurl . "3ds_return.php", "CLIENT_IP" => $_SERVER["REMOTE_ADDR"], "BILL_LNAME" => $user['lastname'], "BILL_FNAME" => $user['firstname'], "BILL_EMAIL" => $customer->email, "BILL_PHONE" => $tel, "BILL_COUNTRYCODE" => "TR", "BILL_ZIPCODE" => $user['postcode'], //optional
            "BILL_ADDRESS" => $user['address1'], //optional
            "BILL_CITY" => $user['city'], //optional
            "DELIVERY_LNAME" => $user['lastname'], //optional
            "DELIVERY_FNAME" => $user['firstname'], //optional
            "DELIVERY_EMAIL" => $customer->email, //optional
            "DELIVERY_PHONE" => $user['phone_mobile'], //optional
            "DELIVERY_ADDRESS" => $user['address1'], //optional
            "DELIVERY_ZIPCODE" => $user['postcode'], //optional
            "DELIVERY_CITY" => $user['city'], //optiona
            "DELIVERY_COUNTRYCODE" => "TR", //optional
            );
            //begin HASH calculation
            ksort($arParams);
            $hashString = "";
            foreach ($arParams as $key => $val) {
                if (!is_array($val)) {
                    $hashString.= strlen($val) . $val;
                }
                if (is_array($val)) {
                    foreach ($val as $v2) {
                        $hashString.= strlen($v2) . $v2;
                    }
                }
            }
            // echo $hashString. "<br><br><br>";
            $arParams["ORDER_HASH"] = hash_hmac("md5", $hashString, $secretKey);
            //echo "<br><br><br><br>" . http_build_query($arParams);
            //end HASH calculation
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arParams));
            $response = curl_exec($ch);
            $curlerrcode = curl_errno($ch);
            $curlerr = curl_error($ch);
            if (empty($curlerr) && empty($curlerrcode)) {
                $parsedXML = @simplexml_load_string($response);
                echo $parsedXML;
                if ($parsedXML !== FALSE) {
                    //Get PayU Transaction reference.
                    //Can be stored in your system DB, linked with your current order, for match order in case of 3DSecure enrolled cards
                    //Can be empty in case of invalid parameters errors
                    $payuTranReference = $parsedXML->REFNO;
                    if ($parsedXML->STATUS == "SUCCESS") {
                        if (($parsedXML->RETURN_CODE == "3DS_ENROLLED") && (!empty($parsedXML->URL_3DS))) {
                            header("Location:" . $parsedXML->URL_3DS);
                            die();
                        }
                        $this->module->validateOrder($cart->id, _PS_OS_PAYMENT_, $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
                        $id_order = $this->module->currentOrder;
                        $banka = $useBank["NAME"];
                        $taksit = $parsedXML->INSTALLMENTS_NO;
                        $referans = $parsedXML->REFNO;
                        $tutar = $parsedXML->AMOUNT;
                        $kur = $parsedXML->CURRENCY;
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
                        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key);
                    } else {
                        $i = $parsedXML->RETURN_CODE;
                        $hata = "";
                        switch ($i) {
                            case GWERROR_19:
                                $hata = "Sipari oluurken bir hata olutu. Ltfen daha deneyiniz";
                            break;
                            case GW_ERROR_GENERIC_3D:
                                $hata = "3D dorulama yaplrken bir hata olutu";
                            break;
                            case GWERROR_ - 9:
                                $hata = "Geersiz son kullanma tarihi";
                            break;
                            case GWERROR_ - 3:
                                $hata = "Acquirer birimini araynz";
                            break;
                            case GWERROR_ - 2:
                                $hata = "Sipari ileminde hata. Ltfen tekrar deneyiniz";
                            break;
                            case GWERROR_05:
                                $hata = "Sipari onaylanmad";
                            break;
                            case GWERROR_08:
                                $hata = "Geersiz tutar";
                            break;
                            case GWERROR_13:
                                $hata = "Geersiz tutar";
                            break;
                            case GWERROR_14:
                                $hata = "Kart hatal";
                            break;
                            case GGWERROR_15:
                                $hata = "Kart hatal";
                            break;
                            case GWERROR_19:
                                $hata = "Siparii tekrar deneyiniz";
                            break;
                            case GWERROR_34:
                                $hata = "Fazla sayda deneme";
                            break;
                            case GWERROR_41:
                                $hata = "Kayp ya da alnt kart";
                            break;
                            case GWERROR_43:
                                $hata = "alnt kart";
                            break;
                            case GWERROR_51:
                                $hata = "Yetersiz bakiye";
                            break;
                            case GWERROR_54:
                                $hata = "Vadesi dolmu kart";
                            break;
                            case GWERROR_57:
                                $hata = "Karta kapal ilem";
                            break;
                            case GWERROR_58:
                                $hata = "yerine kapal ilem";
                            break;
                            case GWERROR_61:
                                $hata = "Tutar limiti ald";
                            break;
                            case GWERROR_62:
                                $hata = "Kstlanm kart";
                            break;
                            case GWERROR_65:
                                $hata = "Tekrar limiti ald";
                            break;
                            case GWERROR_75:
                                $hata = "PIN tekrar limiti ald";
                            break;
                            case GWERROR_82:
                                $hata = "Kart bankasnda sre am gerekleti , tekrar deneyiniz";
                            break;
                            case GWERROR_84:
                                $hata = "Hatal CVV";
                            break;
                            case GWERROR_91:
                                $hata = "Kart bankasnda teknik problem";
                            break;
                            case GWERROR_96:
                                $hata = "Sistem hatas";
                            break;
                            case GWERROR_2204:
                                $hata = "Kart taksite kapal";
                            break;
                            case GWERROR_2304:
                                $hata = "Bu sipari devam eden durumda";
                            break;
                            case GWERROR_5007:
                                $hata = "Banka debit kartlaryla sadece 3D ilem yaplabilir";
                            break;
                            case ALREADY_AUTHORIZED:
                                $hata = "Tekrar deneyiniz";
                            break;
                            case NEW_ERROR:
                                $hata = "Banka, sistemin tanmad yeni bir hata dnd";
                            break;
                            case WRONG_ERROR:
                                $hata = "Tekrar deneyin";
                            break;
                            case -9999:
                                $hata = "Banlanm ilem";
                            break;
                            case 1:
                                $hata = "Banka destek birimi ile grnz";
                            break;
                        }
                        /*echo "HATA: " . $parsedXML->RETURN_MESSAGE . " [" . $parsedXML->RETURN_CODE . "]";*/
                        Tools::redirectLink('index.php?fc=module&module=estpay&controller=payment&fail=1&amount=' . $totalAmount . '&err=' . $i . $parsedXML->RETURN_MESSAGE);
                    }
                }
            } else {
                $hata = "Ltfen Btn Alanlar Doldurunuz";
                Tools::redirectLink('index.php?fc=module&module=estpay&controller=payment&fail=1&amount=' . $totalAmount . '&err=' . $hata);
            }
        } else {
            $hata = "Ltfen Btn Alanlar Doldurunuz";
            Tools::redirectLink('index.php?fc=module&module=estpay&controller=payment&fail=1&amount=' . $totalAmount . '&err=' . $hata);
        }
    }
}