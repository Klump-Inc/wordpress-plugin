const payload = {
    publicKey: klp_payment_params.primary_key,
    data: {
        amount: klp_payment_params.amount,
        currency: klp_payment_params.currency,
        merchant_reference: klp_payment_params.txnref,
        meta_data: {
            customer: klp_payment_params.firstname + ' ' + klp_payment_params.lastname,
            email: klp_payment_params.email
        },
        items: klp_payment_params.order_items,
        redirect_url: klp_payment_params.cb_url,
    },
    onSuccess: (data) => {
        console.log('html onSuccess will be handled by the merchant');
        console.log(data);
        transactionComplete(data.data.data.data)
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

if (klp_payment_params.shipping_fee !== '0' && klp_payment_params.shipping_fee > 0) {
    payload.data.shipping_fee = klp_payment_params.shipping_fee;
}

document.getElementById('klump__checkout').addEventListener('click', function () {
    const klump = new Klump(payload);
});

function transactionComplete(data) {
    const fields = {
        order_id: klp_payment_params.order_id,
        ...data
    }

    const form = document.createElement("form");
    form.setAttribute("method", "POST");
    form.setAttribute("action", klp_payment_params.cb_url);

    for (let item in fields) {
        const field = document.createElement("input");
        field.setAttribute("type", "hidden");
        field.setAttribute("name", item);
        field.setAttribute("value", data[item]);
        form.appendChild(field);
    }

    document.body.appendChild(form);
    form.submit();
}
