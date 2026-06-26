document.addEventListener('DOMContentLoaded', () => {
    console.debug('main.js loaded');
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

    const passwordToggleButtons = document.querySelectorAll('.password-toggle');
    passwordToggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const wrapper = button.closest('.password-group');
            if (!wrapper) return;
            const input = wrapper.querySelector('input');
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = 'Hide';
            } else {
                input.type = 'password';
                button.textContent = 'Show';
            }
        });
    });

    const userRole = document.body.dataset.userRole;
    const orderStatusEndpoint = document.body.dataset.orderStatusEndpoint;
    if (userRole === 'student' && orderStatusEndpoint) {
        const storageKey = 'rectemOrderStatuses';
        const notificationKey = 'rectemOrderNotification';
        let initialized = false;

        const parseStoredMap = () => {
            try {
                return JSON.parse(localStorage.getItem(storageKey)) || {};
            } catch {
                return {};
            }
        };

        const saveStoredMap = (map) => {
            localStorage.setItem(storageKey, JSON.stringify(map));
        };

        const createToastContainer = () => {
            let container = document.getElementById('orderStatusToastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'orderStatusToastContainer';
                container.className = 'order-status-toast-container';
                document.body.appendChild(container);
            }
            return container;
        };

        const showOrderToast = (orderId, status, message) => {
            const container = createToastContainer();
            const toast = document.createElement('div');
            toast.className = 'order-status-toast';

            const header = document.createElement('div');
            header.className = 'order-status-toast-header';

            const title = document.createElement('strong');
            title.textContent = 'Order Update';

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'toast-close';
            closeButton.setAttribute('aria-label', 'Close notification');
            closeButton.textContent = '×';
            closeButton.addEventListener('click', () => {
                toast.classList.remove('visible');
                setTimeout(() => toast.remove(), 300);
            });

            header.appendChild(title);
            header.appendChild(closeButton);

            const body = document.createElement('div');
            body.className = 'order-status-toast-body';
            const bodyText = document.createElement('p');
            bodyText.textContent = message;
            body.appendChild(bodyText);

            toast.appendChild(header);
            toast.appendChild(body);

            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.add('visible');
            });

            setTimeout(() => {
                toast.classList.remove('visible');
                setTimeout(() => toast.remove(), 300);
            }, 7000);
        };

        const broadcastUpdate = (payload) => {
            try {
                localStorage.setItem(notificationKey, JSON.stringify(payload));
            } catch (_) {
                // ignore storage write failures
            }
        };

        const handleUpdates = (orders) => {
            const previousStatus = parseStoredMap();
            const nextStatus = {};
            const changedOrders = [];
            const hasPreviousStatus = Object.keys(previousStatus).length > 0;

            orders.forEach((order) => {
                nextStatus[order.id] = order.order_status;
                const oldStatus = previousStatus[order.id];

                if (hasPreviousStatus) {
                    if (oldStatus && oldStatus !== order.order_status) {
                        changedOrders.push(order);
                    } else if (!oldStatus && order.order_status !== 'Pending') {
                        changedOrders.push(order);
                    }
                } else if (order.order_status !== 'Pending') {
                    changedOrders.push(order);
                }
            });

            saveStoredMap(nextStatus);
            initialized = true;

            changedOrders.forEach((order) => {
                const message = `Order #${order.id} has been updated to ${order.order_status}.`;
                showOrderToast(order.id, order.order_status, message);
                broadcastUpdate({orderId: order.id, status: order.order_status, message, timestamp: Date.now()});
            });
        };

        const currentBase = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/');
        const resolvedEndpoint = orderStatusEndpoint && orderStatusEndpoint.trim() !== ''
            ? orderStatusEndpoint
            : null;
        const alternateEndpoints = [
            resolvedEndpoint,
            currentBase + 'order-status-updates.php',
            window.location.origin + '/rectem_Resturant/order-status-updates.php',
            window.location.origin + '/order-status-updates.php'
        ].filter(Boolean);

        console.debug('Order status polling enabled, endpoints:', alternateEndpoints);

        const fetchEndpoint = (index = 0) => {
            if (index >= alternateEndpoints.length) {
                return Promise.reject(new Error('No order status endpoint available'));
            }

            const url = alternateEndpoints[index];
            return fetch(url, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json'
                }
            }).then((response) => {
                if (response.ok) {
                    return response.json();
                }
                if (response.status === 404 || response.status === 500) {
                    return fetchEndpoint(index + 1);
                }
                throw new Error('Network response was not ok: ' + response.status);
            });
        };

        const pollOrderStatus = () => {
            fetchEndpoint()
                .then((data) => {
                    console.debug('Order status polling response:', data);
                    if (data && Array.isArray(data.orders)) {
                        handleUpdates(data.orders);
                    }
                })
                .catch((error) => {
                    console.warn('Order status polling failed:', error);
                });
        };

        window.addEventListener('storage', (event) => {
            if (event.key !== notificationKey || !event.newValue) {
                return;
            }

            try {
                const payload = JSON.parse(event.newValue);
                if (payload && payload.message) {
                    showOrderToast(payload.orderId, payload.status, payload.message);
                }
            } catch (_error) {
                // invalid data
            }
        });

        pollOrderStatus();
        setInterval(pollOrderStatus, 3000);
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
