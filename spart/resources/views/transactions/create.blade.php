<x-layouts.app>
    <x-slot:title>New Transaction</x-slot:title>
    <x-slot:header>New Stock Transaction</x-slot:header>

    <style>
        /* Custom select dropdown styles */
        .custom-select-wrapper {
            position: relative;
        }
        .custom-select-search {
            position: relative;
        }
        .custom-select-dropdown {
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
            z-index: 50;
            display: none;
        }
        .custom-select-dropdown.show {
            display: block;
        }
        .custom-select-option {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .custom-select-option:hover {
            background-color: #f3f4f6;
        }
        .custom-select-option.selected {
            background-color: #e0e7ff;
        }
        /* Quantity stepper buttons */
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
        }
        .qty-stepper input:focus {
            outline: none;
        }
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
        }
        .qty-stepper button:hover {
            background: #e5e7eb;
        }
        /* Part row styles */
        .part-row {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            background: #f9fafb;
            position: relative;
        }
        .part-dropdown {
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
            z-index: 50;
            display: none;
        }
        .part-dropdown.show {
            display: block;
        }
        .part-option {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            font-size: 0.8125rem;
        }
        .part-option:hover { background-color: #f3f4f6; }
        .part-option.selected { background-color: #e0e7ff; }
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
                        <label for="reference_no" class="block text-sm font-medium text-gray-700 mb-1">Order Number <span class="text-red-500">*</span></label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type <span class="text-red-500">*</span></label>
                        @if(auth()->user()->role === 'admin')
                        <div class="grid grid-cols-3 gap-3">
                            <label class="flex items-center justify-center p-4 border rounded-lg cursor-pointer transition hover:border-green-400
                                          {{ old('type') == 'in' ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                                <input type="radio" name="type" value="in" class="sr-only" {{ old('type') == 'in' ? 'checked' : '' }} required>
                                <div class="text-center">
                                    <i class="fas fa-arrow-down text-green-500 text-xl mb-1"></i>
                                    <p class="text-sm font-medium">In</p>
                                </div>
                            </label>
                            <label class="flex items-center justify-center p-4 border rounded-lg cursor-pointer transition hover:border-red-400
                                          {{ old('type', 'out') == 'out' ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                                <input type="radio" name="type" value="out" class="sr-only" {{ old('type', 'out') == 'out' ? 'checked' : '' }}>
                                <div class="text-center">
                                    <i class="fas fa-arrow-up text-red-500 text-xl mb-1"></i>
                                    <p class="text-sm font-medium">Out</p>
                                </div>
                            </label>
                            <label class="flex items-center justify-center p-4 border rounded-lg cursor-pointer transition hover:border-yellow-400
                                          {{ old('type') == 'adjustment' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-300' }}">
                                <input type="radio" name="type" value="adjustment" class="sr-only" {{ old('type') == 'adjustment' ? 'checked' : '' }}>
                                <div class="text-center">
                                    <i class="fas fa-edit text-yellow-500 text-xl mb-1"></i>
                                    <p class="text-sm font-medium">Adjust</p>
                                </div>
                            </label>
                        </div>
                        @else
                        <div class="grid grid-cols-1">
                            <label class="flex items-center justify-center p-4 border rounded-lg bg-red-50 border-red-500">
                                <input type="radio" name="type" value="out" class="sr-only" checked>
                                <div class="text-center">
                                    <i class="fas fa-arrow-up text-red-500 text-xl mb-1"></i>
                                    <p class="text-sm font-medium">Out</p>
                                </div>
                            </label>
                        </div>
                        @endif
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 3. Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
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

                    <!-- 4. Parts (dynamic, multiple) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700">Parts <span class="text-red-500">*</span></label>
                            <span id="parts-count" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">1 part</span>
                        </div>
                        <div id="parts-container" class="space-y-3">
                            {{-- rows injected by JS --}}
                        </div>
                        <button type="button" id="add-part-btn"
                                class="mt-3 w-full py-2 px-4 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-primary-400 hover:text-primary-600 transition flex items-center justify-center gap-2">
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

    <!-- Camera Scanner Modal (shared) -->
    <div id="scanner_modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
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
        // ===== Part row counter =====
        let partCounter = 0;
        let activeScanRow = null;
        let html5QrCode   = null;

        // ===== HTML escape helper =====
        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ===== Build dropdown options HTML =====
        function buildOptions() {
            return sparepartsData.map(sp =>
                `<div class="part-option px-3 py-2 cursor-pointer text-sm hover:bg-gray-100"
                      data-id="${sp.id}"
                      data-code="${escHtml(sp.code)}"
                      data-desc="${escHtml(sp.description)}"
                      data-stock="${sp.stock}"
                      data-unit="${escHtml(sp.unit)}">
                    <strong>${escHtml(sp.code)}</strong> &ndash; ${escHtml(sp.description.substring(0, 40))}
                    <span class="text-xs text-gray-400">(${sp.stock.toLocaleString('id-ID')} ${escHtml(sp.unit)})</span>
                </div>`
            ).join('');
        }

        // ===== Create a new part row =====
        function addPartRow() {
            const idx = partCounter++;
            const container = document.getElementById('parts-container');
            const row = document.createElement('div');
            row.className = 'part-row';
            row.dataset.idx = idx;
            row.innerHTML = `
                <div class="flex justify-between items-center mb-3">
                    <span class="part-label text-sm font-semibold text-gray-700">Part #1</span>
                    <button type="button" class="remove-part-btn hidden text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
                <div class="grid grid-cols-1 gap-3">
                    <!-- Barcode -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Product Barcode <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="part-barcode-wrapper relative flex-1">
                                <input type="text" class="part-barcode-search block w-full rounded-md border-gray-300 shadow-sm sm:text-sm px-3 py-2 border"
                                       placeholder="Scan or search barcode..." autocomplete="off">
                                <input type="hidden" name="parts[${idx}][sparepart_id]" class="part-sparepart-id" required>
                                <div class="part-dropdown">
                                    ${buildOptions()}
                                </div>
                            </div>
                            <button type="button" class="part-scan-btn inline-flex items-center px-3 py-2 border border-gray-300 rounded-md bg-gray-50 hover:bg-gray-100 text-gray-700 transition">
                                <i class="fas fa-camera text-lg"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-medium text-amber-600 mb-1">Material Description</label>
                        <input type="text" class="part-description block w-full rounded-md border-gray-200 bg-gray-50 shadow-sm sm:text-sm px-3 py-2 border text-gray-600"
                               readonly placeholder="Auto-fill dari Product Barcode">
                    </div>
                    <!-- Stock & Satuan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-blue-600 mb-1">Existing Stock</label>
                            <input type="text" class="part-stock block w-full rounded-md border-gray-200 bg-blue-50 shadow-sm sm:text-sm px-3 py-2 border text-blue-700 font-semibold"
                                   readonly placeholder="—">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                            <input type="text" class="part-satuan block w-full rounded-md border-gray-200 bg-gray-50 shadow-sm sm:text-sm px-3 py-2 border text-gray-600"
                                   readonly placeholder="PC">
                        </div>
                    </div>
                    <!-- Quantity -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Quantity <span class="text-red-500">*</span></label>
                        <div class="qty-stepper">
                            <button type="button" onclick="adjustPartQty(this,-1)">−</button>
                            <input type="number" name="parts[${idx}][quantity]" class="part-quantity"
                                   placeholder="Masukkan jumlah" min="0" step="0.1" required>
                            <button type="button" onclick="adjustPartQty(this,1)">+</button>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">For adjustment, enter the new stock quantity</p>
                    </div>
                </div>`;

            container.appendChild(row);
            initPartRow(row);
            refreshUI();
        }

        // ===== Wire up events for a row =====
        function initPartRow(row) {
            const searchInput    = row.querySelector('.part-barcode-search');
            const dropdown       = row.querySelector('.part-dropdown');
            const opts           = row.querySelectorAll('.part-option');
            const removeBtn      = row.querySelector('.remove-part-btn');
            const scanBtn        = row.querySelector('.part-scan-btn');

            // Search / focus
            searchInput.addEventListener('focus', () => dropdown.classList.add('show'));
            searchInput.addEventListener('input', () => {
                const val = searchInput.value.toLowerCase();
                opts.forEach(o => {
                    o.style.display = (o.dataset.code.toLowerCase().includes(val) || o.dataset.desc.toLowerCase().includes(val)) ? '' : 'none';
                });
                dropdown.classList.add('show');
            });

            // Select option
            opts.forEach(o => o.addEventListener('click', () => selectPartSparepart(row, o)));

            // Remove row
            removeBtn.addEventListener('click', () => { row.remove(); refreshUI(); });

            // Scan
            scanBtn.addEventListener('click', () => {
                activeScanRow = row;
                document.getElementById('scanner_modal').classList.remove('hidden');
                startScanner();
            });
        }

        // ===== Fill row from selected option =====
        function selectPartSparepart(row, opt) {
            row.querySelector('.part-sparepart-id').value    = opt.dataset.id;
            row.querySelector('.part-barcode-search').value  = opt.dataset.code;
            row.querySelector('.part-description').value     = opt.dataset.desc;
            row.querySelector('.part-stock').value           = Number(opt.dataset.stock).toLocaleString('id-ID') + ' ' + opt.dataset.unit;
            row.querySelector('.part-satuan').value          = opt.dataset.unit;
            row.querySelector('.part-dropdown').classList.remove('show');
            row.querySelectorAll('.part-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
        }

        // ===== Refresh part labels & remove-button visibility =====
        function refreshUI() {
            const rows = document.querySelectorAll('.part-row');
            rows.forEach((r, i) => {
                r.querySelector('.part-label').textContent = `Part #${i + 1}`;
                r.querySelector('.remove-part-btn').classList.toggle('hidden', rows.length === 1);
            });
            const c = rows.length;
            document.getElementById('parts-count').textContent = c + (c === 1 ? ' part' : ' parts');
        }

        // ===== Quantity stepper =====
        function adjustPartQty(btn, delta) {
            const input = btn.closest('.qty-stepper').querySelector('input');
            let val = parseFloat(input.value) || 0;
            val = Math.max(0, val + delta);
            input.value = val % 1 === 0 ? String(val) : val.toFixed(1);
        }

        // ===== Close dropdown on outside click =====
        document.addEventListener('click', e => {
            if (!e.target.closest('.part-barcode-wrapper')) {
                document.querySelectorAll('.part-dropdown').forEach(d => d.classList.remove('show'));
            }
        });

        // ===== Add Part button =====
        document.getElementById('add-part-btn').addEventListener('click', addPartRow);

        // ===== Scanner =====
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

        function startScanner() {
            html5QrCode = new Html5Qrcode('scanner_reader');
            html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                decodedText => {
                    if (!activeScanRow) return;
                    const match = sparepartsData.find(sp => sp.code === decodedText)
                               || sparepartsData.find(sp => sp.code.includes(decodedText) || decodedText.includes(sp.code));
                    if (match) {
                        const opt = activeScanRow.querySelector(`.part-option[data-id="${match.id}"]`);
                        if (opt) selectPartSparepart(activeScanRow, opt);
                    } else {
                        alert('Barcode tidak ditemukan: ' + decodedText);
                    }
                    stopScanner();
                    document.getElementById('scanner_modal').classList.add('hidden');
                    activeScanRow = null;
                },
                () => {}
            ).catch(() => alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.'));
        }

        function stopScanner() {
            if (html5QrCode) { html5QrCode.stop().catch(() => {}); html5QrCode = null; }
        }

        // ===== Radio button visual =====
        document.querySelectorAll('input[name="type"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('input[name="type"]').forEach(r => {
                    r.closest('label').classList.remove('border-green-500','bg-green-50','border-red-500','bg-red-50','border-yellow-500','bg-yellow-50','border-gray-300');
                    r.closest('label').classList.add('border-gray-300');
                });
                const lbl = this.closest('label');
                lbl.classList.remove('border-gray-300');
                if (this.value === 'in')         lbl.classList.add('border-green-500','bg-green-50');
                else if (this.value === 'out')   lbl.classList.add('border-red-500','bg-red-50');
                else                             lbl.classList.add('border-yellow-500','bg-yellow-50');
            });
        });

        // ===== Init first row on load =====
        document.addEventListener('DOMContentLoaded', addPartRow);
    </script>
</x-layouts.app>

