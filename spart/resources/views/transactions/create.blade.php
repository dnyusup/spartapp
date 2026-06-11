<x-layouts.app>
    <x-slot:title>New Transaction</x-slot:title>
    <x-slot:header>New Transaction</x-slot:header>

    <style>
        /* Compact part list row */
        .part-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 0.875rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            gap: 0.75rem;
        }
        /* Qty stepper inside modal */
        .qty-stepper {
            display: flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            overflow: hidden;
        }
        .qty-stepper input {
            border: none;
            text-align: center;
            flex: 1;
            padding: 0.5rem;
            font-size: 0.875rem;
            min-width: 0;
        }
        .qty-stepper input:focus { outline: none; }
        .qty-stepper button {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            border: none;
            cursor: pointer;
            font-size: 1.25rem;
            color: #6b7280;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .qty-stepper button:hover { background: #e5e7eb; }
        /* Barcode dropdown inside modal */
        .modal-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 0.375rem 0.375rem;
            max-height: 200px;
            overflow-y: auto;
            z-index: 60;
            display: none;
        }
        .modal-dropdown.show { display: block; }
        .modal-option {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            font-size: 0.8125rem;
        }
        .modal-option:hover { background-color: #f3f4f6; }
        .modal-option.selected { background-color: #e0e7ff; }
    </style>

    @php
        $sparepartsJson = $spareparts->map(fn($sp) => [
            'id'          => $sp->id,
            'code'        => $sp->material_code,
            'description' => $sp->description,
            'stock'       => (float) $sp->stock,
            'unit'        => $sp->unit,
        ])->values();
    @endphp
    <script>
        const sparepartsData = @json($sparepartsJson);
    </script>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('transactions.store') }}" method="POST" class="px-4 py-5 sm:p-6" id="transaction-form">
                @csrf

                @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-sm font-medium text-red-700 mb-1">Terdapat kesalahan pada form:</p>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 gap-6">

                    <!-- 1. Order Number -->
                    <div>
                        <label for="reference_no" class="block text-sm font-medium text-gray-700 mb-1">
                            Order Number <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="reference_no" id="reference_no" value="{{ old('reference_no') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2 border @error('reference_no') border-red-500 @enderror"
                               placeholder="Masukkan nomor order (8-10 digit angka)" required min="10000000" max="9999999999">
                        <p class="text-xs text-gray-500 mt-1">Order number harus angka 8-10 digit.</p>
                        @error('reference_no')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 2. Transaction Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Transaction Type <span class="text-red-500">*</span>
                        </label>
                        @if(auth()->user()->role === 'admin')
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center justify-center gap-2 py-2 px-3 border rounded-lg cursor-pointer transition hover:border-green-400
                                          {{ old('type') == 'in' ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                                <input type="radio" name="type" value="in" class="sr-only" {{ old('type') == 'in' ? 'checked' : '' }} required>
                                <i class="fas fa-arrow-down text-green-500"></i>
                                <span class="text-sm font-medium">In</span>
                            </label>
                            <label class="flex items-center justify-center gap-2 py-2 px-3 border rounded-lg cursor-pointer transition hover:border-red-400
                                          {{ old('type', 'out') == 'out' ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                                <input type="radio" name="type" value="out" class="sr-only" {{ old('type', 'out') == 'out' ? 'checked' : '' }}>
                                <i class="fas fa-arrow-up text-red-500"></i>
                                <span class="text-sm font-medium">Out</span>
                            </label>
                            <label class="flex items-center justify-center gap-2 py-2 px-3 border rounded-lg cursor-pointer transition hover:border-yellow-400
                                          {{ old('type') == 'adjustment' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-300' }}">
                                <input type="radio" name="type" value="adjustment" class="sr-only" {{ old('type') == 'adjustment' ? 'checked' : '' }}>
                                <i class="fas fa-edit text-yellow-500"></i>
                                <span class="text-sm font-medium">Adjust</span>
                            </label>
                        </div>
                        @else
                        <div class="grid grid-cols-1">
                            <label class="flex items-center justify-center gap-2 py-2 px-3 border rounded-lg bg-red-50 border-red-500">
                                <input type="radio" name="type" value="out" class="sr-only" checked>
                                <i class="fas fa-arrow-up text-red-500"></i>
                                <span class="text-sm font-medium">Out</span>
                            </label>
                        </div>
                        @endif
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 3. Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        @if(auth()->user()->role === 'admin')
                            @php
                                $currentUserId = auth()->id();
                                $currentUser   = $users->firstWhere('id', $currentUserId);
                                $otherUsers    = $users->filter(fn($u) => $u->id !== $currentUserId);
                            @endphp
                            <select name="user_id" id="user_id_select"
                                    class="tom-select-user block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2 border @error('user_id') border-red-500 @enderror"
                                    required>
                                <option value="">Pilih user...</option>
                                @if($currentUser)
                                    <option value="{{ $currentUser->id }}" {{ old('user_id') == $currentUser->id ? 'selected' : '' }}>{{ $currentUser->name }}</option>
                                @endif
                                @foreach($otherUsers as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Pilih nama user yang melakukan transaksi</p>
                        @else
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            <input type="text" readonly
                                   class="block w-full rounded-md border-gray-200 bg-gray-50 shadow-sm sm:text-sm px-3 py-2 border text-gray-600"
                                   value="{{ auth()->user()->name }}">
                            <p class="mt-1 text-xs text-gray-500">Nama user yang melakukan transaksi</p>
                        @endif
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 4. Parts compact list -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-semibold text-gray-700">
                                Parts <span class="text-red-500">*</span>
                            </label>
                            <span id="parts-count" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">0 parts</span>
                        </div>
                        <div id="parts-empty-msg" class="text-sm text-gray-400 text-center py-4 border border-dashed border-gray-200 rounded-lg mb-3">
                            <i class="fas fa-box-open text-gray-300 text-2xl mb-1 block"></i>
                            Belum ada part. Klik <strong>Add Part</strong> untuk menambahkan.
                        </div>
                        <div id="parts-container" class="space-y-2 mb-3"></div>
                        <button type="button" id="add-part-btn"
                                class="w-full py-2 px-4 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-primary-400 hover:text-primary-600 transition flex items-center justify-center gap-2">
                            <i class="fas fa-plus"></i> Add Part
                        </button>
                    </div>

                    <!-- 5. Remark -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">REMARK</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2 border"
                                  placeholder="Additional notes...">{{ old('notes') }}</textarea>
                    </div>

                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <button type="submit"
                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-check mr-2"></i>Save Transaction
                    </button>
                    <a href="{{ route('transactions.index') }}"
                       class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Part Modal -->
    <div id="add-part-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="flex justify-between items-center px-5 py-4 border-b">
                <h3 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-plus-circle text-primary-500 mr-2"></i>Add Part
                </h3>
                <button type="button" id="close-part-modal" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <!-- Barcode Search -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Product Barcode <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" id="modal-barcode-search"
                                   class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm px-3 py-2 border focus:border-primary-500 focus:ring-primary-500"
                                   placeholder="Scan or search barcode..." autocomplete="off">
                            <input type="hidden" id="modal-sparepart-id">
                            <div id="modal-dropdown" class="modal-dropdown"></div>
                        </div>
                        <button type="button" id="modal-scan-btn"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md bg-gray-50 hover:bg-gray-100 text-gray-700 transition">
                            <i class="fas fa-camera text-lg"></i>
                        </button>
                    </div>
                </div>
                <!-- Description -->
                <div>
                    <label class="block text-xs font-medium text-amber-600 mb-1">Material Description</label>
                    <input type="text" id="modal-description" readonly
                           class="block w-full rounded-md border-gray-200 bg-gray-50 shadow-sm sm:text-sm px-3 py-2 border text-gray-600"
                           placeholder="Auto-fill dari Product Barcode">
                </div>
                <!-- Stock & Satuan -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-blue-600 mb-1">Current Qty</label>
                        <input type="text" id="modal-stock" readonly
                               class="block w-full rounded-md border-gray-200 bg-blue-50 shadow-sm sm:text-sm px-3 py-2 border text-blue-700 font-semibold"
                               placeholder="--">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                        <input type="text" id="modal-satuan" readonly
                               class="block w-full rounded-md border-gray-200 bg-gray-50 shadow-sm sm:text-sm px-3 py-2 border text-gray-600"
                               placeholder="PC">
                    </div>
                </div>
                <!-- Quantity -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <div class="qty-stepper">
                        <button type="button" onclick="adjustModalQty(-1)">&#8722;</button>
                        <input type="number" id="modal-quantity" placeholder="Masukkan jumlah" min="0.01" step="0.1">
                        <button type="button" onclick="adjustModalQty(1)">+</button>
                    </div>
                </div>
            </div>
            <div class="px-5 pb-5 flex gap-3">
                <button type="button" id="confirm-add-part"
                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                    <i class="fas fa-check mr-2"></i>Add to List
                </button>
                <button type="button" id="cancel-part-modal"
                        class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Camera Scanner Modal -->
    <div id="scanner_modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center px-4 py-3 border-b">
                <h3 class="text-lg font-medium">Scan Barcode / QR Code</h3>
                <button type="button" id="close_scanner" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4">
                <div id="scanner_reader" style="width: 100%;"></div>
                <p class="text-sm text-gray-500 mt-3 text-center">
                    <i class="fas fa-info-circle mr-1"></i> Arahkan kamera ke barcode atau QR code
                </p>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'admin')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.TomSelect) {
                new TomSelect('#user_id_select', { create: false, sortField: 'text', placeholder: 'Cari nama user...' });
            }
        });
    </script>
    @endif

    <script>
        let partCounter       = 0;
        let html5QrCode       = null;
        let modalPartData     = null;
        let addedSparepartIds = new Set();

        function escHtml(str) {
            return String(str)
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function openPartModal() {
            document.getElementById('modal-barcode-search').value = '';
            document.getElementById('modal-sparepart-id').value   = '';
            document.getElementById('modal-description').value    = '';
            document.getElementById('modal-stock').value          = '';
            document.getElementById('modal-satuan').value         = '';
            document.getElementById('modal-quantity').value       = '';
            document.getElementById('modal-dropdown').classList.remove('show');
            modalPartData = null;
            document.getElementById('add-part-modal').classList.remove('hidden');
            setTimeout(() => document.getElementById('modal-barcode-search').focus(), 100);
        }

        function closePartModal() {
            document.getElementById('add-part-modal').classList.add('hidden');
        }

        function renderModalDropdown(query) {
            const dropdown = document.getElementById('modal-dropdown');
            const q = query.toLowerCase();
            const filtered = (q
                ? sparepartsData.filter(sp => sp.code.toLowerCase().includes(q) || sp.description.toLowerCase().includes(q))
                : sparepartsData).filter(sp => !addedSparepartIds.has(String(sp.id)));
            if (filtered.length === 0) {
                dropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Tidak ada hasil</div>';
            } else {
                dropdown.innerHTML = filtered.slice(0, 60).map(sp =>
                    `<div class="modal-option"
                          data-id="${sp.id}"
                          data-code="${escHtml(sp.code)}"
                          data-desc="${escHtml(sp.description)}"
                          data-stock="${sp.stock}"
                          data-unit="${escHtml(sp.unit)}">
                        <strong>${escHtml(sp.code)}</strong> &ndash; ${escHtml(sp.description.substring(0, 45))}
                        <span class="text-xs text-gray-400 ml-1">(${Number(sp.stock).toLocaleString('id-ID')} ${escHtml(sp.unit)})</span>
                    </div>`
                ).join('');
                dropdown.querySelectorAll('.modal-option').forEach(o =>
                    o.addEventListener('click', () => selectModalPart(o))
                );
            }
            dropdown.classList.add('show');
        }

        function selectModalPart(opt) {
            modalPartData = {
                id:          opt.dataset.id,
                code:        opt.dataset.code,
                description: opt.dataset.desc,
                stock:       opt.dataset.stock,
                unit:        opt.dataset.unit,
            };
            document.getElementById('modal-sparepart-id').value   = opt.dataset.id;
            document.getElementById('modal-barcode-search').value  = opt.dataset.code;
            document.getElementById('modal-description').value     = opt.dataset.desc;
            document.getElementById('modal-stock').value           = Number(opt.dataset.stock).toLocaleString('id-ID') + ' ' + opt.dataset.unit;
            document.getElementById('modal-satuan').value          = opt.dataset.unit;
            document.getElementById('modal-dropdown').classList.remove('show');
            document.getElementById('modal-quantity').focus();
        }

        function confirmAddPart() {
            const sparepartId = document.getElementById('modal-sparepart-id').value;
            const qty         = parseFloat(document.getElementById('modal-quantity').value);
            if (!sparepartId || !modalPartData) {
                alert('Pilih part terlebih dahulu.');
                document.getElementById('modal-barcode-search').focus();
                return;
            }
            if (addedSparepartIds.has(String(sparepartId))) {
                alert('Part ini sudah ada di daftar. Hapus dulu jika ingin mengubahnya.');
                return;
            }
            if (!qty || qty <= 0) {
                alert('Masukkan jumlah yang valid (> 0).');
                document.getElementById('modal-quantity').focus();
                return;
            }
            addCompactRow(sparepartId, qty, modalPartData);
            closePartModal();
        }

        function addCompactRow(sparepartId, qty, data) {
            const idx = partCounter++;
            addedSparepartIds.add(String(sparepartId));
            document.getElementById('parts-empty-msg').classList.add('hidden');
            const container = document.getElementById('parts-container');
            const row = document.createElement('div');
            row.className = 'part-row';
            row.dataset.idx = idx;
            const qtyDisplay = qty % 1 === 0 ? qty : qty.toFixed(1);
            row.innerHTML = `
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="part-label text-xs font-bold text-gray-400 shrink-0"></span>
                        <span class="text-sm font-semibold text-gray-800">${escHtml(data.code)}</span>
                    </div>
                    <p class="text-xs text-gray-500 truncate mt-0.5">${escHtml(data.description)}</p>
                </div>
                <div class="flex items-center gap-2 ml-2 shrink-0">
                    <span class="text-sm font-bold text-gray-700 whitespace-nowrap">
                        ${qtyDisplay} <span class="text-xs font-normal text-gray-500">${escHtml(data.unit)}</span>
                    </span>
                    <input type="hidden" name="parts[${idx}][sparepart_id]" value="${escHtml(sparepartId)}">
                    <input type="hidden" name="parts[${idx}][quantity]" value="${qty}">
                    <button type="button" class="remove-part-btn text-gray-300 hover:text-red-500 transition ml-1">
                        <i class="fas fa-times-circle text-base"></i>
                    </button>
                </div>`;
            row.querySelector('.remove-part-btn').addEventListener('click', () => {
                addedSparepartIds.delete(String(sparepartId));
                row.remove();
                refreshUI();
            });
            container.appendChild(row);
            refreshUI();
        }

        function refreshUI() {
            const rows = document.querySelectorAll('.part-row');
            rows.forEach((r, i) => {
                r.querySelector('.part-label').textContent = '#' + (i + 1);
            });
            const c = rows.length;
            document.getElementById('parts-count').textContent = c + (c === 1 ? ' part' : ' parts');
            document.getElementById('parts-empty-msg').classList.toggle('hidden', c > 0);
        }

        function adjustModalQty(delta) {
            const input = document.getElementById('modal-quantity');
            let val = parseFloat(input.value) || 0;
            val = Math.max(0, val + delta);
            input.value = val % 1 === 0 ? String(val) : val.toFixed(1);
        }

        function startScanner() {
            html5QrCode = new Html5Qrcode('scanner_reader');
            html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                decodedText => {
                    const match = sparepartsData.find(sp => sp.code === decodedText)
                               || sparepartsData.find(sp => sp.code.includes(decodedText) || decodedText.includes(sp.code));
                    stopScanner();
                    document.getElementById('scanner_modal').classList.add('hidden');
                    if (match) {
                        if (addedSparepartIds.has(String(match.id))) {
                            alert('Part "' + match.code + '" sudah ada di daftar.');
                        } else {
                            const fakeOpt = {
                                dataset: { id: String(match.id), code: match.code, desc: match.description, stock: String(match.stock), unit: match.unit }
                            };
                            selectModalPart(fakeOpt);
                        }
                    } else {
                        alert('Barcode tidak ditemukan: ' + decodedText);
                    }
                },
                () => {}
            ).catch(() => alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.'));
        }

        function stopScanner() {
            if (html5QrCode) { html5QrCode.stop().catch(() => {}); html5QrCode = null; }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('add-part-btn').addEventListener('click', openPartModal);
            document.getElementById('close-part-modal').addEventListener('click', closePartModal);
            document.getElementById('cancel-part-modal').addEventListener('click', closePartModal);
            document.getElementById('add-part-modal').addEventListener('click', e => {
                if (e.target === document.getElementById('add-part-modal')) closePartModal();
            });
            document.getElementById('confirm-add-part').addEventListener('click', confirmAddPart);

            const barcodeSearch = document.getElementById('modal-barcode-search');
            barcodeSearch.addEventListener('focus', () => renderModalDropdown(barcodeSearch.value));
            barcodeSearch.addEventListener('input', () => renderModalDropdown(barcodeSearch.value));

            document.addEventListener('click', e => {
                if (!e.target.closest('#modal-barcode-search') && !e.target.closest('#modal-dropdown')) {
                    document.getElementById('modal-dropdown').classList.remove('show');
                }
            });

            document.getElementById('modal-scan-btn').addEventListener('click', () => {
                document.getElementById('scanner_modal').classList.remove('hidden');
                startScanner();
            });
            document.getElementById('close_scanner').addEventListener('click', () => {
                stopScanner();
                document.getElementById('scanner_modal').classList.add('hidden');
            });
            document.getElementById('scanner_modal').addEventListener('click', e => {
                if (e.target === document.getElementById('scanner_modal')) {
                    stopScanner();
                    document.getElementById('scanner_modal').classList.add('hidden');
                }
            });

            document.getElementById('modal-quantity').addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); confirmAddPart(); }
            });

            // Prevent double submit
            document.getElementById('transaction-form').addEventListener('submit', function (e) {
                const btn = this.querySelector('button[type="submit"]');
                if (btn.dataset.submitting === 'true') {
                    e.preventDefault();
                    return;
                }
                btn.dataset.submitting = 'true';
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            });
        });

        document.querySelectorAll('input[name="type"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('input[name="type"]').forEach(r => {
                    r.closest('label').classList.remove(
                        'border-green-500','bg-green-50',
                        'border-red-500','bg-red-50',
                        'border-yellow-500','bg-yellow-50',
                        'border-gray-300'
                    );
                    r.closest('label').classList.add('border-gray-300');
                });
                const lbl = this.closest('label');
                lbl.classList.remove('border-gray-300');
                if (this.value === 'in')           lbl.classList.add('border-green-500','bg-green-50');
                else if (this.value === 'out')     lbl.classList.add('border-red-500','bg-red-50');
                else                               lbl.classList.add('border-yellow-500','bg-yellow-50');
            });
        });
    </script>
</x-layouts.app>
