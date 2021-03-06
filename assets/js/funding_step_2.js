// Set your publishable key: remember to change this to your live publishable key in production
// See your keys here: https://dashboard.stripe.com/account/apikeys

import $ from 'jquery';
import '../css/components/_funding.scss';
import '../css/components/_cause-card.scss';

var IsNotAnonymous = false;
$('#anonymous').click(function() {
    IsNotAnonymous = $('#anonymous').prop('checked');
});

var stripe = Stripe(stripe_pk_key);
var elements = stripe.elements();


 var style = {
    base: {
        iconColor: '#2F4F4F',
        color:  'rgba(42,38,38,0.85)',
        fontWeight: 500,
        fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
        fontSize: '17px',
        fontSmoothing: 'antialiased',

        ':-webkit-autofill': {
            color: '#25e738',
        },
        '::placeholder': {
            color: 'rgba(154,148,148,0.85)',
        },
    }
};
/**
old code here
 var card = elements.create("card", { style: style });
 card.mount("#card-element");

 */


var cardNumberElement = elements.create('cardNumber', {
    style: style,
    placeholder: '0000 0000 0000 0000',
});
cardNumberElement.mount('#card-number-element');

var cardExpiryElement = elements.create('cardExpiry', {
    style: style,
    placeholder: 'DD/MM',
});
cardExpiryElement.mount('#card-expiry-element');

var cardCvcElement = elements.create('cardCvc', {
    style: style,
    placeholder: 'XXX',
});
cardCvcElement.mount('#card-cvc-element');

cardNumberElement.on('change', function(event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
      displayError.textContent = event.error.message;
    } else {
      displayError.textContent = '';
    }
});

cardExpiryElement.on('change', function(event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

cardCvcElement.on('change', function(event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

 var form = document.getElementById('payment-form');

 form.addEventListener('submit', function(ev) {

   $(".btn_payment").hide();
   $(".btn_loading").show();

  ev.preventDefault();
  stripe.confirmCardPayment(clientSecret, {
    payment_method: {
      card: cardNumberElement,
      billing_details: {
        name: userId+' - '+userEmail
      }
    }
  }).then(function(result) {


      //break;
    if (result.error) {

        swal("Chuyển khoản thất bại",result.error.message, "error");
        $(".btn_payment").show();
        $(".btn_loading").hide();
      //console.log(result.error.message);
    } else {
      // The payment has been processed!
      if (result.paymentIntent.status === 'succeeded') {
        // Show a success message to your customer
        // There's a risk of the customer closing the window before callback
        // execution. Set up a webhook or plugin to listen for the
        // payment_intent.succeeded event that handles any business critical
        // post-payment actions.

        $.ajax({
          cache: false,
          url: '/add_transaction/'+uniquekey+'/'+clientSecret+'/'+amount+'/'+givingAmount+'/'+IsNotAnonymous,
          method: 'POST',
          data: {token: token.value},
          async: false,
      }).then(function(data) {

      });
          $(".btn_payment").hide();
          $(".btn_loading").hide();
          var path = window.location.pathname;
          var redirectURL = path.replace("/finance/","/post/");

          swal(
              {
                  title: "Chuyển khoản thành công",
                  text: "Cảm ơn bạn đã đóng góp cho dự án này!",
                  icon: "success",
              }
          )
              .then((value) => {
                  window.location.href = redirectURL;
              });



      }
    }
  });
});

