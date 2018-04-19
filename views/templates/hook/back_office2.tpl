<div class="panel">

    <div class="panel-heading"><i class="icon-shopping-cart"></i> {l s='Kredi Kartı Çekim Detayları' mod='estpay'}</div>

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