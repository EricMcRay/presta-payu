<br/>
<h3><img alt="" src="../img/admin/cart.gif"> {l s='Kredi Kartı Çekim Detayları' mod='estpay'}</h3>
<div class="table_block">
    <table class="std" cellspacing="0" cellpadding="0" id="estpayTable" width="100%">
        <thead>
        <tr>
            <th>{l s='Sipariş no' mod='estpay'}</th>
            <th>{l s='Banka' mod='estpay'}</th>
            <th>{l s='Taksit' mod='estpay'}</th>
            <th>{l s='Payu - Referans' mod='estpay'}</th>
            <th>{l s='Tutar' mod='estpay'}</th>
            <th>{l s='Kur' mod='estpay'}</th>
            <th>{l s='Vade Farkı' mod='estpay'}</th>
        </tr>
        </thead>
        <tbody>
            <tr class="product-row">
                <td>{$id_order}</td>
                <td>{$banka}</td>
                <td>{$taksit}</td>
                <td>{$referans}</td>
                <td>{$tutar}</td>
                <td>{$kur}</td>
                <td>{$vade}</td>

            </tr>
        </tbody>
    </table>

</div>