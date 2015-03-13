jQuery(document).ready(function($){
var handler = StripeCheckout.configure({
	key: productdet.stripe,
	token: function(token) {
		$stripetoken=token.id;
		$('input[name=token]').val($stripetoken);
		$currenturl= window.location.href;
		if(window.location.search.length){
			$newaction=$currenturl+'&wpcuequiz_stripe=1';
		}else{
			$newaction=$currenturl+'?wpcuequiz_stripe=1';
		}
		$('#stripepayment').attr('action',$newaction);
		$('#stripepayment').submit();
	}
});
$('.productsale').on('click','#stripebutton',function(e){
	handler.open({
		name: productdet.producttitle,
		description: productdet.productdesc,
		amount: (productdet.productprice*100),
		currency:productdet.productcurrency
	});
	e.preventDefault();
});
// Close Checkout on page navigation
$(window).on('popstate', function() {
	handler.close();
});
});