// Set your publishable key: remember to change this to your live publishable key in production
// See your keys here: https://dashboard.stripe.com/account/apikeys
var stripe = Stripe('pk_test_xpnUKfTJeENGTpG8tdzzpPd800IsDeAKjJ');
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
            color: 'rgba(42,38,38,0.85)',
        },
    }
};

var card = elements.create("card", { style: style });
card.mount("#card-element");

card.on('change', function(event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
      displayError.textContent = event.error.message;
    } else {
      displayError.textContent = '';
    }
});

var form = document.getElementById('payment-form');

form.addEventListener('submit', function(ev) {
  ev.preventDefault();
  stripe.confirmCardPayment(clientSecret, {
    payment_method: {
      card: card,
      billing_details: {
        name: userId+' - '+userEmail
      }
    }
  }).then(function(result) {


    if (result.error) {
      // Show error to your customer (e.g., insufficient funds)

        swal("Chuyển khoản thất bại",result.error.message, "error");
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