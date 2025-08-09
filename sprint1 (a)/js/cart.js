let cart = JSON.parse(localStorage.getItem('cart')) || [];

function renderCart() {
  const tbody = document.querySelector('#cart-table tbody');
  const grandTotalEl = document.getElementById('grand-total');
  tbody.innerHTML = '';
  let grandTotal = 0;

  cart.forEach((item, index) => {
    let row = document.createElement('tr');
    let total = item.price * item.quantity;
    grandTotal += total;

    row.innerHTML = `
      <td>${item.name}</td>
      <td>$${item.price}</td>
      <td>${item.quantity}</td>
      <td>$${total}</td>
      <td><button class="btn" onclick="removeItem(${index})">Remove</button></td>
    `;
    tbody.appendChild(row);
  });

  grandTotalEl.textContent = 'Grand Total: $' + grandTotal.toFixed(2);
}

function removeItem(index) {
  cart.splice(index, 1);
  localStorage.setItem('cart', JSON.stringify(cart));
  renderCart();
}

renderCart();
