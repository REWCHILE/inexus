/**
 * INEXUS Chile - Core Client Interactive JS
 */

// =========================================================================
// 0. GLOBAL BRANDED PAGE LOADER & TRANSITION SYSTEM
// =========================================================================
window.showPageLoader = function (customText = 'Cargando INEXUS...') {
    const loader = document.getElementById('page-loader');
    const textEl = document.getElementById('page-loader-text');
    if (textEl && customText) {
        textEl.textContent = customText;
    }
    if (loader) {
        loader.classList.remove('hidden');
    }
};

window.hidePageLoader = function () {
    const loader = document.getElementById('page-loader');
    if (loader) {
        loader.classList.add('hidden');
    }
};

// Auto-hide preloader on initial page load
window.addEventListener('load', () => {
    setTimeout(window.hidePageLoader, 200);
});

// Safety fallback
setTimeout(window.hidePageLoader, 2200);

// Auto-show loader on page transitions (clicking internal links)
document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href) return;

    if (
        href.startsWith('#') ||
        href.startsWith('javascript:') ||
        href.startsWith('mailto:') ||
        href.startsWith('tel:') ||
        link.hasAttribute('download') ||
        link.getAttribute('target') === '_blank' ||
        link.hasAttribute('data-no-loader') ||
        e.ctrlKey || e.metaKey || e.shiftKey
    ) {
        return;
    }

    try {
        const url = new URL(href, window.location.origin);
        if (url.origin === window.location.origin) {
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash !== '') {
                return;
            }
            window.showPageLoader('Cargando INEXUS...');
        }
    } catch (err) {}
});

