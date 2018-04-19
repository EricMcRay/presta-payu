{capture name=path}{l s='Odeme durumu' mod='trpayment'}{/capture}
<h1 class="page-heading bottom-indent">{l s='Ödeme detayları' mod='trpayment'}</h1>
{assign var='current_step' value='payment'}
<div class="col-sm-12 col-md-12">
	<div class="alert alert-success" style="text-align: center;">
		<br/>
		<br/>
		<h2>{l s='Ödeme Başarılıyla Alındı.' mod='trpayment'}</h2>
		<hr class="message-inner-separator">

		<h5>{l s='Siparişiniz alınmıştır' mod='trpayment'}</h5>
		<h5>
			<br /><br /><span class="bold">{l s='Siparişinizi en kısa sürede ileteceğiz.' mod='trpayment'}</span>
			<br /><br />{l s='Herhangi bir sorununuz ya da sorunuz olduğunda müşteri hizmetleri birimimizle temas kurabilirsiniz.' mod='trpayment'}.
		</h5>

	</div>
	<ul class="footer_links clearfix">
		<li>
			<a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
				<span>
					<i class="icon-chevron-left"></i> {l s='Hesabıma Dön'}
				</span>
			</a>
		</li>
		<li>
			<a class="btn btn-default button button-small" href="/">
				<span><i class="icon-chevron-left"></i> {l s='Ana Sayfa'}</span>
			</a>
		</li>
	</ul>
</div>

