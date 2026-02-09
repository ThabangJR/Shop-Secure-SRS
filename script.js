document.addEventListener('DOMContentLoaded', function() {
    const cartElement = document.getElementById('cart');
    const cartToggle = document.getElementById('cartToggle');
    const categoryFilter = document.getElementById('categoryFilter');
    const cartCount = document.getElementById('cartCount');

    // 1. Cart Toggling
    if (cartToggle) {
        cartToggle.addEventListener('click', () => {
            cartElement.classList.toggle('open');
            if (cartElement.classList.contains('open')) {
                fetchCartData();
            }
        });
    }

    // 2. Product Filtering
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            const selectedCategory = this.value;
            document.querySelectorAll('.product-card').forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                const isMatch = !selectedCategory || cardCategory === selectedCategory;
                card.style.display = isMatch ? 'block' : 'none';
            });
        });
    }

    //AJAX/Interactivity 

    //function to add a product to the cart
    window.addToCart = function(form) {
        const formData = new FormData(form);
        const quantity = parseInt(formData.get('quantity'));
        
        if (quantity < 1 || isNaN(quantity)) return; 

        //add action to the POST body for cart_handler.php
        formData.append('action', 'add'); 

        fetch('cart_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                console.error("Network response failed:", response.status, response.statusText);
                return response.text().then(text => { throw new Error("Server Error: " + text); });
            }
            return response.json(); 
        })
        .then(data => {
            if (data.success) {
                alert(`Added ${quantity} item(s) to cart.`);
                if (cartCount) cartCount.textContent = data.cartCount;
                if (cartElement.classList.contains('open')) {
                    fetchCartData(); // Update cart if it's open
                }
            } else {
                alert(data.message || 'Failed to add product to cart (Backend rejected).');
            }
        })
        .catch(error => {
            console.error('CRITICAL CART ERROR:', error);
            alert('A critical error occurred while adding to cart. Check the console for details.');
        });
    };

    //FUNCTION: to remove single item in the cart
    window.removeItemFromCart = function(productID) {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }

        const formData = new URLSearchParams();
        formData.append('action', 'remove_item');
        formData.append('productID', productID);

        fetch('cart_handler.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (cartCount) cartCount.textContent = data.cartCount;
                fetchCartData(); //reloading the cart UI
            } else {
                alert('Error removing item: ' + (data.message || 'Unknown error.'));
            }
        })
        .catch(error => console.error('Error removing item:', error));
    }


    //FUNTION: clearing the cart
    window.clearCart = function() {
        if (!confirm('Are you sure you want to clear your entire cart?')) {
            return;
        }

        const formData = new URLSearchParams();
        formData.append('action', 'clear');

        fetch('cart_handler.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (cartCount) cartCount.textContent = 0;
                fetchCartData(); //reloading the cart UI
            } else {
                alert('Error clearing cart: ' + (data.message || 'Unknown error.'));
            }
        })
        .catch(error => console.error('Error clearing cart:', error));
    }


    //FUNTION: to fetch and display the current cart from the server
    function fetchCartData() {
        fetch('cart_handler.php?action=view')
            .then(response => response.json())
            .then(data => {
                let html = '<h3>Shopping Cart</h3><ul>';
                let total = 0;

                if (data.cartItems && data.cartItems.length > 0) {
                    data.cartItems.forEach(item => {
                        const subtotal = item.price * item.quantity;
                        total += subtotal;
                        
                       
                        //dynamically generated delete button
                        html += `<li>
                            ${item.name} (${item.quantity} x R${parseFloat(item.price).toFixed(2)}) = R${subtotal.toFixed(2)}
                            <button onclick="removeItemFromCart(${item.productID})" 
                                style="float: right; padding: 3px 6px; margin-left: 10px; font-size: 0.75em; background: #dc3545; color: white; border: none; border-radius: 3px;">
                                X
                            </button>
                        </li>`;
                    });
                    
                    html += `</ul><p><strong>Total: R${total.toFixed(2)}</strong></p>`;
                    
                    //dynamically generated clear cart button
                    html += '<button onclick="clearCart()" style="background-color: #6c757d; margin-bottom: 10px;">Clear Cart</button>';
                    
                    html += '<button onclick="window.location.href=\'checkout.php\'">Proceed to Checkout</button>'; 
                } else {
                    html += '<li style="color: #666;">Your cart is empty.</li></ul>';
                }
                
                if (cartElement) cartElement.innerHTML = html;
                if (cartCount) cartCount.textContent = data.cartCount || 0;
            })
            .catch(error => {
                console.error('Fetch Cart Error:', error);
                if (cartElement) cartElement.innerHTML = '<h3>Cart</h3><p>Could not load cart data. (Check Console)</p>';
            });
    }

    //initial load for cart count and data if it is visible
    fetchCartData();
});