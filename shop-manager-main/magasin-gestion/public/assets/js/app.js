(function () {
    function initNavbar() {
        var nav = document.getElementById('site-navbar');
        var btn = document.getElementById('navbar-toggle');
        if (!nav || !btn) return;

        btn.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
        });

        window.addEventListener('resize', function () {
            if (window.matchMedia('(min-width: 901px)').matches) {
                nav.classList.remove('is-open');
                btn.setAttribute('aria-expanded', 'false');
                btn.setAttribute('aria-label', 'Ouvrir le menu');
            }
        });
    }

    function bindPriceAutofill(row) {
        var sel = row.querySelector('select[name="product_id[]"]');
        var price = row.querySelector('.price-field');
        if (!sel || !price) return;
        sel.addEventListener('change', function () {
            var o = sel.options[sel.selectedIndex];
            var dp = o ? o.getAttribute('data-price') : '';
            if (dp !== null && dp !== '') {
                price.value = dp;
            }
        });
    }

    function formatMadDisplay(n) {
        var s = (Math.round(n * 100) / 100).toFixed(2).replace('.', ',');
        return s.replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' DH';
    }

    function saleLineTotal(rowsEl) {
        var total = 0;
        rowsEl.querySelectorAll('.sale-row').forEach(function (row) {
            var q = row.querySelector('input[name="quantity[]"]');
            var p = row.querySelector('.price-field');
            if (!q || !p) return;
            var qty = parseInt(q.value, 10) || 0;
            var price = parseFloat(String(p.value).replace(',', '.')) || 0;
            total += qty * price;
        });
        return Math.round(total * 100) / 100;
    }

    function syncSalePayDiff(total) {
        var elEsp = document.getElementById('payment_especes');
        var elCarte = document.getElementById('payment_carte');
        var elAutre = document.getElementById('payment_autre');
        var hint = document.getElementById('sale-pay-diff');
        var elTot = document.getElementById('sale-total-display');
        if (elTot) elTot.textContent = formatMadDisplay(total);
        if (!hint || !elEsp || !elCarte || !elAutre) return;
        var pe = parseFloat(String(elEsp.value).replace(',', '.')) || 0;
        var pc = parseFloat(String(elCarte.value).replace(',', '.')) || 0;
        var pa = parseFloat(String(elAutre.value).replace(',', '.')) || 0;
        var paySum = Math.round((pe + pc + pa) * 100) / 100;
        if (total <= 0.001 && paySum <= 0.001) {
            hint.textContent = '';
            return;
        }
        var diff = Math.round((paySum - total) * 100) / 100;
        if (Math.abs(diff) < 0.02) {
            hint.textContent = 'Répartition : OK.';
            hint.classList.remove('text-danger');
        } else if (diff < 0) {
            hint.textContent = 'Il manque ' + formatMadDisplay(Math.abs(diff)) + ' pour égaler le total.';
            hint.classList.add('text-danger');
        } else {
            hint.textContent = 'Excédent de ' + formatMadDisplay(diff) + ' par rapport au total.';
            hint.classList.add('text-danger');
        }
    }

    function bindOneSaleRow(row, rowsEl) {
        function recalc() {
            syncSalePayDiff(saleLineTotal(rowsEl));
        }
        row.addEventListener('input', function (e) {
            if (e.target && (e.target.name === 'quantity[]' || (e.target.classList && e.target.classList.contains('price-field')))) {
                recalc();
            }
        });
        row.addEventListener('change', function (e) {
            if (e.target && e.target.name === 'product_id[]') {
                recalc();
            }
        });
    }

    function initSaleForm() {
        var rows = document.getElementById('rows');
        var tpl = document.getElementById('row-template');
        var addBtn = document.getElementById('add-line');
        if (!rows || !tpl || !addBtn) return;

        var recalcPay = function () {
            syncSalePayDiff(saleLineTotal(rows));
        };

        addBtn.addEventListener('click', function () {
            rows.appendChild(tpl.content.cloneNode(true));
            var last = rows.lastElementChild;
            bindPriceAutofill(last);
            bindOneSaleRow(last, rows);
            recalcPay();
        });

        rows.querySelectorAll('.sale-row').forEach(function (row) {
            bindPriceAutofill(row);
            bindOneSaleRow(row, rows);
        });
        recalcPay();

        ['payment_especes', 'payment_carte', 'payment_autre'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', recalcPay);
        });
    }

    function initUserRoleSupplierField() {
        var role = document.getElementById('role');
        var wrap = document.getElementById('user-supplier-field-wrap');
        if (!role || !wrap) {
            return;
        }
        function sync() {
            wrap.classList.toggle('is-visible', role.value === 'fournisseur');
        }
        role.addEventListener('change', sync);
        sync();
    }

    function initPoForm() {
        var rows = document.getElementById('po-rows');
        var tpl = document.getElementById('po-row-template');
        var addBtn = document.getElementById('po-add-line');
        var sup = document.getElementById('supplier_id');
        if (!rows || !tpl || !addBtn) return;

        function syncRow(row) {
            var sid = sup && sup.value ? sup.value : '';
            var sel = row.querySelector('.po-product-select');
            var hint = row.querySelector('.po-price-hint');
            if (!sel) return;
            sel.querySelectorAll('option').forEach(function (opt) {
                if (opt.value === '') {
                    opt.disabled = false;
                    return;
                }
                var ps = opt.getAttribute('data-supplier-id') || '';
                opt.disabled = sid === '' || ps !== sid;
            });
            var o = sel.options[sel.selectedIndex];
            if (o && o.disabled && o.value) {
                sel.value = '';
                o = sel.options[sel.selectedIndex];
            }
            if (hint) {
                if (o && o.value && !o.disabled) {
                    var pb = o.getAttribute('data-price-buy');
                    if (pb !== null && pb !== '') {
                        hint.textContent = parseFloat(String(pb).replace(',', '.')).toFixed(2).replace('.', ',') + ' DH';
                    } else {
                        hint.textContent = '—';
                    }
                } else {
                    hint.textContent = '—';
                }
            }
        }

        function syncAll() {
            rows.querySelectorAll('.po-row').forEach(syncRow);
        }

        function bindRow(row) {
            var sel = row.querySelector('.po-product-select');
            if (sel) {
                sel.addEventListener('change', function () {
                    syncRow(row);
                });
            }
            syncRow(row);
        }

        if (sup) {
            sup.addEventListener('change', syncAll);
        }

        rows.querySelectorAll('.po-row').forEach(bindRow);

        addBtn.addEventListener('click', function () {
            rows.appendChild(tpl.content.cloneNode(true));
            bindRow(rows.lastElementChild);
        });
    }

    function boot() {
        initNavbar();
        initSaleForm();
        initUserRoleSupplierField();
        initPoForm();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
