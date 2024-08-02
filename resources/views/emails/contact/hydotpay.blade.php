<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to {{ config('app.name') }}!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .main {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .signup-input {
            width: 90%;
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: none;
            outline: none;
        }
        fieldset {
            border-radius: 1rem;
        }
        legend {
            padding: 0 0.5rem;
            font-size: 1rem;
            font-weight: bold;
        }
        button {
            width: 100%;
            padding: 1rem;
            border-radius: 1.5rem;
            background-color: #000000;
            color: #ffffff;
            font-size: 1rem;
        }
    </style>
    <script>
        const currency = "₵";

        function Payment() {
            const url = 'https://hydotpay.hydottech.com/payment/33gdhsDghs4529Z22Z2Zzw12dgst=' + {{ $Sales->TransactionId }};
            window.location.href = url;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="main">
            <span style="font-size: 1.5rem;">Make Payment To {{ config('app.name') }}!</span>

            <div style="display: flex; flex-direction: row; justify-content: space-between; padding: 1rem;">
                <span style="font-weight: 500; font-size: 1.2rem;">PaymentId</span>
                <span style="font-weight: 500; font-size: 1.2rem;">{{ $Sales->TransactionId }}</span>
            </div>

            <div style="display: flex; flex-direction: row; justify-content: space-between; padding: 1rem;">
                <span style="font-weight: 500; font-size: 1.2rem;">Username</span>
                <span style="font-weight: 500; font-size: 1.2rem;">{{ $Sales->CustomerName }}</span>
            </div>

            <div style="display: flex; flex-direction: row; justify-content: space-between; padding: 1rem;">
                <span style="font-weight: 500; font-size: 1.2rem;">Email</span>
                <span style="font-weight: 500; font-size: 1.2rem;">{{ $Sales->CustomerId }}</span>
            </div>

            <div style="display: flex; flex-direction: row; justify-content: space-between; padding: 1rem;">
                <span style="font-weight: 500; font-size: 1.2rem;">Narration</span>
                <span style="font-weight: 500; font-size: 1.2rem;">{{ $Sales->PaymentReference }}</span>
            </div>

            <div style="display: flex; flex-direction: row; justify-content: space-between; padding: 1rem;">
                <span style="font-weight: 500; font-size: 1.2rem;">Total Amount</span>
                <span style="font-weight: 500; font-size: 1.2rem;">{{ currency }} {{ $Sales->Amount }}</span>
            </div>

            <button type="button" onclick="Payment()">Pay</button>
        </div>
    </div>
</body>
</html>
