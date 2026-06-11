// js/cart.js - Complete cart functionality

let cart = [];

function loadCart() {
    const stored = localStorage.getItem('vrCart');
    if (stored) {
        try {
            cart = JSON.parse(stored);
            // Fix old cart data - ensure image paths are correct
            fixCartImagePaths();
        } catch(e) {
            cart = [];
        }
    }
    updateCartBadge();
    if (typeof renderCartSidebar === 'function') {
        renderCartSidebar();
    }
}

// NEW FUNCTION: Fix old cart data image paths
function fixCartImagePaths() {
    let modified = false;
    cart.forEach(item => {
        // If image path doesn't have 'images/' prefix, add it
        if (item.image && !item.image.startsWith('images/') && !item.image.startsWith('http')) {
            item.image = 'images/' + item.image;
            modified = true;
        }
        // If no image, try to get from products
        if (!item.image || item.image === 'undefined') {
            const product = getProductById(item.id);
            if (product && product.image) {
                item.image = product.image;
                modified = true;
            }
        }
    });
    if (modified) {
        localStorage.setItem('vrCart', JSON.stringify(cart));
    }
}

function saveCart() {
    localStorage.setItem('vrCart', JSON.stringify(cart));
    updateCartBadge();
    if (typeof renderCartSidebar === 'function') {
        renderCartSidebar();
    }
    window.dispatchEvent(new CustomEvent('cartUpdated', { detail: { cart } }));
}

function updateCartBadge() {
    const badge = document.getElementById('cartBadge');
    if (!badge) return;
    
    const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    if (totalItems > 0) {
        badge.textContent = totalItems;
        badge.classList.add('show');
    } else {
        badge.classList.remove('show');
    }
}

function addToCart(productId, quantity = 1) {
    const product = getProductById(productId);
    if (!product) {
        console.error('Product not found:', productId);
        return false;
    }
    
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
        existing.quantity = (existing.quantity || 1) + quantity;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: quantity,
            image: product.image,  // This is "images/filename.png"
            icon: product.icon,
            storage: product.storage
        });
    }
    saveCart();
    showToast(`${product.name} added to cart!`, 'success');
    return true;
}

function removeFromCart(productId) {
    const product = cart.find(item => item.id === productId);
    if (product) {
        cart = cart.filter(item => item.id !== productId);
        saveCart();
        showToast(`${product.name} removed from cart`, 'info');
    }
}

function updateQuantity(productId, delta) {
    const itemIndex = cart.findIndex(item => item.id === productId);
    if (itemIndex !== -1) {
        const newQty = (cart[itemIndex].quantity || 1) + delta;
        if (newQty <= 0) {
            removeFromCart(productId);
        } else {
            cart[itemIndex].quantity = newQty;
            saveCart();
        }
    }
}

function getCartQuantity(productId) {
    const item = cart.find(i => i.id === productId);
    return item ? (item.quantity || 1) : 0;
}

function getCartTotal() {
    return cart.reduce((sum, item) => sum + ((item.price || 0) * (item.quantity || 1)), 0);
}

function getCartItemCount() {
    return cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
}

function clearCart() {
    cart = [];
    saveCart();
    showToast('Cart cleared', 'info');
}

// UPDATED: Render cart sidebar with better error handling
function renderCartSidebar() {
    const container = document.getElementById('cartItemsContainer');
    const totalSpan = document.getElementById('cartTotalPrice');
    
    if (!container) return;
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="cart-empty-msg">Your cart is empty</p>';
        if (totalSpan) totalSpan.innerText = '৳0.00';
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach(item => {
        const itemTotal = (item.price || 0) * (item.quantity || 1);
        total += itemTotal;
        
        // FIXED: Better image path handling
        let imagePath = item.image;
        
        // If image is missing or invalid, try to get from products
        if (!imagePath || imagePath === 'undefined' || imagePath === 'null') {
            const product = getProductById(item.id);
            if (product && product.image) {
                imagePath = product.image;
            }
        }
        
        // Ensure 'images/' prefix
        if (imagePath && !imagePath.startsWith('images/') && !imagePath.startsWith('http')) {
            imagePath = 'images/' + imagePath;
        }
        
        // Fallback if still no image
        if (!imagePath) {
            imagePath = 'VR-logo.png';
        }
        
        // Debug log (remove in production)
        console.log('Cart item image path:', imagePath);
        
        html += `
            <div class="cart-item" data-id="${item.id}">
                <div class="cart-item-img">
                    <img src="${imagePath}" 
                        alt="${escapeHtml(item.name)}" 
                        style="width:100%;height:100%;object-fit:cover;border-radius:12px;" 
                        onerror="this.style.display='none'; this.parentElement.innerHTML='<i class="fas ${item.icon || 'fa-vr-cardboard'}" style="font-size:2rem;color:#6e45e2;"></i>'">
                </div>
                <div class="cart-item-details">
                    <div class="cart-item-title">${escapeHtml(item.name)}</div>
                    <div class="cart-item-price">৳${(item.price || 0).toLocaleString()}</div>
                    <div class="cart-qty-row">
                        <button class="cart-qty-minus" onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span>${item.quantity || 1}</span>
                        <button class="cart-qty-plus" onclick="updateQuantity(${item.id}, 1)">+</button>
                        <button class="cart-item-remove" onclick="removeFromCart(${item.id})">Remove</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    if (totalSpan) totalSpan.innerText = `৳${total.toLocaleString()}`;
}

// Initialize cart UI (open/close functionality)
function initCartUI() {
    const cartIcon = document.getElementById('cartIcon');
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    const closeCartBtn = document.getElementById('closeCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    function openCart() {
        if (cartSidebar) cartSidebar.classList.add('open');
        if (cartOverlay) cartOverlay.classList.add('active');
        if (typeof renderCartSidebar === 'function') renderCartSidebar();
    }
    
    function closeCart() {
        if (cartSidebar) cartSidebar.classList.remove('open');
        if (cartOverlay) cartOverlay.classList.remove('active');
    }
    
    if (cartIcon) cartIcon.addEventListener('click', openCart);
    if (closeCartBtn) closeCartBtn.addEventListener('click', closeCart);
    if (cartOverlay) cartOverlay.addEventListener('click', closeCart);
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            if (cart.length > 0) {
                window.location.href = 'booking.html';
            } else {
                showToast('Your cart is empty. Add some products first!', 'error');
            }
        });
    }
}