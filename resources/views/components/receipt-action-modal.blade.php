{{-- Modal unificado para acciones del recibo (imprimir / compartir / cancelar) --}}
<div id="receiptActionModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0"
         id="receiptActionModalContent">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Recibo
                </h3>
            </div>
            <button type="button" onclick="window.__closeReceiptActionModal?.('cancel')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                Elegí qué hacer con el recibo
            </p>
        </div>

        <!-- Footer -->
        <div class="flex flex-wrap justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button"
                    id="receiptActionModalCancelBtn"
                    onclick="window.__closeReceiptActionModal?.('cancel')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Continuar
            </button>
            <button type="button"
                    id="receiptActionModalWhatsAppBtn"
                    onclick="window.__closeReceiptActionModal?.('whatsapp')"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors bg-green-600 hover:bg-green-700 disabled:bg-green-400 disabled:cursor-not-allowed">
                Compartir por WhatsApp
            </button>
            <button type="button"
                    id="receiptActionModalPrintBtn"
                    onclick="window.__closeReceiptActionModal?.('print')"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors bg-emerald-600 hover:bg-emerald-700">
                Imprimir
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('receiptActionModal');
    const content = document.getElementById('receiptActionModalContent');
    const btnWhatsApp = document.getElementById('receiptActionModalWhatsAppBtn');

    function show() {
        if (!modal || !content) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function hide() {
        if (!modal || !content) return;
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    async function fetchWhatsAppConnected() {
        try {
            const r = await fetch("{{ route('whatsapp.status') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await r.json();
            return !!(data && data.connected);
        } catch (e) {
            return false;
        }
    }

    window.askReceiptAction = async function (opts) {
        const options = opts || {};
        const kind = options.kind || 'patient_payment';
        const hasPatientPhone = !!options.hasPatientPhone;
        const whatsappEnabled = "{{ setting('whatsapp.enabled', '0') }}" === '1';

        if (btnWhatsApp) {
            const connected = whatsappEnabled ? await fetchWhatsAppConnected() : false;
            const canShow = whatsappEnabled && connected && kind === 'patient_payment' && hasPatientPhone;
            btnWhatsApp.style.display = canShow ? 'inline-flex' : 'none';
            btnWhatsApp.disabled = !canShow;
        }

        show();

        return await new Promise((resolve) => {
            window.__closeReceiptActionModal = function (action) {
                hide();
                resolve(action || 'cancel');
            };
        });
    };

    async function postJson(url, body) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const r = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(body || {})
        });

        let data = null;
        try { data = await r.json(); } catch (e) { data = null; }
        return { ok: r.ok, data };
    }

    window.sharePaymentReceiptByWhatsApp = async function (paymentId) {
        const { ok, data } = await postJson(`/payments/${paymentId}/share-whatsapp`, {});
        const success = !!(data && data.success);
        const message = (data && data.message) ? data.message : (success ? 'Recibo enviado por WhatsApp.' : 'No se pudo enviar el recibo por WhatsApp.');
        window.showToast(message, (ok && success) ? 'success' : 'error');
        return { ok, success, data };
    };

    window.shareIncomeReceiptByWhatsApp = async function (paymentId) {
        const { ok, data } = await postJson(`/cash/income-receipt/${paymentId}/share-whatsapp`, {});
        const success = !!(data && data.success);
        const message = (data && data.message) ? data.message : (success ? 'Recibo enviado por WhatsApp.' : 'No se pudo enviar el recibo por WhatsApp.');
        window.showToast(message, (ok && success) ? 'success' : 'error');
        return { ok, success, data };
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            window.__closeReceiptActionModal?.('cancel');
        }
    });

    modal?.addEventListener('click', function (e) {
        if (e.target === modal) {
            window.__closeReceiptActionModal?.('cancel');
        }
    });
})();
</script>
