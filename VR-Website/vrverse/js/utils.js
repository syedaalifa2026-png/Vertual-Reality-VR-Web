// js/utils.js - Common utility functions

// XSS Protection
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Toast notification
function showToast(message, type = 'success') {
    // Remove existing toast if any
    const existingToast = document.querySelector('.custom-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        z-index: 9999;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideUp 0.3s ease;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animation style if not exists
if (!document.querySelector('#toast-animation-style')) {
    const style = document.createElement('style');
    style.id = 'toast-animation-style';
    style.textContent = `
        @keyframes slideUp {
            from {
                transform: translateX(-50%) translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
}

// Header initialization (mobile menu, scroll effects)
function initHeader() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');
    
    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', () => {
            mainNav.classList.toggle('active');
            mobileMenuBtn.innerHTML = mainNav.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });
    }
    
    // Close menu when clicking on links (mobile)
    document.querySelectorAll('#mainNav a, #mainNav .nav-right-item, #mainNav .cart-icon-wrapper').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768 && mainNav) {
                mainNav.classList.remove('active');
                if (mobileMenuBtn) mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    });
    
    // Scroll effects
    window.addEventListener('scroll', () => {
        const header = document.getElementById('main-header');
        const backToTop = document.getElementById('backToTop');
        
        if (header) {
            header.classList.toggle('scrolled', window.scrollY > 100);
        }
        if (backToTop) {
            backToTop.classList.toggle('active', window.scrollY > 300);
        }
    });
    
    // Back to top button
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
}

// Format price
function formatPrice(price) {
    return `৳${price.toLocaleString()}`;
}

// Get delivery charge based on location
const deliveryCharges = {
    'Dhaka': 0, 
    'Dhaka Metro': 0, 
    'Chattogong': 100, 
    'Rajshahi': 120,
    'Khulna': 120, 
    'Barishal': 150, 
    'Sylhet': 150, 
    'Rangpur': 130, 
    'Mymensingh': 130
};

function getDeliveryCharge(location) {
    return deliveryCharges[location] || 0;
}

// Newsletter subscription
function initNewsletter() {
    document.querySelectorAll('.newsletter-form button').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const input = btn.closest('.newsletter-form')?.querySelector('input');
            const email = input?.value.trim();
            if (email && email.includes('@')) {
                showToast(`Thanks for subscribing, ${email}!`, 'success');
                input.value = '';
            } else {
                showToast('Please enter a valid email address.', 'error');
            }
        });
    });
}

// Footer link handlers
function initFooterLinks() {
    document.querySelectorAll('.footer-links a, .footer-contact a').forEach(link => {
        link.addEventListener('click', (e) => {
            if (link.getAttribute('href') === '#') {
                e.preventDefault();
                showToast(`Coming Soon: ${link.innerText.trim()}`, 'info');
            }
        });
    });
}

// Initialize everything
document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initNewsletter();
    initFooterLinks();
    loadCart();
    initCartUI();
});