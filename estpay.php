<?php
@ini_set('display_errors', 'on');

class estPay extends PaymentModule {
    private $_html = '';
    private $_postErrors = array();
    public $account;
    public $secret;
    public $banks;
    public $bankArray;
    public $currencies;
    public function __construct() {
        $this->name = 'estpay';
        $this->tab = 'payments_gateways';
        $this->version = 1.2;
        $this->author = 'Sanalposmodul.com';
        $config = Configuration::getMultiple(array('EST_PAY_BANKS', 'EST_PAY_CURRENCIES', 'ACCOUNT', 'DETAILS'));
        if (isset($config['EST_PAY_BANKS'])) $this->banks = $config['EST_PAY_BANKS'];
        $this->bankArray = $this->banks;
        if (isset($config['EST_PAY_CURRENCIES'])) $this->currencies = $config['EST_PAY_CURRENCIES'];
        parent::__construct(); /* The parent construct is required for translations */
        $this->page = basename('index.php', '.php');
        $this->displayName = $this->l('Kredi Kart- Payu');
        $this->description = $this->l('Payu Api metoduyla deme almanz salar..');
        if (!isset($this->banks)) $this->warning = $this->l('All fields must be filled!');
        if (!Configuration::get('EST_PAY_CURRENCIES')) {
            $currencies = Currency::getCurrencies();
            $authorized_currencies = array();
            foreach ($currencies as $currency) $authorized_currencies[] = $currency['id_currency'];
            Configuration::updateValue('EST_PAY_CURRENCIES', implode(',', $authorized_currencies));
        }
    }
    public function install() {
        if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn') OR !$this->registerHook('displayOrderDetail') OR !$this->registerHook('AdminOrder') OR !$this->registerHook('DisplayPDFInvoice')) return false;
        $sql_pstock_block = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'pstock_return
        (
            `id_order` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
            `banka` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
            `taksit` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
            `referans` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
            `tutar` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
            `kur` varchar(200) CHARACTER SET utf8 DEFAULT NULL
			)';
            $sql_pstock_block_res = Db::getInstance()->Execute($sql_pstock_block);
            return true;
        }
        public function uninstall() {
            if (!Configuration::deleteByName('EST_PAY_DETAILS') OR !Configuration::deleteByName('EST_PAY_BANKS') OR !parent::uninstall()) return false;
            return true;
        }
        private function _postValidation() {
            if (isset($_POST['currenciesSubmit'])) {
                $currencies = Currency::getCurrencies();
                $authorized_currencies = array();
                foreach ($currencies as $currency) if (isset($_POST['currency_' . $currency['id_currency']]) AND $_POST['currency_' . $currency['id_currency']]) $authorized_currencies[] = $currency['id_currency'];
                if (!sizeof($authorized_currencies)) $this->_postErrors[] = $this->l('at least one currency is required.');
            }
        }
        private function _postProcess() {
            if (isset($_POST['btnSubmit'])) {
                if ($this->bankArray != '') {
                    $evalMyArray = '$storedArray = ' . $this->bankArray . '';
                    eval($evalMyArray);
                    $this->bankArray = $storedArray;
                }
                if ($_POST["editBank"]) {
                    $i = 0;
                    $retMe = 'array(';
                    foreach ($this->bankArray as $bankId => $bankData) {
                        if ($bankId == intval($_POST["editBank"]["id"])) {
                            if ($_POST["editBank"]["name"] == '') {
                            } else {
                                $retMe.= 'array(';
                                $retMe.= "'NAME'=>'" . $_POST["editBank"]["name"] . "',";
                                $retMe.= "'CLIENTID'=>'" . $_POST["editBank"]["clientId"] . "',";
                                $retMe.= "'URL'=>'" . $_POST["editBank"]["url"] . "',";
                                $retMe.= "'USER'=>'" . $_POST["editBank"]["user"] . "',";
                                $retMe.= "'PASS'=>'" . $_POST["editBank"]["pass"] . "',";
                                $retMe.= "'DUES'=>array(";
                                $k = 0;
                                foreach ($_POST["editBank"]["dues"]["due"] as $due) {
                                    if ($due != '') $retMe.= '' . intval($due) . '=>' . $_POST["editBank"]["dues"]["rate"][$k] . ',';
                                    $k++;
                                }
                                $retMe.= ')';
                                $retMe.= '),';
                            }
                        } else {
                            $retMe.= 'array(';
                            $retMe.= "'NAME'=>'" . $bankData["NAME"] . "',";
                            $retMe.= "'CLIENTID'=>'" . $bankData["CLIENTID"] . "',";
                            $retMe.= "'URL'=>'" . $bankData["URL"] . "',";
                            $retMe.= "'USER'=>'" . $bankData["USER"] . "',";
                            $retMe.= "'PASS'=>'" . $bankData["PASS"] . "',";
                            $retMe.= "'DUES'=>array(";
                            foreach ($bankData["DUES"] as $due => $rates) {
                                if ($due != '') $retMe.= '' . intval($due) . '=>' . $rates . ',';
                            }
                            $retMe.= ')';
                            $retMe.= '),';
                        }
                        $i++;
                    }
                    $retMe.= ');';
                }
                if ($_POST["addBank"]) {
                    $i = 0;
                    $retMe = 'array(';
                    if (is_array($this->bankArray)) {
                        foreach ($this->bankArray as $bankId => $bankData) {
                            $retMe.= 'array(';
                            $retMe.= "'NAME'=>'" . $bankData["NAME"] . "',";
                            $retMe.= "'CLIENTID'=>'" . $bankData["CLIENTID"] . "',";
                            $retMe.= "'URL'=>'" . $bankData["URL"] . "',";
                            $retMe.= "'USER'=>'" . $bankData["USER"] . "',";
                            $retMe.= "'PASS'=>'" . $bankData["PASS"] . "',";
                            $retMe.= "'DUES'=>array(";
                            foreach ($bankData["DUES"] as $due => $rates) {
                                if ($due != '') $retMe.= '' . intval($due) . '=>' . $rates . ',';
                            }
                            $retMe.= ')';
                            $retMe.= '),';
                            $i++;
                        }
                    }
                    $retMe.= 'array(';
                    $retMe.= "'NAME'=>'" . $_POST["addBank"]["name"] . "',";
                    $retMe.= "'CLIENTID'=>'" . $_POST["addBank"]["clientId"] . "',";
                    $retMe.= "'URL'=>'" . $_POST["addBank"]["url"] . "',";
                    $retMe.= "'USER'=>'" . $_POST["addBank"]["user"] . "',";
                    $retMe.= "'PASS'=>'" . $_POST["addBank"]["pass"] . "',";
                    $retMe.= "'DUES'=>array(";
                    $k = 0;
                    foreach ($_POST["addBank"]["dues"]["due"] as $due) {
                        if ($due != '') $retMe.= '' . intval($due) . '=>' . $_POST["addBank"]["dues"]["rate"][$k] . ',';
                        $k++;
                    }
                    $retMe.= ')';
                    $retMe.= '),';
                    $retMe.= ');';
                }
                //print_r($_POST);
                Configuration::updateValue('EST_PAY_BANKS', $retMe);
                $config = Configuration::getMultiple(array('EST_PAY_BANKS', 'EST_PAY_CURRENCIES'));
                if (isset($config['EST_PAY_BANKS'])) $this->banks = $config['EST_PAY_BANKS'];
                $this->bankArray = $this->banks;
            } elseif (isset($_POST['accountSubmit'])) {
                Configuration::updateValue('ACCOUNT', Tools::getValue('account'));
                Configuration::updateValue('DETAILS', Tools::getValue('secret'));
            }
            $this->_html.= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('ok') . '" /> ' . $this->l('Settings updated') . '</div>';
        }
        private function _displayestPay() {
            $this->_html.= '<img src="../modules/estpay/estpay.jpg" style="float:left; margin-right:15px;"><b>Payu Api Metoduyla deme almanz salar.<br /><br /><br />';
        }
        private function _displayForm() {
            if ($this->bankArray != '') {
                $evalMyArray = '$storedArray = ' . $this->bankArray . '';
                eval($evalMyArray);
                $this->bankArray = $storedArray;
            }
            $account = Configuration::get('ACCOUNT');
            $secret = Configuration::get('DETAILS');
            $this->_html.= '
            <div style="clear:both;"></div>
            <br /><br />
            <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
            <table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
            <tr><td colspan="2">' . $this->l('Payu Hesabnza Giri Yaparak , Hesap Ynetimi -> Hesap Ayarlar Ksmndan Bilgileri Edinebilirsiniz') . '.<br /><br /></td></tr>
            <tr>
            <td align="left">
            yeri Entegrasyon smi:<br/>
            <input type="text" name="account" value="' . $account . '" style="width: 200px;" />
            </td>
            </tr>
            <tr>
            <td align="left">
            Kodlama Anahtar:<br/>
            <input type="text" name="secret" value="' . $secret . '" style="width: 200px;" />
            </td>
            </tr>
            <tr><td colspan="2" align="center"><br /><input class="button" name="accountSubmit" value="' . $this->l('Ayarlar Gncellle') . '" type="submit" /></td></tr>
            </table>
			</fieldset>
            </form>
            ';
            $this->_html.= '
            <div style="clear:both;"></div>
            <div style="float:left; width:280px;margin:10px;">
            <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            <fieldset>
            <legend><img src="../img/admin/contact.gif" />Yeni Banka Ekle</legend>
			
            <table border="0" width="100%" cellpadding="2" cellspacing="2" id="form">
            <tr>
            <td align="left">
            Kart Tipi:<br/>
            <input type="text" name="addBank[name]" value="" style="width: 200px;" />
            </td>
            </tr>
            
            <tr>
            <td align="left">
            <b>Taksit ve Oranlar</b><br/>
            <table border="0" width="100%" cellpadding="2" cellspacing="2" id="form">
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            <tr>
            <td>Ay: <input type="text" name="addBank[dues][due][]" style="width:30px;"></td>
            <td>Oran: %<input type="text" name="addBank[dues][rate][]" style="width:30px;"></td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
            <input type="submit" name="btnSubmit" class="button" value="Ekle">
			
            </fieldset>
            </form>
            </div>';
            /*
            array(array('NAME'=>'Garanti Bankas','CLIENTID'=>'235456345754','URL'=>'http://asdasd.asdsa.com/sdfds','USER'=>'deneme','PASS'=>'12343432','DUES'=>array(1=>2,2=>3,3=>4,6=>0,)),)
            */
            if (is_array($this->bankArray)) {
                foreach ($this->bankArray as $bankId => $bankData) {
                    $this->_html.= '
                    <div style="float:left; width:290px;margin:5px;">
                    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                    <fieldset>
                    <legend><img src="../img/admin/contact.gif" />' . $bankData["NAME"] . '</legend>
                    
                    <table border="0" width="100%" cellpadding="2" cellspacing="2" id="form">
					<tr>
                    <td align="left">
                    Kart Tipi:<br/>
                    <input type="text" name="editBank[name]" value="' . $bankData["NAME"] . '" style="width: 200px;" />
                    </td>
					</tr>
                    
					<tr>
                    <td align="left">
                    <b>Taksit ve Oranlar</b><br/>
                    <table border="0" width="100%" cellpadding="2" cellspacing="2" id="form">';
                    foreach ($bankData["DUES"] as $due => $rate) {
                        $this->_html.= '
                        <tr>
                        <td>Ay: <input type="text" name="editBank[dues][due][]" value="' . $due . '" style="width:30px;"></td>
                        <td>Oran: %<input type="text" name="editBank[dues][rate][]" value="' . $rate . '" style="width:30px;"></td>
                        </tr>
                        ';
                    }
                    $this->_html.= '
                    <tr>
                    <td>Ay: <input type="text" name="editBank[dues][due][]" value="" style="width:30px;"></td>
                    <td>Oran: %<input type="text" name="editBank[dues][rate][]" value="" style="width:30px;"></td>
                    </tr>
                    </table>
                    </td>
					</tr>
                    </table>
                    <input type="hidden" name="editBank[id]" value="' . $bankId . '">
                    <input type="submit" name="btnSubmit"  class="button" value="Kaydet">
                    
                    </fieldset>
                    </form>
                    </div>';
                }
            };
        }
        public function getContent() {
            $this->_html = '<h2>' . $this->displayName . '</h2>';
            if (!empty($_POST)) {
                $this->_postValidation();
                if (!sizeof($this->_postErrors)) $this->_postProcess();
                else foreach ($this->_postErrors AS $err) $this->_html.= '<div class="alert error">' . $err . '</div>';
            } else $this->_html.= '<br />';
            $this->_displayestPay();
            $this->_displayForm();
            return $this->_html;
        }
        public function execPayment($cart) {
            global $cookie, $smarty;
            if ($this->bankArray != '') {
                $evalMyArray = '$storedArray = ' . $this->bankArray . '';
                eval($evalMyArray);
                $this->bankArray = $storedArray;
            }
            $currencies = Currency::getCurrencies();
            $authorized_currencies = array_flip(explode(',', $this->currencies));
            $currencies_used = array();
            foreach ($currencies as $key => $currency) {
                if (isset($authorized_currencies[$currency['id_currency']])){ 
                    $currencies_used[] = $currencies[$key]; 
                }
            }
            var_dump($this->bankArray);
            $smarty->assign(array('banks' => $this->bankArray, 'currency_default' => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')), 'currencies' => $currencies, 'total' => number_format($cart->getOrderTotal(true, 3), 2, '.', ''), 'isoCode' => Language::getIsoById(intval($cookie->id_lang)), 'this_path' => $this->_path, 'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/' . $this->name . '/'));
            return $this->display('index.php', 'payment_execution.tpl');
                
            }
            public function hookPayment($params) {
                global $smarty;
                $smarty->assign(array(
                    'this_path' => $this->_path,
                    'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
                ));
                return $this->display(__FILE__, 'payment.tpl');
            }
            public function hookdisplayOrderDetail($params) {
                $smarty = $this->smarty->smarty;
                //$objOrder = $params['objOrder'];
                $order = $params['order'];
                //echo $order ;
                //var_dump($order);
                $islemID = $order->id;
                $s = Db::getInstance()->getRow('
                SELECT id_order, banka, taksit, referans, tutar , kur
                FROM ' . _DB_PREFIX_ . 'pstock_return
                WHERE id_order = "' . $islemID . '"
                ');
                // $s['name'];
                if (!empty($s)) {
                    $this->smarty->assign(array('id_order' => $s['id_order'], 'banka' => $s['banka'], 'taksit' => $s['taksit'], 'referans' => $s['referans'], 'tutar' => $s['tutar'], 'kur' => $s['kur'], 'vade' => $s['tutar'] - $params['order']->total_paid,));
                    //return $this->display(_PS_MODULE_DIR_.'/estpay/views/templates/hook/back_office.tpl');
                    return $this->display('index.php', 'back_office.tpl');
                }
                //var_dump($params);exit;
                
            }
            public function hookAdminOrder($params) {
                $smarty = $this->smarty->smarty;
                //$objOrder = $params['objOrder'];
                $order = new Order((int)$params['id_order']);
                //echo $order ;
                //var_dump($order);
                $islemID = $params['id_order'];
                $s = Db::getInstance()->getRow('
                SELECT id_order, banka, taksit, referans, tutar , kur
                FROM ' . _DB_PREFIX_ . 'pstock_return
                WHERE id_order = "' . $islemID . '"
                ');
                // $s['name'];
                if (!empty($s)) {
                    $this->smarty->assign(array('id_order' => $s['id_order'], 'banka' => $s['banka'], 'taksit' => $s['taksit'], 'referans' => $s['referans'], 'tutar' => $s['tutar'], 'kur' => $s['kur'], 'vade' => $s['tutar'] - (float)$order->getTotalPaid(),));
                    //return $this->display(_PS_MODULE_DIR_.'/estpay/views/templates/hook/back_office.tpl');
                    //var_dump($params);
                    return $this->display('index.php', 'back_office2.tpl');
                }
            }
            public function hookDisplayPDFInvoice($params) {
                $order_invoice = $params['object'];
                $order = new Order((int)$order_invoice->id_order);
                $islemID = $order_invoice->id_order;
                $s = Db::getInstance()->getRow('
                SELECT id_order, banka, taksit, referans, tutar , kur
                FROM ' . _DB_PREFIX_ . 'pstock_return
                WHERE id_order = "' . $islemID . '"
                ');
                if (!empty($s)) {
                    $vade = $s['tutar'] - (float)$order->getTotalPaid();
                    //$return  = sprintf('Sipari no    :%1$s', $s['id_order']) ;
                    //$return = sprintf($this->l('Kart Bilgisi  :%1$s'), $s['banka']);
                    $return .= sprintf($this->l('Taksitbilgisi: %1$s'), $s['taksit']);
                    $return .= sprintf($this->l('PayuRefarans: %1$s'), $s['referans']);
                    $return .= sprintf($this->l('Tutar: %1$s'), $s['tutar']);
                    $return .= sprintf($this->l('VadeFark: %1$s'), $vade);
                    
                    return $return ;
                }}
                
                public function hookPaymentReturn($params)
                {
                    if (!$this->active)
                    return;
                    
                    $state = $params['objOrder']->getCurrentState();
                    if ($state == Configuration::get('_PS_OS_PAYMENT_') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
                    {
                        $this->smarty->assign(array(
                            'status' => 'ok',
                            'id_order' => $params['objOrder']->id
                        ));
                        if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
                        $this->smarty->assign('reference', $params['objOrder']->reference);
                    }
                    else
                    $this->smarty->assign('status', 'failed');
                    return $this->display(__FILE__, 'payment_return.tpl');
                }
                
            }
            
            ?>