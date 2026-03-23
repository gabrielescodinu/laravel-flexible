(function () {
    if (window.__cartRealtimeInitialized) {
        return;
    }

    if (!window.Vue || typeof window.Vue.createApp !== 'function') {
        return;
    }

    window.__cartRealtimeInitialized = true;

    var createApp = window.Vue.createApp;
    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    function jsonHeaders() {
        var headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return headers;
    }

    function requestJson(url, method, payload) {
        if (method === undefined) {
            method = 'GET';
        }

        if (payload === undefined) {
            payload = null;
        }

        var headers = jsonHeaders();
        var options = {
            method: method,
            credentials: 'same-origin',
            headers: headers,
        };

        if (payload !== null) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }

        return fetch(url, options).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (!response.ok) {
                    throw new Error(data && data.message ? data.message : 'Operazione non riuscita.');
                }

                return data;
            });
        });
    }

    function updateCartBadges(cartSnapshot) {
        if (!cartSnapshot || typeof cartSnapshot.cart_count !== 'number') {
            return;
        }

        document.querySelectorAll('[data-cart-count-badge]').forEach(function (badge) {
            badge.textContent = String(cartSnapshot.cart_count);
        });
    }

    function emitCartUpdated(cartSnapshot) {
        updateCartBadges(cartSnapshot);

        window.dispatchEvent(new CustomEvent('cart:updated', {
            detail: cartSnapshot,
        }));
    }

    function parseJsonDataset(value, fallback) {
        if (fallback === undefined) {
            fallback = null;
        }

        if (typeof value !== 'string' || value.trim() === '') {
            return fallback;
        }

        try {
            return JSON.parse(value);
        } catch (_error) {
            return fallback;
        }
    }

    function mountAddToCartWidgets() {
        document.querySelectorAll('[data-vue-cart-add]').forEach(function (root) {
            var endpoint = root.getAttribute('data-endpoint') || '';
            var defaultQuantity = Number(root.getAttribute('data-default-quantity') || 1);
            var productTitle = root.getAttribute('data-product-title') || 'Prodotto';

            if (!endpoint) {
                return;
            }

            createApp({
                data: function () {
                    return {
                        quantity: Number.isFinite(defaultQuantity) && defaultQuantity > 0 ? defaultQuantity : 1,
                        loading: false,
                        message: '',
                        error: false,
                    };
                },
                methods: {
                    submit: function () {
                        var _this = this;

                        if (this.loading) {
                            return;
                        }

                        var quantity = Number.isFinite(this.quantity) ? Math.max(1, Math.min(99, Number(this.quantity))) : 1;
                        this.quantity = quantity;
                        this.loading = true;
                        this.message = '';
                        this.error = false;

                        requestJson(endpoint, 'POST', {
                            quantity: quantity,
                        }).then(function (response) {
                            _this.message = response && response.message ? response.message : (productTitle + ' aggiunto al carrello.');
                            _this.error = false;

                            if (response && response.cart) {
                                emitCartUpdated(response.cart);
                            }
                        }).catch(function (error) {
                            _this.message = error instanceof Error ? error.message : 'Errore durante aggiunta al carrello.';
                            _this.error = true;
                        }).finally(function () {
                            _this.loading = false;
                        });
                    },
                },
            }).mount(root);
        });
    }

    function mountCartPage() {
        var root = document.querySelector('[data-vue-cart-page]');
        if (!root) {
            return;
        }

        var updateUrlTemplate = root.getAttribute('data-update-url-template') || '';
        var removeUrlTemplate = root.getAttribute('data-remove-url-template') || '';
        var clearUrl = root.getAttribute('data-clear-url') || '';
        var initialCartState = parseJsonDataset(root.getAttribute('data-cart-state'), {
            items: [],
            total: 0,
            total_formatted: '€ 0,00',
            cart_count: 0,
        });

        if (!updateUrlTemplate || !removeUrlTemplate || !clearUrl) {
            return;
        }

        function resolveEndpoint(template, productId) {
            return template.replace('__PRODUCT__', String(productId));
        }

        createApp({
            data: function () {
                return {
                    cart: initialCartState,
                    loading: false,
                    feedbackMessage: '',
                    feedbackError: false,
                };
            },
            methods: {
                applyResponse: function (response, defaultMessage) {
                    if (defaultMessage === undefined) {
                        defaultMessage = '';
                    }

                    if (response && response.cart) {
                        this.cart = response.cart;
                        emitCartUpdated(response.cart);
                    }

                    this.feedbackMessage = response && response.message ? response.message : defaultMessage;
                    this.feedbackError = false;
                },
                handleError: function (error) {
                    this.feedbackMessage = error instanceof Error ? error.message : 'Operazione non riuscita.';
                    this.feedbackError = true;
                },
                updateItem: function (item) {
                    var _this = this;

                    if (this.loading) {
                        return;
                    }

                    var quantity = Number.isFinite(Number(item.quantity))
                        ? Math.max(1, Math.min(99, Number(item.quantity)))
                        : 1;

                    item.quantity = quantity;
                    this.loading = true;
                    this.feedbackMessage = '';

                    requestJson(resolveEndpoint(updateUrlTemplate, item.product_id), 'PATCH', {
                        quantity: quantity,
                    }).then(function (response) {
                        _this.applyResponse(response, 'Quantita aggiornata.');
                    }).catch(function (error) {
                        _this.handleError(error);
                    }).finally(function () {
                        _this.loading = false;
                    });
                },
                removeItem: function (item) {
                    var _this = this;

                    if (this.loading) {
                        return;
                    }

                    this.loading = true;
                    this.feedbackMessage = '';

                    requestJson(resolveEndpoint(removeUrlTemplate, item.product_id), 'DELETE').then(function (response) {
                        _this.applyResponse(response, 'Prodotto rimosso dal carrello.');
                    }).catch(function (error) {
                        _this.handleError(error);
                    }).finally(function () {
                        _this.loading = false;
                    });
                },
                clearCart: function () {
                    var _this = this;

                    if (this.loading) {
                        return;
                    }

                    this.loading = true;
                    this.feedbackMessage = '';

                    requestJson(clearUrl, 'DELETE').then(function (response) {
                        _this.applyResponse(response, 'Carrello svuotato.');
                    }).catch(function (error) {
                        _this.handleError(error);
                    }).finally(function () {
                        _this.loading = false;
                    });
                },
            },
            mounted: function () {
                var _this = this;

                emitCartUpdated(this.cart);

                window.addEventListener('cart:updated', function (event) {
                    var snapshot = event && event.detail;
                    if (!snapshot || !Array.isArray(snapshot.items)) {
                        return;
                    }

                    _this.cart = snapshot;
                });
            },
        }).mount(root);
    }

    mountAddToCartWidgets();
    mountCartPage();
})();
