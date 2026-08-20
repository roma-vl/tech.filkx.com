// Submits a hidden form to LiqPay's hosted checkout - we never touch card data,
// LiqPay collects it on their own PCI DSS-compliant page and calls our
// server-to-server callback once the payment is resolved.
export function redirectToLiqPay(
  data: string,
  signature: string,
  checkoutUrl: string,
) {
  const form = document.createElement("form");
  form.method = "POST";
  form.action = checkoutUrl;
  form.style.display = "none";

  for (const [name, value] of [
    ["data", data],
    ["signature", signature],
  ]) {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
}
