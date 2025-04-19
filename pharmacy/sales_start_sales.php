
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="sale.css">
    <style>
        .checkout-card {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            display: none;
        }
        .receipt-container {
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            width: 95%;
            height: 90%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            font-family: Arial, sans-serif;
            overflow-y: auto;
        }
        .customer-details, .order-details {
            width: 48%;
        }
        .customer-details input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            font-size: 18px;
        }
        .order-details .receipt {
            border: 1px dashed black;
            padding: 10px;
        }
        .close-btn {
            background: red;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            float: right;
        }
        .sales-container {
            display: flex;
            justify-content: space-between;
        }
        .products-card, .cart-card {
            width: 48%;
        }
        .product img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        
    </style>
</head>
<body>
    <div class="sales-container">
        <div class="products-card">
            <h2>Available Products</h2>
            <input type="text" id="search-bar" placeholder="Search for a product..." onkeyup="filterProducts()" />
            <div class="product-list">
                <?php
                require 'connect.php';
                
                $query = "SELECT id, image, selling_price, name, quantity FROM drug_product";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $image = !empty($row['image']) ? $row['image'] : 'default.png';
                    $imagePath = "http://localhost/pharmacy/uploads/{$image}";
                    echo "<div class='product' data-name='{$row['name']}'>";
                    echo "<img src='$imagePath' alt='{$row['name']}' onerror=\"this.onerror=null; this.src='pharmacy/uploads/default.png';\">";
                    echo "<h3>{$row['name']}</h3>";
                    echo "<p>Price: Ksh {$row['selling_price']}</p>";
                    echo "<input type='number' min='1' max='{$row['quantity']}' value='1' id='qty_{$row['id']}' />";
                    echo "<button onclick=\"addToCart({$row['id']}, '" . htmlspecialchars($row['name'], ENT_QUOTES) . "', {$row['selling_price']})\">Add to Cart</button>";

                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div class="cart-card">
            <h2>Shopping Cart</h2>
            <button id="clear-cart" onclick="clearCart()">Clear Cart</button>
            <table id="cart-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cart-items"></tbody>
            </table>
            <button id="checkout" onclick="checkout()">Checkout</button>
        </div>
    </div>

    <div id="checkout-card" class="checkout-card">
        <div class="receipt-container">
            <div class="customer-details">
            
                <h2>Customer Details</h2>
                <label for="customer-name">Name:</label>
                <input type="text" id="customer-name" placeholder="Enter customer name" required>
                
                <label for="customer-date">Date:</label>
                <input type="date" id="customer-date" required>
                
                <label for="customer-amount">Amount Paid:</label>
                <input type="number" id="customer-amount" placeholder="Enter amount paid" required>
                <button onclick="payNow()">Pay Now</button>
                

               
            </div>
            <div class="order-details">
            <button class="close-btn" onclick="closeCheckout()">X</button>
                <h2>Order Details</h2>
                <div class="receipt" id="receipt"></div>
                <button onclick="closeCheckout()">Cancel Order</button>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        function addToCart(id, name, price) {
            let qty = parseInt(document.getElementById('qty_' + id).value);
            if (qty <= 0) return;
            let item = cart.find(p => p.id === id);
            if (item) { item.qty += qty; } else { cart.push({ id, name, price, qty }); }
            updateCartUI();
        }

        function updateCartUI() {
            let cartHTML = '';
            cart.forEach((item, index) => {
                let total = item.price * item.qty;
                cartHTML += `<tr><td>${item.name}</td><td>${item.qty}</td><td>Ksh ${item.price.toFixed(2)}</td><td>Ksh ${total.toFixed(2)}</td><td><button onclick="removeItem(${index})">Remove</button></td></tr>`;
            });
            document.getElementById('cart-items').innerHTML = cartHTML;
        }
        function removeItem(index) {
    cart.splice(index, 1);  // Remove the item at the given index
    updateCartUI();  // Refresh the cart display
}
function clearCart() {
    cart = [];  // Empty the cart array
    updateCartUI();  // Refresh the cart display
    Swal.fire("Cart Cleared", "All items have been removed.", "info");
}


        function checkout() {
    console.log("Checkout function called!"); // Debugging log

    if (cart.length === 0) { 
        Swal.fire("Error", "Your cart is empty!", "error"); 
        return; 
    }

    let checkoutCard = document.getElementById('checkout-card');
    
    if (!checkoutCard) {
        console.error("Error: checkout-card element not found!");
        return;
    }

    // Generate order details (NOT the receipt yet)
    let orderDetailsHTML = `
        <h3>Order Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <th style="border-bottom: 1px solid black; text-align: left;">Product</th>
                <th style="border-bottom: 1px solid black; text-align: center;">Qty</th>
                <th style="border-bottom: 1px solid black; text-align: right;">Total</th>
            </tr>
    `;

    let totalAmount = 0;
    cart.forEach(item => {
        let total = item.price * item.qty;
        totalAmount += total;
        orderDetailsHTML += `
            <tr>
                <td>${item.name}</td>
                <td style="text-align: center;">${item.qty}</td>
                <td style="text-align: right;">Ksh ${total.toFixed(2)}</td>
            </tr>
        `;
    });

    orderDetailsHTML += `</table><hr><p><strong>Total Amount:</strong> Ksh ${totalAmount.toFixed(2)}</p>`;

    // Display Order Details in the right section (not the receipt)
    document.getElementById('receipt').innerHTML = orderDetailsHTML;
    
    // Show the checkout card
    checkoutCard.style.display = "flex";

    console.log("Order details displayed in checkout.");
}


        function generateReceipt(customerName = "Guest", amountPaid = 0, change = 0, salesperson = "Unknown") {
    let receiptHTML = `
        <div style="text-align: center; font-family: Arial, sans-serif;">
            <img src="http://localhost/pharmacy/images/ABC.png" alt="Pharmacy Logo" width="100">
            <h2>Joy’s Pharmacy</h2>
            <p><strong>Customer Name:</strong> ${customerName}</p>
            <p><strong>Salesperson:</strong> ${salesperson}</p>
            <hr>
            <h3>Purchased Items</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <th style="border-bottom: 1px solid black; text-align: left;">Product</th>
                    <th style="border-bottom: 1px solid black; text-align: center;">Qty</th>
                    <th style="border-bottom: 1px solid black; text-align: right;">Total</th>
                </tr>
    `;

    let totalAmount = 0;
    cart.forEach(item => {
        let total = item.price * item.qty;
        totalAmount += total;
        receiptHTML += `
            <tr>
                <td>${item.name}</td>
                <td style="text-align: center;">${item.qty}</td>
                <td style="text-align: right;">Ksh ${total.toFixed(2)}</td>
            </tr>
        `;
    });

    receiptHTML += `
            </table>
            <hr>
            <p><strong>Total Amount:</strong> Ksh ${totalAmount.toFixed(2)}</p>
            <p><strong>Amount Paid:</strong> Ksh ${amountPaid.toFixed(2)}</p>
            <p><strong>Change:</strong> Ksh ${change.toFixed(2)}</p>
            <hr>
            <p><strong>Served by:</strong> ${salesperson}</p>
            <p style="font-style: italic;">Thank you for shopping with us! Get well soon. 💊</p>
        </div>
    `;

    return receiptHTML;
}


        function closeCheckout() {
            document.getElementById('checkout-card').style.display = "none";
        }

        
        
        
        function payNow() {
    let customerName = document.getElementById('customer-name').value.trim();
    let customerDate = document.getElementById('customer-date').value.trim();
    let customerAmount = parseFloat(document.getElementById('customer-amount').value.trim());

    if (!customerName || !customerDate || isNaN(customerAmount)) {
        Swal.fire("Error", "Please enter all customer details correctly!", "error");
        return;
    }

    let cartData = JSON.stringify(cart);
    let salesperson = "<?php echo $_SESSION['firstName']; ?>"; 


    fetch("process_sale.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `customer_name=${customerName}&sale_date=${customerDate}&amount_paid=${customerAmount}&cart=${encodeURIComponent(cartData)}&salesperson=${encodeURIComponent(salesperson)}`
    })
    
    .then(response => response.text())
    .then(text => JSON.parse(text))
    .then(data => {
        if (data.status === "success") {
            let receiptContent = generateReceipt(customerName, customerAmount, data.change, salesperson);

            // Replace Order Details with the Receipt after clicking Pay Now
            document.getElementById('receipt').innerHTML = receiptContent;

            // Print the receipt
            printReceipt(receiptContent);

            // Create a downloadable receipt
            let blob = new Blob([receiptContent], { type: "text/html" });
            let url = URL.createObjectURL(blob);

            Swal.fire({
                title: "Success",
                html: `Sale completed! Change: Ksh ${data.change.toFixed(2)}<br>
                       <a href="${url}" download="receipt.html" class="swal2-confirm swal2-styled">Download Receipt</a>`,
                icon: "success"
            });

            cart = [];
            updateCartUI();
            closeCheckout();
        } else {
            Swal.fire("Error", data.message, "error");
        }
    })
    .catch(error => console.error("Fetch Error:", error));
}



// Function to Auto-Print Receipt
function printReceipt(content) {
    let printWindow = window.open('', '', 'width=600,height=600');
    printWindow.document.write(`<html><head><title>Receipt</title></head><body>${content}</body></html>`);
    printWindow.document.close();
    printWindow.print();
}





        
    </script>
</body>
</html>
