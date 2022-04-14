const payload = {
    publicKey: klp_payment_params.primary_key,
    data: {
        amount: klp_payment_params.amount,
        currency: klp_payment_params.currency,
        // redirect_url,
        merchant_reference: klp_payment_params.txnref,
        meta_data: {
            customer: klp_payment_params.firstname + ' ' + klp_payment_params.lastname,
            email: klp_payment_params.email
        }
    },
    onSuccess: (data) => {
        console.log('html onSuccess will be handled by the merchant');
        console.log(data);
        transactionComplete(data)
        return data;
    },
    onError: (data) => {
        console.log('html onError will be handled by the merchant');
        console.log(data);
    },
    onLoad: (data) => {
        console.log('html onLoad will be handled by the merchant');
        console.log(data);
    },
    onOpen: (data) => {
        console.log('html OnOpen will be handled by the merchant');
        console.log(data);
    },
    onClose: (data) => {
        console.log('html onClose will be handled by the merchant');
        console.log(data);
    }
}
document.getElementById('klump__checkout').addEventListener('click', function () {
    const klump = new Klump(payload);
});

function transactionComplete(data) {
    console.log('Sending data');

    const XHR = new XMLHttpRequest();

    let urlEncodedData = "",
        urlEncodedDataPairs = [],
        name;

    // Turn the data object into an array of URL-encoded key/value pairs.
    for (name in data) {
        urlEncodedDataPairs.push(encodeURIComponent(name) + '=' + encodeURIComponent(data[name]));
    }

    // Combine the pairs into a single string and replace all %-encoded spaces to
    // the '+' character; matches the behavior of browser form submissions.
    urlEncodedData = urlEncodedDataPairs.join('&').replace(/%20/g, '+');

    // Define what happens on successful data submission
    XHR.addEventListener('load', function (event) {
        console.log('Yeah! Data sent and response loaded.');
    });

    // Define what happens in case of error
    XHR.addEventListener('error', function (event) {
        console.log('Oops! Something went wrong.');
    });

    // Set up our request
    XHR.open('POST', klp_payment_params.cb_url);

    // Add the required HTTP header for form data POST requests
    XHR.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Finally, send our data.
    XHR.send(urlEncodedData);
}
