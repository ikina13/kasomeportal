<?php


$transToken = "4ECBF188-BD64-4D19-9576-D9A88BDBE2E7";
$paymentURL = "https://secure.3gdirectpay.com/dpopayment?token=" . $transToken;

// Redirect the user to the payment page
header("Location: " . $paymentURL);
exit();

// Extract data sent by DPO
//echo $paymentAmount = $_POST['PaymentAmount'];  // Adjust based on DPO's actual field names
//echo $paymentCurrency = $_POST['PaymentCurrency'];
//echo $companyRef = $_POST['CompanyRef'];
// Extract other relevant data

// Perform actions based on the canceled payment or error
// For example, display an error message, allow the user to retry the payment, etc.
// ...

// Display a message or redirect the user back to the shopping cart
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Canceled or Error</title>
</head>
<body>
    <h1>Payment Canceled or Error</h1>
    <p>There was an issue with your payment.</p>
    <!-- Display additional information or a link back to the shopping cart if needed -->
</body>
</html>