// Auto-show loader on standard form submits
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (
        form.classList.contains('ajax-add-to-cart') || 
        form.classList.contains('ajax-add-to-cart-form') || 
        form.hasAttribute('data-no-loader') ||
        form.classList.contains('no-loader') ||
        (form.action && form.action.includes('/carrito/agregar'))
    ) {
        return;
    }
    window.showPageLoader('Procesando solicitud...');
});

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

    // 2.1 Mobile Shop Filter Toggle
    const mobileFilterBtn = document.getElementById('mobile-filter-btn');
    const shopFilterSidebar = document.getElementById('shop-filter-sidebar');
    if (mobileFilterBtn && shopFilterSidebar) {
        mobileFilterBtn.addEventListener('click', () => {
            const isOpen = shopFilterSidebar.classList.toggle('open');
            mobileFilterBtn.classList.toggle('open', isOpen);
        });
    }

    // 2.2 Admin Panel Mobile Sidebar Toggle
    const adminMenuToggle = document.getElementById('admin-menu-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    if (adminMenuToggle && adminSidebar) {
        adminMenuToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('show');
        });
    }

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

    // =========================================================================
    // 7. SLIDE-OVER CART DRAWER & AJAX CART SYSTEM
    // =========================================================================
    const cartDrawer = document.getElementById('cart-drawer');
    const cartDrawerOverlay = document.getElementById('cart-drawer-overlay');
    const cartDrawerClose = document.getElementById('cart-drawer-close');
    const headerCartBtn = document.getElementById('header-cart-btn');
    const cartDrawerItems = document.getElementById('cart-drawer-items');

    window.openCartDrawer = function () {
        if (cartDrawer && cartDrawerOverlay) {
            cartDrawer.classList.add('active');
            cartDrawerOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeCartDrawer = function () {
        if (cartDrawer && cartDrawerOverlay) {
            cartDrawer.classList.remove('active');
            cartDrawerOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    if (cartDrawerClose) cartDrawerClose.addEventListener('click', window.closeCartDrawer);
    if (cartDrawerOverlay) cartDrawerOverlay.addEventListener('click', window.closeCartDrawer);
    if (headerCartBtn) {
        headerCartBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.openCartDrawer();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && cartDrawer && cartDrawer.classList.contains('active')) {
            window.closeCartDrawer();
        }
    });

    function updateCartDrawerUI(data) {
        // Badges
        const headerBadge = document.getElementById('header-cart-badge') || document.querySelector('.header-action-btn .badge-count');
        const drawerBadge = document.getElementById('cart-drawer-badge');
        if (headerBadge) headerBadge.textContent = data.cart_count || 0;
        if (drawerBadge) drawerBadge.textContent = data.cart_count || 0;

        // Items HTML
        if (cartDrawerItems && data.html) {
            cartDrawerItems.innerHTML = data.html;
        }

        // Totals
        const subtotalEl = document.getElementById('drawer-subtotal-val');
        const totalEl = document.getElementById('drawer-total-val');
        const shippingEl = document.getElementById('drawer-shipping-val');
        const footerEl = document.getElementById('cart-drawer-footer');
        const shippingNotice = document.getElementById('cart-drawer-shipping-notice');

        if (subtotalEl) subtotalEl.textContent = data.cart_subtotal || '$0 CLP';
        if (totalEl) totalEl.textContent = data.cart_total || '$0 CLP';
        if (shippingEl) shippingEl.textContent = data.shipping || 'GRATIS';

        if (footerEl) {
            footerEl.style.display = (data.cart_count > 0) ? 'block' : 'none';
        }

        if (shippingNotice) {
            if (data.cart_count > 0 && data.has_free_shipping) {
                shippingNotice.innerHTML = '<span class="shipping-unlocked">🎉 ¡Felicidades! Tienes <strong>Despacho GRATIS</strong></span>';
            } else if (data.cart_count > 0) {
                shippingNotice.innerHTML = `<span>Agrega <strong>${data.free_shipping_diff}</strong> más para <strong>Despacho GRATIS</strong></span>`;
            } else {
                shippingNotice.innerHTML = '<span>🇨🇱 Despacho express a todo Chile | Factura SII</span>';
            }
        }
    }

    // Helper to send AJAX Cart Updates (add, change quantity, delete)
    async function postCartAction(url, payload) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                       || document.querySelector('input[name="_token"]')?.value;

        const bodyData = (payload instanceof FormData) ? payload : new FormData();
        if (!(payload instanceof FormData)) {
            for (const [key, val] of Object.entries(payload)) {
                bodyData.append(key, val);
            }
        }

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: bodyData
        });

        return await res.json();
    }

    // Bind AJAX Cart additions (Product detail page form and catalog quick-add)
    function bindAjaxAddToCart(container = document) {
        container.querySelectorAll('.ajax-add-to-cart:not([data-bound]), .ajax-add-to-cart-form:not([data-bound])').forEach(form => {
            form.setAttribute('data-bound', 'true');
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (typeof window.hidePageLoader === 'function') {
                    window.hidePageLoader();
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('loading');
                }

                try {
                    const formData = new FormData(form);
                    const data = await postCartAction(form.action, formData);

                    if (data.success) {
                        updateCartDrawerUI(data);
                        window.showToast(data.message, 'success');
                        window.openCartDrawer();
                    } else {
                        window.showToast(data.message || 'Error al agregar producto', 'error');
                    }
                } catch (err) {
                    console.error(err);
                    form.submit();
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('loading');
                        submitBtn.innerHTML = originalText;
                    }
                }
            });
        });
    }

    bindAjaxAddToCart();

    // Drawer interactive buttons (Quantity +/- and remove item)
    if (cartDrawerItems) {
        cartDrawerItems.addEventListener('click', async (e) => {
            const plusBtn = e.target.closest('.btn-qty-plus');
            const minusBtn = e.target.closest('.btn-qty-minus');
            const removeBtn = e.target.closest('.btn-remove-item');

            if (plusBtn) {
                const id = plusBtn.dataset.id;
                const qtyEl = document.getElementById(`drawer-qty-${id}`);
                const currentQty = parseInt(qtyEl?.textContent || '1', 10);
                const data = await postCartAction('/carrito/actualizar', { product_id: id, quantity: currentQty + 1 });
                if (data.success) updateCartDrawerUI(data);
            } else if (minusBtn) {
                const id = minusBtn.dataset.id;
                const qtyEl = document.getElementById(`drawer-qty-${id}`);
                const currentQty = parseInt(qtyEl?.textContent || '1', 10);
                if (currentQty > 1) {
                    const data = await postCartAction('/carrito/actualizar', { product_id: id, quantity: currentQty - 1 });
                    if (data.success) updateCartDrawerUI(data);
                } else {
                    const data = await postCartAction('/carrito/eliminar', { product_id: id });
                    if (data.success) updateCartDrawerUI(data);
                }
            } else if (removeBtn) {
                const id = removeBtn.dataset.id;
                const data = await postCartAction('/carrito/eliminar', { product_id: id });
                if (data.success) {
                    updateCartDrawerUI(data);
                    window.showToast('Producto eliminado del carrito', 'info');
                }
            }
        });
    }

    // =========================================================================
    // 7.1 PRODUCT GALLERY CAROUSEL WITH ARROWS & ELEGANT INFINITE AUTOPLAY
    // =========================================================================
    function initProductGalleryCarousel() {
        const galleryContainer = document.getElementById('product-gallery-container');
        const mainImg = document.getElementById('main-product-img');
        const prevBtn = document.getElementById('gallery-prev-btn');
        const nextBtn = document.getElementById('gallery-next-btn');
        const counterEl = document.getElementById('gallery-counter');
        const thumbs = document.querySelectorAll('.gallery-thumb-item');

        if (!mainImg || thumbs.length <= 1) return;

        let currentIndex = 0;
        const totalImages = thumbs.length;
        let autoplayTimer = null;
        let isPaused = false;

        function showImage(index, animate = true) {
            if (index < 0) index = totalImages - 1;
            if (index >= totalImages) index = 0;
            currentIndex = index;

            const targetThumb = thumbs[currentIndex];
            if (!targetThumb) return;

            const newSrc = targetThumb.dataset.src;

            if (animate) {
                mainImg.classList.add('fade-out');
                setTimeout(() => {
                    mainImg.src = newSrc;
                    mainImg.classList.remove('fade-out');
                }, 140);
            } else {
                mainImg.src = newSrc;
            }

            // Update active thumbnail border & scroll thumbnail into view smoothly
            thumbs.forEach((t, i) => {
                if (i === currentIndex) {
                    t.classList.add('active');
                    t.style.borderColor = 'var(--primary)';
                    t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    t.classList.remove('active');
                    t.style.borderColor = '#e2e8f0';
                }
            });

            // Update indicator counter
            if (counterEl) {
                counterEl.textContent = `${currentIndex + 1} / ${totalImages}`;
            }
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                showImage(currentIndex + 1);
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                showImage(currentIndex - 1);
            });
        }

        thumbs.forEach((thumb, idx) => {
            thumb.addEventListener('click', (e) => {
                e.preventDefault();
                showImage(idx);
            });
        });

        // Elegant Slow Infinite Autoplay:
        function startAutoplay() {
            stopAutoplay();
            autoplayTimer = setInterval(() => {
                if (!isPaused) {
                    showImage(currentIndex + 1);
                }
            }, 4200); // 4.2 seconds for slow, luxurious cadence
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        }

        if (galleryContainer) {
            galleryContainer.addEventListener('mouseenter', () => { isPaused = true; });
            galleryContainer.addEventListener('mouseleave', () => { isPaused = false; });
            galleryContainer.addEventListener('touchstart', () => { isPaused = true; }, { passive: true });
            galleryContainer.addEventListener('touchend', () => { isPaused = false; });
        }

        // Slow elegant start after initial 1.8 seconds
        setTimeout(() => {
            startAutoplay();
        }, 1800);
    }

    initProductGalleryCarousel();


    // =========================================================================
    // 8. DYNAMIC MAGNETIC CIRCULAR CURSOR FOLLOWER
    // =========================================================================
    const cursorDot = document.getElementById('cursor-dot');
    const cursorCircle = document.getElementById('cursor-circle');
    const cursorText = document.getElementById('cursor-text');

    if (cursorDot && cursorCircle && window.matchMedia('(pointer: fine)').matches) {
        let mouseX = -100;
        let mouseY = -100;
        let circleX = -100;
        let circleY = -100;
        let isVisible = false;

        window.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;

            if (!isVisible) {
                isVisible = true;
                cursorDot.style.opacity = '1';
                cursorCircle.style.opacity = '1';
                circleX = mouseX;
                circleY = mouseY;
            }

            cursorDot.style.left = `${mouseX}px`;
            cursorDot.style.top = `${mouseY}px`;
        });

        document.addEventListener('mouseleave', () => {
            isVisible = false;
            cursorDot.style.opacity = '0';
            cursorCircle.style.opacity = '0';
        });

        document.addEventListener('mouseenter', () => {
            isVisible = true;
            cursorDot.style.opacity = '1';
            cursorCircle.style.opacity = '1';
        });

        // Smooth physics loop with linear interpolation (lerp)
        function renderCursor() {
            if (isVisible) {
                circleX += (mouseX - circleX) * 0.18;
                circleY += (mouseY - circleY) * 0.18;
                cursorCircle.style.left = `${circleX}px`;
                cursorCircle.style.top = `${circleY}px`;
            }
            requestAnimationFrame(renderCursor);
        }
        requestAnimationFrame(renderCursor);

        // Click Ripple & Active State
        window.addEventListener('mousedown', (e) => {
            cursorCircle.classList.add('cursor-active');
            cursorDot.classList.add('cursor-active');

            // Spawn dynamic click ripple
            const ripple = document.createElement('div');
            ripple.className = 'cursor-ripple';
            ripple.style.left = `${e.clientX}px`;
            ripple.style.top = `${e.clientY}px`;
            document.body.appendChild(ripple);
            setTimeout(() => ripple.remove(), 550);
        });

        window.addEventListener('mouseup', () => {
            cursorCircle.classList.remove('cursor-active');
            cursorDot.classList.remove('cursor-active');
        });

        // Delegated Hover Detection for Interactive Elements
        document.addEventListener('mouseover', (e) => {
            const target = e.target.closest('a, button, input, select, textarea, .btn, .btn-quick-add, .product-card, .thumb-item, .faq-question-btn, .tab-btn, [role="button"], label[for]');
            if (target) {
                cursorCircle.classList.add('cursor-hover');
                cursorDot.classList.add('cursor-hover');

                if (cursorText) {
                    if (target.closest('.btn-quick-add')) {
                        cursorText.textContent = '+';
                    } else if (target.closest('.product-card')) {
                        cursorText.textContent = 'VER';
                    } else if (target.tagName === 'BUTTON' || target.classList.contains('btn')) {
                        cursorText.textContent = 'CLICK';
                    } else if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
                        cursorText.textContent = '';
                    } else {
                        cursorText.textContent = 'LINK';
                    }
                }
            }
        });

        document.addEventListener('mouseout', (e) => {
            const target = e.target.closest('a, button, input, select, textarea, .btn, .btn-quick-add, .product-card, .thumb-item, .faq-question-btn, .tab-btn, [role="button"], label[for]');
            if (target) {
                cursorCircle.classList.remove('cursor-hover');
                cursorDot.classList.remove('cursor-hover');
                if (cursorText) cursorText.textContent = '';
            }
        });
    }

    // =========================================================================
    // 9. MODERN INFINITE SCROLLING ENGINE (FOR SHOP & ALL CATALOG LISTINGS)
    // =========================================================================
    const infiniteContainer = document.getElementById('infinite-scroll-container');
    const catalogGrid = document.getElementById('products-catalog-grid');
    const loadMoreBtn = document.getElementById('btn-load-more');
    const loadingIndicator = document.getElementById('infinite-scroll-loading');
    const manualWrap = document.getElementById('infinite-scroll-manual');
    const endIndicator = document.getElementById('infinite-scroll-end');

    if (infiniteContainer && catalogGrid) {
        let isLoading = false;
        let hasMore = infiniteContainer.dataset.hasMore === '1';
        let nextPageUrl = infiniteContainer.dataset.nextPage;

        async function loadNextPage() {
            if (isLoading || !hasMore || !nextPageUrl) return;

            isLoading = true;
            if (loadingIndicator) loadingIndicator.style.display = 'flex';
            if (manualWrap) manualWrap.style.display = 'none';

            try {
                const response = await fetch(nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Error al cargar página: ' + response.status);

                const data = await response.json();

                if (data.success && data.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    const newCards = Array.from(temp.querySelectorAll('.product-card'));

                    newCards.forEach((card, idx) => {
                        card.classList.add('animate-in');
                        card.style.animationDelay = `${idx * 0.05}s`;
                        catalogGrid.appendChild(card);
                    });

                    // Rebind ajax add to cart on new cards
                    bindAjaxAddToCart(catalogGrid);

                    // Update state
                    hasMore = data.has_more;
                    nextPageUrl = data.next_page_url;
                    infiniteContainer.dataset.hasMore = hasMore ? '1' : '0';
                    infiniteContainer.dataset.nextPage = nextPageUrl || '';

                    if (!hasMore) {
                        if (loadingIndicator) loadingIndicator.style.display = 'none';
                        if (manualWrap) manualWrap.style.display = 'none';
                        if (endIndicator) endIndicator.style.display = 'block';
                    } else {
                        if (loadingIndicator) loadingIndicator.style.display = 'none';
                        if (manualWrap) manualWrap.style.display = 'block';
                    }
                }
            } catch (err) {
                console.error('Infinite scroll error:', err);
                if (manualWrap) manualWrap.style.display = 'block';
            } finally {
                isLoading = false;
                if (loadingIndicator && !hasMore) loadingIndicator.style.display = 'none';
            }
        }

        // 1. Intersection Observer for seamless auto-trigger
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                const entry = entries[0];
                if (entry.isIntersecting && hasMore && !isLoading) {
                    loadNextPage();
                }
            }, {
                root: null,
                rootMargin: '250px 0px',
                threshold: 0.1
            });

            observer.observe(infiniteContainer);
        }

        // 2. Manual click fallback
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', loadNextPage);
        }
    }

    // =========================================================================
    // 9. ELEGANT SLOW HORIZONTAL CATEGORIES CAROUSEL / INFINITE SCROLL
    // =========================================================================
    const catContainer = document.getElementById('categories-scroll-container');
    const catTrack = document.getElementById('categories-cards-track');
    const catPrevBtn = document.getElementById('cat-prev-btn');
    const catNextBtn = document.getElementById('cat-next-btn');

    if (catContainer && catTrack) {
        let isPaused = false;
        let isDown = false;
        let startX = 0;
        let scrollStartLeft = 0;
        let resumeTimeout = null;
        let hasDragged = false;
        let currentScrollPos = 0;
        const scrollSpeed = 0.22; // Ultra calm, slow, showroom elegance (~13px per second)

        // Pause on mouse hover & focus
        catContainer.addEventListener('mouseenter', () => { isPaused = true; });
        catContainer.addEventListener('mouseleave', () => {
            if (!isDown) isPaused = false;
        });

        // Touch handling for mobile & tablet
        catContainer.addEventListener('touchstart', () => {
            isPaused = true;
            clearTimeout(resumeTimeout);
        }, { passive: true });

        catContainer.addEventListener('touchend', () => {
            clearTimeout(resumeTimeout);
            currentScrollPos = catContainer.scrollLeft;
            resumeTimeout = setTimeout(() => { isPaused = false; }, 2000);
        }, { passive: true });

        // Mouse Drag to scroll freely
        catContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            isPaused = true;
            hasDragged = false;
            catContainer.classList.add('is-dragging');
            startX = e.pageX - catContainer.offsetLeft;
            scrollStartLeft = catContainer.scrollLeft;
            clearTimeout(resumeTimeout);
        });

        window.addEventListener('mouseup', () => {
            if (isDown) {
                isDown = false;
                catContainer.classList.remove('is-dragging');
                currentScrollPos = catContainer.scrollLeft;
                clearTimeout(resumeTimeout);
                resumeTimeout = setTimeout(() => { isPaused = false; }, 2000);
            }
        });

        catContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - catContainer.offsetLeft;
            const walk = (x - startX);
            if (Math.abs(walk) > 4) {
                hasDragged = true;
            }
            catContainer.scrollLeft = scrollStartLeft - walk;
            currentScrollPos = catContainer.scrollLeft;
        });

        // Prevent accidental card clicks when dragging
        catContainer.querySelectorAll('.category-card').forEach((card) => {
            card.addEventListener('click', (e) => {
                if (hasDragged) {
                    e.preventDefault();
                }
            });
        });

        // Prev & Next Manual Buttons
        if (catPrevBtn) {
            catPrevBtn.addEventListener('click', () => {
                isPaused = true;
                catContainer.scrollBy({ left: -260, behavior: 'smooth' });
                clearTimeout(resumeTimeout);
                resumeTimeout = setTimeout(() => {
                    currentScrollPos = catContainer.scrollLeft;
                    isPaused = false;
                }, 2500);
            });
        }

        if (catNextBtn) {
            catNextBtn.addEventListener('click', () => {
                isPaused = true;
                catContainer.scrollBy({ left: 260, behavior: 'smooth' });
                clearTimeout(resumeTimeout);
                resumeTimeout = setTimeout(() => {
                    currentScrollPos = catContainer.scrollLeft;
                    isPaused = false;
                }, 2500);
            });
        }

        // True Infinite Loop calculation with precise sub-pixel float
        function stepAutoScroll() {
            if (!isPaused && !isDown) {
                currentScrollPos += scrollSpeed;
                catContainer.scrollLeft = currentScrollPos;

                const setWidth = catTrack.scrollWidth / 3;
                if (setWidth > 0) {
                    if (currentScrollPos >= setWidth * 2) {
                        currentScrollPos -= setWidth;
                        catContainer.scrollLeft = currentScrollPos;
                    } else if (currentScrollPos <= 5) {
                        currentScrollPos += setWidth;
                        catContainer.scrollLeft = currentScrollPos;
                    }
                }
            } else if (isDown) {
                currentScrollPos = catContainer.scrollLeft;
            }
            requestAnimationFrame(stepAutoScroll);
        }

        // Initialize scroll position in the center set (Set 2 of 3)
        setTimeout(() => {
            const setWidth = catTrack.scrollWidth / 3;
            if (setWidth > 0) {
                currentScrollPos = setWidth;
                catContainer.scrollLeft = currentScrollPos;
            }
            requestAnimationFrame(stepAutoScroll);
        }, 150);
    }

    // =========================================================================
    // 10. DYNAMIC MULTI-SLIDE HERO BANNER & PRODUCT MINI-CAROUSEL
    // =========================================================================
    const heroSlider = document.getElementById('hero-slider');
    const heroSlides = document.querySelectorAll('.hero-slide');
    const heroPrevBtn = document.getElementById('hero-main-prev');
    const heroNextBtn = document.getElementById('hero-main-next');
    const heroPills = document.querySelectorAll('.hero-pagination-pill');

    if (heroSlider && heroSlides.length > 0) {
        let currentSlideIndex = 0;
        let heroSlideTimer = null;
        let isHeroHovered = false;
        const slideInterval = 7000; // 7 seconds per slide

        function showSlide(index) {
            if (index < 0) index = heroSlides.length - 1;
            if (index >= heroSlides.length) index = 0;
            currentSlideIndex = index;

            heroSlides.forEach((slide, idx) => {
                if (idx === currentSlideIndex) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });

            heroPills.forEach((pill, idx) => {
                if (idx === currentSlideIndex) {
                    pill.classList.add('active');
                } else {
                    pill.classList.remove('active');
                }
            });
        }

        function nextSlide() {
            showSlide(currentSlideIndex + 1);
        }

        function prevSlide() {
            showSlide(currentSlideIndex - 1);
        }

        function startHeroAutoplay() {
            stopHeroAutoplay();
            heroSlideTimer = setInterval(() => {
                if (!isHeroHovered) {
                    nextSlide();
                }
            }, slideInterval);
        }

        function stopHeroAutoplay() {
            if (heroSlideTimer) clearInterval(heroSlideTimer);
        }

        if (heroPrevBtn) {
            heroPrevBtn.addEventListener('click', () => {
                prevSlide();
                startHeroAutoplay();
            });
        }

        if (heroNextBtn) {
            heroNextBtn.addEventListener('click', () => {
                nextSlide();
                startHeroAutoplay();
            });
        }

        heroPills.forEach((pill) => {
            pill.addEventListener('click', () => {
                const targetIndex = parseInt(pill.dataset.slide, 10);
                if (!isNaN(targetIndex)) {
                    showSlide(targetIndex);
                    startHeroAutoplay();
                }
            });
        });

        heroSlider.addEventListener('mouseenter', () => { isHeroHovered = true; });
        heroSlider.addEventListener('mouseleave', () => { isHeroHovered = false; });

        // Touch swipe on mobile for main hero
        let heroTouchStartX = 0;
        heroSlider.addEventListener('touchstart', (e) => {
            isHeroHovered = true;
            heroTouchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        heroSlider.addEventListener('touchend', (e) => {
            const diffX = e.changedTouches[0].screenX - heroTouchStartX;
            if (diffX < -50) {
                nextSlide();
            } else if (diffX > 50) {
                prevSlide();
            }
            setTimeout(() => { isHeroHovered = false; }, 2000);
        }, { passive: true });

        // Product Mini-Carousels inside each slide
        document.querySelectorAll('.hero-product-carousel').forEach((carousel) => {
            const items = carousel.querySelectorAll('.hero-prod-item');
            const dots = carousel.querySelectorAll('.hero-prod-dot');
            const prevBtn = carousel.querySelector('.hero-prod-prev');
            const nextBtn = carousel.querySelector('.hero-prod-next');
            let currentProdIdx = 0;

            function showProduct(pIdx) {
                if (items.length <= 1) return;
                if (pIdx < 0) pIdx = items.length - 1;
                if (pIdx >= items.length) pIdx = 0;
                currentProdIdx = pIdx;

                items.forEach((item, idx) => {
                    item.classList.toggle('active', idx === currentProdIdx);
                });

                dots.forEach((dot, idx) => {
                    dot.classList.toggle('active', idx === currentProdIdx);
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showProduct(currentProdIdx - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showProduct(currentProdIdx + 1);
                });
            }

            dots.forEach((dot) => {
                dot.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const targetIdx = parseInt(dot.dataset.goto, 10);
                    if (!isNaN(targetIdx)) {
                        showProduct(targetIdx);
                    }
                });
            });
        });

        startHeroAutoplay();
    }
});
