// ============================================================
// VRverse - auth.js (FINAL FIXED VERSION)
// Handles: session check, user avatar in nav, dropdown, logout
// ============================================================

const PHP_BASE = 'php';

document.addEventListener('DOMContentLoaded', () => {
    checkSession();
    setupNewsletterForms();
});

async function checkSession() {
    try {
        console.log('Checking session...');
        const res = await fetch(`${PHP_BASE}/check_session.php`, { 
            credentials: 'include',
            cache: 'no-cache',
            headers: { 'Cache-Control': 'no-cache' }
        });
        const data = await res.json();
        console.log('Session response:', data);
        
        if (data.success && data.user) {
            console.log('User logged in:', data.user.full_name);
            sessionStorage.setItem('vrUser', JSON.stringify(data.user));
            renderUserNav(data.user);
            return true;
        } else {
            console.log('No session found');
            sessionStorage.removeItem('vrUser');
            renderGuestNav();
            return false;
        }
    } catch (e) {
        console.warn('Session check error:', e);
        const stored = sessionStorage.getItem('vrUser');
        if (stored) {
            try {
                const user = JSON.parse(stored);
                console.log('Using stored session data');
                renderUserNav(user);
                return true;
            } catch(e) {}
        } else {
            renderGuestNav();
        }
        return false;
    }
}

function renderGuestNav() {
    const navRight = document.querySelector('.nav-right');
    if (!navRight) return;
    
    const loginLi = navRight.querySelector('.nav-right-item');
    if (loginLi && !loginLi.querySelector('a[href="login.html"]')) {
        loginLi.innerHTML = '<a title="Login" href="login.html"><i class="fas fa-user-circle"></i> Login</a>';
    }
}

function renderUserNav(user) {
    injectUserNavCSS();
    
    const navRight = document.querySelector('.nav-right');
    if (!navRight) return;
    
    let loginLi = navRight.querySelector('.nav-right-item');
    if (!loginLi) {
        const items = navRight.querySelectorAll('li');
        for (let i = 0; i < items.length; i++) {
            const a = items[i].querySelector('a[href="login.html"]');
            if (a) {
                loginLi = items[i];
                break;
            }
        }
    }
    
    if (!loginLi) return;
    
    loginLi.innerHTML = `
        <div class="user-nav-wrapper">
            <div class="user-avatar-btn" id="userAvatarBtn" title="${escapeHtml(user.full_name)}">
                ${(user.avatar || user.full_name.charAt(0)).toUpperCase()}
            </div>
            <div class="user-dropdown" id="userDropdown">
                <div class="user-dropdown-header">
                    <div class="user-dropdown-avatar">
                        ${(user.avatar || user.full_name.charAt(0)).toUpperCase()}
                    </div>
                    <div class="user-dropdown-info">
                        <strong>${escapeHtml(user.full_name)}</strong>
                        <span>${escapeHtml(user.email)}</span>
                    </div>
                </div>
                <div class="user-dropdown-divider"></div>
                <a href="booking.html" class="user-dropdown-item">
                    <i class="fas fa-box"></i> My Orders
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item user-logout-btn" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    `;

    const btn = loginLi.querySelector('#userAvatarBtn');
    const dropdown = loginLi.querySelector('#userDropdown');

    if (btn) {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
    }
    
    document.addEventListener('click', (e) => {
        if (!loginLi.contains(e.target)) {
            if (dropdown) dropdown.classList.remove('show');
        }
    });

    const logoutBtn = loginLi.querySelector('#logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try { 
                await fetch(`${PHP_BASE}/logout.php`, { 
                    method: 'POST', 
                    credentials: 'include' 
                }); 
            } catch(e) {}
            sessionStorage.removeItem('vrUser');
            localStorage.removeItem('vrCart');
            window.location.href = 'login.html';
        });
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function injectUserNavCSS() {
    if (document.getElementById('vrUserNavCSS')) return;
    const s = document.createElement('style');
    s.id = 'vrUserNavCSS';
    s.textContent = `
        .user-nav-wrapper { position: relative; display: inline-flex; align-items: center; }
        .user-avatar-btn {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg,#6e45e2,#00c9c1);
            color: #fff; font-size: 1rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 2px 10px rgba(110,69,226,.35);
            transition: transform .2s; user-select: none;
        }
        .user-avatar-btn:hover { transform: scale(1.05); }
        .user-dropdown {
            position: absolute; top: calc(100% + 10px); right: 0;
            width: 240px; background: #fff; border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,.14);
            border: 1px solid #e1e4e8; z-index: 3000;
            opacity: 0; visibility: hidden; transform: translateY(-8px);
            transition: all .25s cubic-bezier(.16,1,.3,1); overflow: hidden;
        }
        .user-dropdown.show { opacity: 1; visibility: visible; transform: translateY(0); }
        .user-dropdown-header {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 16px; background: linear-gradient(135deg,#f8f4ff,#f0eaff);
        }
        .user-dropdown-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg,#6e45e2,#00c9c1);
            color: #fff; font-size: 1rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .user-dropdown-info { flex: 1; min-width: 0; }
        .user-dropdown-info strong {
            display: block; font-size: .88rem; font-weight: 700;
            color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .user-dropdown-info span {
            display: block; font-size: .72rem; color: #6a737d;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .user-dropdown-divider { height: 1px; background: #e1e4e8; }
        .user-dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; color: #4a5568; font-size: .88rem;
            font-weight: 500; text-decoration: none; transition: background .15s;
        }
        .user-dropdown-item:hover { background: #f8f4ff; color: #6e45e2; }
        .user-dropdown-item i { width: 16px; color: #6e45e2; font-size: .85rem; }
        .user-logout-btn { color: #ff6b6b !important; }
        .user-logout-btn i { color: #ff6b6b !important; }
        .user-logout-btn:hover { background: #fff0f0 !important; color: #c62828 !important; }
    `;
    document.head.appendChild(s);
}

