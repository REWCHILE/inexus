/**
 * INEXUS Chile - Core Client Interactive JS
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Header scroll effect
    const header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // 2. Mobile Drawer Menu Toggle
    const hamburgerBtn = document.getElementById('hamburger-toggle');
    const mobileDrawer = document.getElementById('mobile-drawer');
    const drawerOverlay = document.getElementById('drawer-overlay');
    const drawerClose = document.getElementById('drawer-close');

    function openMobileMenu() {
        if (mobileDrawer && drawerOverlay) {
            mobileDrawer.classList.add('active');
            drawerOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeMobileMenu() {
        if (mobileDrawer && drawerOverlay) {
            mobileDrawer.classList.remove('active');
            drawerOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', openMobileMenu);
    if (drawerClose) drawerClose.addEventListener('click', closeMobileMenu);
    if (drawerOverlay) drawerOverlay.addEventListener('click', closeMobileMenu);

    // 3. FAQ Accordion Toggle
    document.querySelectorAll('.faq-question-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-accordion-item');
            const wasOpen = item.classList.contains('open');
            
            // Close other items in same container
            const container = item.closest('.faqs-wrapper') || document;
            container.querySelectorAll('.faq-accordion-item').forEach(i => i.classList.remove('open'));

            if (!wasOpen) {
                item.classList.add('open');
            }
        });
    });

    // 4. Tab switching
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', () => {
            const targetId = tab.dataset.target;
            const container = tab.closest('.tabs-container');
            if (!container) return;

            container.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
            container.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');

            tab.classList.add('active');
            const targetPane = document.getElementById(targetId);
            if (targetPane) targetPane.style.display = 'block';
        });
    });

    // 5. Checkout Boleta / Factura Fields Toggle
    const boletaRadio = document.getElementById('doc_type_boleta');
    const facturaRadio = document.getElementById('doc_type_factura');
    const invoiceFields = document.getElementById('factura-fields');

    function updateInvoiceFields() {
        if (!invoiceFields) return;
        if (facturaRadio && facturaRadio.checked) {
            invoiceFields.style.display = 'block';
            document.querySelectorAll('#factura-fields input').forEach(inp => inp.setAttribute('required', 'required'));
        } else {
            invoiceFields.style.display = 'none';
            document.querySelectorAll('#factura-fields input').forEach(inp => inp.removeAttribute('required'));
        }
    }

    if (boletaRadio) boletaRadio.addEventListener('change', updateInvoiceFields);
    if (facturaRadio) facturaRadio.addEventListener('change', updateInvoiceFields);
    updateInvoiceFields();

    // 6. Global Toast Notification Helper
    window.showToast = function (message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:10px;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            background: ${type === 'success' ? '#0f172a' : (type === 'error' ? '#dc2626' : '#0284c7')};
            color: #ffffff;
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 280px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        `;

        toast.innerHTML = `
            <span>${type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ')}</span>
            <div style="flex:1;">${message}</div>
        `;

        container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 10);

        // Animate out
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    };

    // 7. AJAX Add to Cart Listeners
    document.querySelectorAll('.ajax-add-to-cart').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Agregando...';
            }

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    window.showToast(data.message, 'success');
                    // Update header badge
                    const badge = document.querySelector('.header-action-btn .badge-count');
                    if (badge) badge.textContent = data.cart_count;
                } else {
                    window.showToast(data.message || 'Error al agregar producto', 'error');
                }
            } catch (err) {
                console.error(err);
                form.submit(); // fallback to standard submit
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    });
});
