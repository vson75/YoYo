<?php


namespace App\Controller;


use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class PaypalController extends AbstractController
{

    /**
     * @Route("/payment_paypal", name="app_paypal_payment")
     */
    public function payment(){


        // follow this tuto : https://www.youtube.com/watch?v=n-Vbjd2eI_4 and https://developer.paypal.com/docs/platforms/get-started/
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->getParameter('paypal_client_id'),
                $this->getParameter('paypal_secret')
            )
        );
       //dd($apiContext);
// After Step 2
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new \PayPal\Api\Amount();
        $amount->setTotal('1.00');
        $amount->setCurrency('USD');

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount);

        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl("https://example.com/your_redirect_url.html")
            ->setCancelUrl("https://example.com/your_cancel_url.html");

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);

        try {
            $payment->create($apiContext);
            dd($payment->getApprovalLink());

        }
        catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            echo $ex->getData();
        }



    }

}