import $ from 'jquery';
import '../css/components/_funding.scss';
import '../css/components/_cause-card.scss';


var amount = 0;
var fees = 0;
var receive_amount = 0;

        //console.log(fees);
$(document).ready(function() {
    $('#payment_amount').keyup(function(){
        var management_fees = parseFloat(document.getElementById("management_fees").value);
        var fixedFees = parseFloat(document.getElementById("Fixed_fees").value);

        amount =   parseFloat(document.getElementById("payment_amount").value.replace(",", "."));
        fees = (management_fees * amount) + fixedFees;
        receive_amount = amount - fees.toFixed(2);
        if(amount > 1){
            document.getElementById("calculate_fees").innerHTML = '- ' + fees.toFixed(2);
            document.getElementById("receive_amount").innerHTML = receive_amount.toFixed(2);
        }else{
            document.getElementById("calculate_fees").innerHTML = 'Amount should be greater than 1';
            document.getElementById("receive_amount").innerHTML = '-';
        }
    });
});