document.addEventListener('DOMContentLoaded', () => {
    /* ===========================
       DELIVERY SLIDER TOGGLE
       =========================== */
    const slider = document.getElementById('deliverySlider');
    const hiddenInput = document.getElementById('deliveryMethodInput');
    const addressGroup = document.querySelector('[data-address-group]');
    const locationGroup = document.querySelector('[data-campus-location]');
    const deliveryFeeRow = document.getElementById('deliveryFeeRow');
    const orderTotalEl = document.getElementById('orderTotal');

    const updateDeliveryUI = (value) => {
        const isCampusDelivery = value === 'Campus Delivery';

        if (addressGroup) {
            addressGroup.classList.toggle('hidden', !isCampusDelivery);
        }
        if (locationGroup) {
            locationGroup.classList.toggle('hidden', !isCampusDelivery);
        }

        // Toggle delivery fee row visibility and update total
        if (deliveryFeeRow && orderTotalEl) {
            deliveryFeeRow.style.display = isCampusDelivery ? 'flex' : 'none';

            const subtotalEl = deliveryFeeRow.previousElementSibling;
            if (subtotalEl) {
                const subtotalText = subtotalEl.querySelector('span:last-child').textContent;
                const subtotal = parseFloat(subtotalText.replace(/[₦,]/g, '')) || 0;

                const feeText = deliveryFeeRow.querySelector('span:last-child').textContent;
                const fee = parseFloat(feeText.replace(/[₦,]/g, '')) || 0;

                const total = isCampusDelivery ? subtotal + fee : subtotal;
                orderTotalEl.textContent = '₦' + total.toLocaleString('en-NG', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }
    };

    if (slider) {
        const options = slider.querySelectorAll('.delivery-slider-option');
        const thumb = slider.querySelector('.delivery-slider-thumb');

        const setActive = (btn) => {
            options.forEach(o => o.classList.remove('active'));
            btn.classList.add('active');

            // Move thumb
            const idx = Array.from(options).indexOf(btn);
            thumb.style.transform = `translateX(${idx * 100}%)`;

            // Update hidden input
            const value = btn.getAttribute('data-value');
            if (hiddenInput) hiddenInput.value = value;

            updateDeliveryUI(value);
        };

        options.forEach(btn => {
            btn.addEventListener('click', () => setActive(btn));
        });

        // Initialize
        const initial = slider.querySelector('.delivery-slider-option.active');
        if (initial) setActive(initial);
    }

    // Legacy radio button support (if any page still uses radios)
    const deliveryOptions = document.querySelectorAll('input[name="delivery_method"][type="radio"]');
    if (deliveryOptions.length > 0) {
        const toggleRadio = () => {
            const selected = document.querySelector('input[name="delivery_method"]:checked');
            if (selected) updateDeliveryUI(selected.value);
        };
        deliveryOptions.forEach(o => o.addEventListener('change', toggleRadio));
        toggleRadio();
    }

    /* ===========================
       RESERVATION TABS
       =========================== */
    const resTabs = document.querySelectorAll('.res-tab');
    const resContents = document.querySelectorAll('.reservation-tab-content');

    resTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetId = tab.getAttribute('data-tab');

            resTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            resContents.forEach(content => {
                if (content.id === targetId) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        });
    });

    /* ===========================
       CHECKOUT CONFIRMATION
       =========================== */
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', (e) => {
            const confirmBox = document.getElementById('confirmOrder');
            if (confirmBox && !confirmBox.checked) {
                e.preventDefault();
                alert('Please confirm your order details before placing the order.');
                return;
            }

            const paymentSelect = checkoutForm.querySelector('select[name="payment_method"]');
            if (paymentSelect && paymentSelect.value === '') {
                e.preventDefault();
                alert('Please select a payment method.');
                return;
            }
        });
    }
});

/* ===========================
   STUDENT SIDEBAR TOGGLE
   =========================== */
(function () {
    var toggle  = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('studentSidebar');
    var overlay = document.getElementById('sidebarOverlay');

    if (!toggle || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('open');
        if (overlay) { overlay.classList.add('visible'); }
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        if (overlay) { overlay.classList.remove('visible'); }
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });
}());
