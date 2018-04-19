{capture name=path}{l s='Kredi kartı İle Ödeme.' mod='bankwire'}{/capture}

<h2>{l s='Order summary' mod='bankwire'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
<link media="all" type="text/css" href="{$this_path}css/csscss.css" rel="stylesheet">
<script>
	var dues = new Array();
	dues[9999] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[0] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[1] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[2] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[3] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[4] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[5] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[6] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[7] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[8] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[9] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[10] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[11] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[12] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[13] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[14] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[15] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	dues[16] = '<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {$total} {$currencies.1.name}';
	{section name=bb loop=$banks}
	
	              {if $group.name|escape:'htmlall':'UTF-8'|substr:0:3 == "sag"}<label for="group_{$id_attribute_group|intval}">{$group.name|escape:'htmlall':'UTF-8'|substr:3:10} :</label>{/if}
				  
				  
		dues[{$smarty.section.bb.index}] = '<table border="0" width = "500px" cellpadding="5" cellspacing="5" ><tr><td></td><td><b>Taksit Sayısı</b><br /></td><td><b>Vade Oranı</b><br /></td><td><b>Toplam Tutar</b><br /></td></tr>{foreach key=due item=rate from=$banks[bb].DUES}<tr><td><input type="radio"  name="due" value="{$due}" ></td><td>{if $due == 1 }Tek &Ccedil;ekim{else} {$due} {/if}  </td><td>{l s='rate:' mod='estpay'} %{$rate} </td> <td>Toplam: {math equation="(total+(total*(rate/100)))" total=$total rate=$rate  format="%.2f"} </td></tr>{foreachelse}<input type="radio" name="due" value="" />{l s='No dues' mod='estpay'} {math equation="($total*1)"  format="%.2f"} {/foreach}</table>';
	{/section}
</script>

{literal}
<script type="text/javascript">
$(document).ready(function() {
    $("#due").click(function () {
 
	$("#odeme").show();
	//$('input:radio[name=sex]')[0].checked = true;
 
    });

		
		
		
  $.divliste = {
    '0' : $([]),
      '5' : $('#card_702, #card_704,#kartlogo'), //fortis
    '6' : $('#card_707,#safa2,#kartlogo'), // finans bank
	'7' : $('#card_709,#safa3,#kartlogo'), //hsbc
	  '0' : $('#card_692, #card_683 , #card_684 , #card_678,#kartlogo'), // garanti bankası
	  '1' : $('#card_695, #card_679,#kartlogo'), // denizbank
	  '2' : $('#card_695, #card_679,#kartlogo'), // şekerbank
	  '3' : $('#card_695, #card_679,#kartlogo'), // teb
	  '4' : $('#card_695, #card_679, #card_686,#kartlogo'), // ing bank
	'8' : $('#card_695, #card_686,#kartlogo'), // iş bankası
	'9' : $('#card_695, #card_686,#kartlogo'), // ziraat bankası
	'10' : $('#card_688, #card_690, #card_691,#kartlogo'), // akbank
	'11' : $('#card_696, #card_689,#kartlogo'), //citibank
	'12' : $('#card_701, #card_703,#kartlogo'),  // vakıfbank
	  '13' : $('#card_701, #card_703,#kartlogo'),  // yapı kredi
	'14' : $('#card_703,#kartlogo'),  //diğer
  };

  $('#divsecici').change(function() {
    // hide all
	document.getElementById('due').innerHTML = dues[this.value];
    $.each($.divliste, function() { this.hide();
	 $('#taksitler').show();
	 $('#odeme').hide();
	 });
    // show current
    $.divliste[$(this).val()].show();
	
  });
});


</script>
{/literal}
{if isset($smarty.get.success)}
<div style="padding:6px;">
<img src="{$this_path}success.gif" align="left" style="margin:4px;">
<b>{l s='Payment Successful!' mod='estpay'} ({$smarty.get.amount} ) </b><br/>
{l s='[success text]' mod='estpay'}
</div>
{else}

{if isset($smarty.get.fail)}
<div style="padding:6px;">
<img src="{$this_path}fail.gif" align="left" style="margin:4px;">
<b>{l s='Payment Error!' mod='estpay'}</b><br/>
{l s='[payment error text]' mod='estpay'}<br/>
<b>{$smarty.get.err|urldecode}</b>
</div>
<br><br>
{/if}

<form action="{$link->getModuleLink('estpay', 'validation', [], true)|escape:'html'}" method="post">
<div id="step_1" style="background-color:#8FC300; width:99%;  padding: 5px; margin-top: 10px; margin-bottom: 10px; text-align: left; display: block; font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 1px solid #fff; color: #fff;"><b>1. ADIM - KART T&#304;P&#304;</b><br />

<div style="float:left; width: 100%; height: auto; padding:5px; margin-top: 10px; margin-bottom: 10px; text-align: left; font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 1px solid #ccc; color: #666; margin-left:-6px">

<label for="banka"><b>L&uuml;tfen Kredi Kart&#305;n&#305;z&#305;n Tipini Se&ccedil;iniz:</b></label>
<select id="divsecici" name="banka" style="padding:5px; border-style: dashed; ; height:27px;">
<option value=""><b>Kredi Kart&#305;n&#305;z&#305;n Tipini Se&ccedil;iniz.</b></option>
<option value="0">Bonus</option>
<option value="1">Axess</option>
<option value="2">Maximum</option>
<option value="3">CardFinans</option>
<option value="4">World</option>
<option value="5">AsyaCard</option>
<option value="6">Paraf</option>
<option value="7">Diğer</option>
</select>

</div>

</div>
<br />


<div style="clear:both;"></div>
<div id="taksitler" style="background-color:#8FC300; width:99%; padding: 5px; margin-left: 0px; margin-top: 5px; margin-bottom: 10px; text-align: left; display:none; font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 1px solid #fff; color: #fff;" >
<b style="float:left;">2. ADIM - &Ouml;DEME &#350;EKL&#304;</b><br />

<div style="float:left; width:100%; height: auto; padding:5px; margin-top: 10px; margin-bottom: 10px; text-align: left; font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 1px solid #ccc; color: #666; margin-left:-6px">
			<select style="display:none" name="bankId" onchange="document.getElementById('due').innerHTML = dues[this.value];" style="margin-top:5px;">
				<option value="9999">{l s='-- Choose --' mod='estpay'}</option>
           
				<option value="9999">{l s='Other Bank' mod='estpay'}</option>
        
			{section name=bb loop=$banks}
				<option value="{$smarty.section.bb.index}">{$banks[bb].NAME}</option>
			{/section}
			</select>
            <br />
			<div class="taksit" id="due"></div>

            </div>
</div>
            

<div style="clear:both;"></div>

<div id="odeme" style="width:99%; padding: 5px; margin-left: 0px;  margin-bottom: 10px; text-align: left; display: none; font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 1px solid #fff; color: #fff; background-color:#8FC300;" >
<b style="float:left;">SON ADIM - KART B&#304;LG&#304;LER&#304;</b><br />
<div style="float:left; width:100%; height: auto; padding:5px; margin-top:10px; margin-bottom: 10px; text-align: left; font-weight: bold; font-family: Arial, Helvetica, sans-serif; font-size: 12px; border: 1px solid #ccc; color: #666;  margin-left:-6px;">

        <div style="float:left; width:230px; height:auto; border:none">

<b>{l s='Name on card:' mod='estpay'}</b><br />

<input type="text" name="cardHolder" value="" style="width:200px; padding:5px; border:1px solid #ccc; margin-top:5px; margin-left:0px; border-style: dashed;"><br />

<b>{l s='Card number:' mod='estpay'}</b><br />

		<input type="text" name="animKeypad" id="animKeypad" value="" maxlength="16" style="width:200px; padding:5px; border:1px solid #ccc; margin-top:5px; margin-left:0px; border-style: dashed;">

<br /><b>{l s='Cvv2:' mod='estpay'}</b><br />

<input type="text" name="cvv2" id="cvv2" value="" maxlength="3" style="width:50px; padding:5px; border:1px solid #ccc; margin-top:5px; margin-left:0px; border-style: dashed;">

<br /><b>{l s='Expire date:' mod='estpay'}</b><br />


<select name="eMonth" style="margin-top:5px; margin-left:0px; padding:5px; border-style: dashed; width:60px; ; height:27px;">

<option value="01"  >01</option>



<option value="02" >02</option>

<option value="03" >03</option>

<option value="04" >04</option>

<option value="05" >05</option>

<option value="06" >06</option>

<option value="07" >07</option>

<option value="08" >08</option>

<option value="09" >09</option>

<option value="10" >10</option>



<option value="11" >11</option>

<option value="12" >12</option>

</select>

/<select name="eYear" style="margin-top:5px; padding:5px; border-style: dashed; width:100px; height:27px;">
<option value="2015" >2015</option>

<option value="2016" >2016</option>

<option value="2017" >2017</option>

<option value="2018" >2018</option>

<option value="2019" >2019</option>
<option value="2020" >2020</option>
<option value="2021" >2021</option>
<option value="2022" >2022</option>
<option value="2023" >2023</option>
<option value="2024" >2024</option>
<option value="2025" >2025</option>
<option value="2026" >2026</option>
<option value="2027" >2027</option>
<option value="2028" >2028</option>
<option value="2029" >2029</option>
<option value="2030" >2030</option>
</select><br />
Visa/MC secimi<br />


<select name="cardType" style="padding:5px; border:1px solid #ccc; margin-top:5px; margin-left:0px; border-style: dashed; ; height:27px;">

                            <option value="1">Visa</option>

                            <option value="2">MasterCard</option>

                        </select>


</div>
</div>
</div>
        <p class="cart_navigation clearfix" id="cart_navigation">
        	<a 
            class="button-exclusive btn btn-default" 
            href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
                <i class="icon-chevron-left"></i>Diğer Ödeme Seçenekleri
            </a>
            <button 
            class="button btn btn-default button-medium" 
            type="submit" onclick="$(this).hide();" >
                <span>Ödemeyi Tamamla<i class="icon-chevron-right right"></i></span>
            </button>
        </p>

</form>
<div style="clear:both;"></div>
{/if}
