// const cartItemsContainer = document.querySelector(".cart-items");
// const cartTotal = document.querySelector("#cart-total");
// let total = 0;

// const cart = JSON.parse(localStorage.getItem("cart")) || [];

// if (cart.length === 0) {
//   cartItemsContainer.innerHTML = "<p>Your cart is empty!</p>";
// } else {
//   cart.forEach((item, index) => {
//     // Parse weight and calculate the total price
//     const weightConversion = {
//       "1/4": 0.25,
//       "1/2": 0.5,
//       "3/4": 0.75,
//       "1KG": 1,
//     };
//     const weightInKg = weightConversion[item.weight] || 1;
//     const itemTotalPrice = (item.price * weightInKg).toFixed(2);

//     const itemElement = document.createElement("div");
//     itemElement.classList.add("cart-item");
//     itemElement.innerHTML = `
//       <div>
//         <span>${item.name}</span>
//       </div>
//       <div>₱${item.price.toFixed(2)}</div>
//       <div>
//         <select class="weight-select" data-index="${index}">
//           <option value="1/4" ${item.weight === "1/4" ? "selected" : ""}>1/4</option>
//           <option value="1/2" ${item.weight === "1/2" ? "selected" : ""}>1/2</option>
//           <option value="3/4" ${item.weight === "3/4" ? "selected" : ""}>3/4</option>
//           <option value="1KG" ${item.weight === "1KG" ? "selected" : ""}>1KG</option>
//         </select>
//       </div>
//       <div class="item-total-price">₱${itemTotalPrice}</div>
//       <div>
//         <button class="remove-btn" data-index="${index}">
//           <i class="ri-delete-bin-line"></i>
//         </button>
//       </div>
//     `;

//     cartItemsContainer.appendChild(itemElement);
//     total += parseFloat(itemTotalPrice);
//   });
// }

// // Update the total price in the cart
// cartTotal.textContent = `₱${total.toFixed(2)}`;

// // Handle weight change in the cart
// cartItemsContainer.addEventListener("change", (e) => {
//   if (e.target.classList.contains("weight-select")) {
//     const index = e.target.dataset.index;
//     const newWeight = e.target.value;

//     const weightConversion = {
//       "1/4": 0.25,
//       "1/2": 0.5,
//       "3/4": 0.75,
//       "1KG": 1,
//     };
//     const weightInKg = weightConversion[newWeight] || 1;

//     // Update the cart item with the new weight and recalculate the total price
//     cart[index].weight = newWeight;
//     cart[index].totalPrice = (cart[index].price * weightInKg).toFixed(2);
//     localStorage.setItem("cart", JSON.stringify(cart));

//     // Update the UI
//     e.target.closest(".cart-item").querySelector(".item-total-price").textContent = `₱${cart[index].totalPrice}`;
//     updateCartTotal();
//   }
// });

// // Remove item from the cart
// cartItemsContainer.addEventListener("click", (e) => {
//   if (e.target.closest(".remove-btn")) {
//     const index = e.target.closest(".remove-btn").dataset.index;
//     cart.splice(index, 1);
//     localStorage.setItem("cart", JSON.stringify(cart));
//     location.reload();
//   }
// });

// // Function to update the cart total price
// function updateCartTotal() {
//   let newTotal = 0;
//   cart.forEach((item) => {
//     newTotal += parseFloat(item.totalPrice);
//   });
//   cartTotal.textContent = `₱${newTotal.toFixed(2)}`;
// }