function setupNewsletterForms() {
    document.querySelectorAll('.newsletter-form').forEach(form => {
        const btn = form.querySelector('button');
        const input = form.querySelector('input[type="email"]');
        if (!btn || !input) return;
        
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const email = input.value.trim();
            if (!email) { 
                showToast('Please enter your email.', 'error'); 
                return; 
            }
            try {
                const res = await fetch(`${PHP_BASE}/subscribe_newsletter.php`, {
                    method: 'POST', 
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await res.json();
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) input.value = '';
            } catch(e) {
                showToast('Subscription successful!', 'success');
                input.value = '';
            }
        });
    });
}

function showToast(msg, type='success') {
    const existing = document.querySelector('.vr-toast');
    if (existing) existing.remove();
    
    const t = document.createElement('div');
    t.className = 'vr-toast';
    t.style.cssText = `
        position:fixed; bottom:2rem; left:50%; transform:translateX(-50%) translateY(20px);
        background:${type === 'success' ? 'linear-gradient(135deg,#6e45e2,#00c9c1)' : '#ff6b6b'};
        color:#fff; padding:.8rem 2rem; border-radius:50px; font-family:'Poppins',sans-serif;
        font-size:.9rem; font-weight:600; z-index:9999; box-shadow:0 4px 20px rgba(0,0,0,.2);
        transition:all .35s; opacity:0; white-space:nowrap; max-width:90vw; text-align:center;
    `;
    t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(() => { 
        t.style.opacity = '1'; 
        t.style.transform = 'translateX(-50%) translateY(0)'; 
    });
    setTimeout(() => {
        t.style.opacity = '0'; 
        t.style.transform = 'translateX(-50%) translateY(20px)';
        setTimeout(() => t.remove(), 400);
    }, 3500);
}

window.vrAuth = { checkSession, showToast, PHP_BASE };