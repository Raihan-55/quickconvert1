<x-layout>
    <div class="container">
        <x-Background />
        <header>
            <x-Navbar />
        </header>
        <div class="d-flex justify-content-center mt-5">
            <div class="converter-box d-inline-block">
                <div class="convert-title text-center">
                    Convert
                    <span>
                        <select name="convertType" id="dynamicSelect" class="form-select form-select-sm" onchange="changeUnitType()">
                            <option value="length">Length</option>
                            <option value="area">Area</option>
                            <option value="volume">Volume</option>
                            <option value="mass">Mass/Weight</option>
                            <option value="speed">Speed</option>
                            <option value="time">Time</option>
                            <option value="angle">Angle</option>
                            <option value="pressure">Pressure</option>
                            <option value="energy">Energy</option>
                            <option value="data">Data Storage</option>
                            <option value="frequency">Frequency</option>
                        </select>
                    </span>
                </div>

                <!-- Form untuk konversi -->
                <form method="POST" action="{{ route('convert') }}" class="convert-form mt-4" id="converterForm">
                    @csrf
                    <input type="hidden" name="convertType" id="convertTypeInput" value="length">

                    <div class="mb-3">
                        <label for="fromValue" class="form-label">From :</label>
                        <div class="input-group flex-nowrap">
                            <input type="number" name="fromValue" id="fromValue" class="form-control" placeholder="Masukkan Nilai" step="any" value="{{ old('fromValue', session('fromValue')) }}" required>
                            <select name="fromUnit" id="fromUnit" class="form-select">
                                @foreach($unitTypes['length'] as $code => $name)
                                <option value="{{ $code }}" {{ old('fromUnit', session('fromUnit')) == $code ? 'selected' : '' }}>
                                    {{ $name }} ({{ $code }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="toUnit" class="form-label">To :</label>
                        <div class="input-group flex-nowrap">
                            <input type="text" name="toValue" id="toValue" class="form-control" placeholder="Hasil Konversi" value="{{ session('outputValue') }}" readonly>
                            <select name="toUnit" id="toUnit" class="form-select">
                                @foreach($unitTypes['length'] as $code => $name)
                                <option value="{{ $code }}" {{ old('toUnit', session('toUnit')) == $code ? 'selected' : '' }}>
                                    {{ $name }} ({{ $code }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="toggleAutoConvert" checked>
                        <label class="form-check-label" for="toggleAutoConvert">
                            Auto Convert
                        </label>
                    </div>
                    <!-- Loading indicator -->
                    <div id="loadingIndicator" class="text-center mt-3" style="display: none;">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Converting...
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-custom btn-orange text-center mt-3">Convert</button>
                        <button type="button" id="clearBtn" class="btn btn-custom btn-gray text-center mt-3 ms-2">Clear</button>
                    </div>
                </form>

                <!-- Menampilkan error jika ada -->
                @if($errors->any())
                <div class="alert alert-danger mt-3" id="errorAlert">
                    @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
            </div>
        </div>

        <div class="convert-text p-5">
            <p>
                <strong>QuickConvert</strong> adalah platform berbasis web yang dikembangkan untuk memudahkan proses konversi satuan secara cepat, akurat, dan praktis. Melalui antarmuka yang sederhana dan responsif, pengguna dapat dengan mudah memasukkan nilai, memilih satuan asal dan tujuan, lalu memperoleh hasil konversi dalam waktu singkat.
            </p>
            <p>
                Website ini dihadirkan untuk memenuhi kebutuhan pelajar, profesional, maupun masyarakat umum yang memerlukan alat bantu konversi satuan yang cepat dan efisien.
            </p>
        </div>
    </div>
    <script>
        /**
         * Menyesuaikan lebar elemen <select> sesuai teks yang dipilih
         */
        function resizeSelect(el) {
            const temp = document.createElement("span");
            const style = window.getComputedStyle(el);

            // Set gaya span sementara
            Object.assign(temp.style, {
                visibility: "hidden"
                , whiteSpace: "nowrap"
                , position: "absolute"
                , fontFamily: style.fontFamily
                , fontSize: style.fontSize
                , fontWeight: style.fontWeight
            });

            temp.innerHTML = el.options[el.selectedIndex].text;
            document.body.appendChild(temp);

            // Set lebar elemen select + ruang untuk icon dropdown
            el.style.width = (temp.offsetWidth + 60) + "px";
            document.body.removeChild(temp);
        }

        /**
         * Filter opsi unit berdasarkan jenis yang dipilih
         */
        function filterOptions(selectElement, selectedTypeId) {
            let hasSelected = false;

            Array.from(selectElement.options).forEach(option => {
                const isMatch = option.getAttribute('data-type') === selectedTypeId;
                option.hidden = !isMatch;

                if (isMatch && !hasSelected) {
                    option.selected = true;
                    hasSelected = true;
                }
            });

            if (!hasSelected) selectElement.selectedIndex = -1;
        }

        /**
         * Jalankan saat DOM selesai dimuat
         */
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('dynamicSelect');
            const fromUnit = document.getElementById('fromUnit');
            const toUnit = document.getElementById('toUnit');

            function filterUnitsByType() {
                const selectedTypeId = typeSelect.value;
                filterOptions(fromUnit, selectedTypeId);
                filterOptions(toUnit, selectedTypeId);
            }

            typeSelect.addEventListener('change', function() {
                resizeSelect(this);
                filterUnitsByType();
            });

            // Resize awal dan filter unit
            resizeSelect(typeSelect);
            filterUnitsByType();
        });

    </script>

    <script>
        // Unit type dari backend
        const unitTypes = @json($unitTypes);
        let autoConvertTimeout = null;

        // Debug function untuk melihat apa yang terjadi
        function debugLog(message, data = null) {
            console.log(`[CONVERTER DEBUG] ${message}`, data);
        }

        /**
         * Mengubah dropdown unit saat jenis diubah
         */
        function changeUnitType() {
            debugLog('changeUnitType called');
            const selectedType = document.getElementById('dynamicSelect').value;
            const fromUnit = document.getElementById('fromUnit');
            const toUnit = document.getElementById('toUnit');
            const convertTypeInput = document.getElementById('convertTypeInput');

            debugLog('Selected type:', selectedType);

            // Simpan nilai yang dipilih sebelumnya
            const currentFromUnit = fromUnit.value;
            const currentToUnit = toUnit.value;

            convertTypeInput.value = selectedType;

            // Hapus semua opsi
            fromUnit.innerHTML = '';
            toUnit.innerHTML = '';

            const units = unitTypes[selectedType];
            if (units) {
                debugLog('Available units:', units);
                for (const [code, name] of Object.entries(units)) {
                    const option1 = new Option(`${name} (${code})`, code);
                    const option2 = new Option(`${name} (${code})`, code);
                    option1.setAttribute('data-type', selectedType);
                    option2.setAttribute('data-type', selectedType);
                    fromUnit.add(option1);
                    toUnit.add(option2);
                }

                // Coba restore nilai sebelumnya jika masih tersedia
                if (units[currentFromUnit]) {
                    fromUnit.value = currentFromUnit;
                }
                if (units[currentToUnit]) {
                    toUnit.value = currentToUnit;
                }
            }

            // Hanya clear hasil jika ini adalah perubahan user, bukan inisialisasi
            if (!document.getElementById('isInitializing')) {
                document.getElementById('toValue').value = '';
            }
            hideAlerts();
        }

        /**
         * Melakukan konversi otomatis dengan debounce
         */
        function autoConvert() {
            debugLog('autoConvert called');
            const toggle = document.getElementById('toggleAutoConvert');
            if (toggle && !toggle.checked) {
                debugLog('Auto convert disabled');
                return;
            }

            // Perbaiki duplikasi clearTimeout
            clearTimeout(autoConvertTimeout);

            autoConvertTimeout = setTimeout(() => {
                const fromValue = document.getElementById('fromValue').value;
                const fromUnit = document.getElementById('fromUnit').value;
                const toUnit = document.getElementById('toUnit').value;

                debugLog('Auto convert values:', {
                    fromValue
                    , fromUnit
                    , toUnit
                });

                if (fromValue && fromUnit && toUnit) {
                    performConversion(fromValue, fromUnit, toUnit);
                }
            }, 500);
        }

        /**
         * Fungsi terpisah untuk melakukan konversi
         */
        function performConversion(fromValue, fromUnit, toUnit) {
            debugLog('performConversion called', {
                fromValue
                , fromUnit
                , toUnit
            });
            showLoading(true);
            hideAlerts();

            const formData = new FormData();
            formData.append('fromValue', fromValue);
            formData.append('fromUnit', fromUnit);
            formData.append('toUnit', toUnit);

            // Coba ambil CSRF token dari beberapa sumber
            let csrfToken = null;
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                csrfToken = csrfMeta.getAttribute('content');
            }
            if (!csrfToken) {
                const csrfInput = document.querySelector('input[name="_token"]');
                if (csrfInput) {
                    csrfToken = csrfInput.value;
                }
            }
            if (!csrfToken) {
                csrfToken = '{{ csrf_token() }}';
            }

            formData.append('_token', csrfToken);
            debugLog('CSRF Token:', csrfToken);

            const convertUrl = '{{ route("convert") }}';
            debugLog('Convert URL:', convertUrl);

            fetch(convertUrl, {
                    method: 'POST'
                    , headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                        , 'Accept': 'application/json'
                    , }
                    , body: formData
                })
                .then(response => {
                    debugLog('Response status:', response.status);
                    debugLog('Response headers:', Object.fromEntries(response.headers));

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    debugLog('Response data:', data);
                    showLoading(false);
                    if (data.success) {
                        document.getElementById('toValue').value = data.result;
                        showSuccess(`${data.fromValue} ${data.fromUnit} = ${data.result} ${data.toUnit}`);
                    } else {
                        showError(data.error || 'Conversion failed');
                        document.getElementById('toValue').value = '';
                    }
                })
                .catch(error => {
                    debugLog('Fetch error:', error);
                    showLoading(false);
                    showError('Network error occurred: ' + error.message);
                    console.error('Conversion error:', error);
                });
        }

        function showLoading(show) {
            debugLog('showLoading:', show);
            const loadingIndicator = document.getElementById('loadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.style.display = show ? 'block' : 'none';
            } else {
                debugLog('Loading indicator not found!');
            }
        }

        function hideAlerts() {
            const errorAlert = document.getElementById('errorAlert');
            const resultAlert = document.getElementById('resultAlert');
            const dynamicError = document.getElementById('dynamicError');
            const dynamicSuccess = document.getElementById('dynamicSuccess');

            if (errorAlert) errorAlert.style.display = 'none';
            if (resultAlert) resultAlert.style.display = 'none';
            if (dynamicError) dynamicError.remove();
            if (dynamicSuccess) dynamicSuccess.remove();
        }

        function showError(message) {
            debugLog('showError:', message);
            hideAlerts();
            const errorHtml = `
        <div class="alert alert-danger mt-3" id="dynamicError">
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        </div>
    `;
            const formElement = document.querySelector('.convert-form');
            if (formElement) {
                formElement.insertAdjacentHTML('afterend', errorHtml);
            } else {
                debugLog('Form element not found for error display');
            }
        }

        function showSuccess(message) {
            debugLog('showSuccess:', message);
            hideAlerts();

            // Simpan ke history
            const fromValue = document.getElementById('fromValue').value;
            const fromUnit = document.getElementById('fromUnit').value;
            const toUnit = document.getElementById('toUnit').value;
            const toValue = document.getElementById('toValue').value;

            if (typeof saveToHistory === 'function') {
                saveToHistory(fromValue, fromUnit, toValue, toUnit);
            }

            const successHtml = `
        <div class="alert alert-success mt-3" id="dynamicSuccess">
            <strong>Conversion Result:</strong><br>
            <small class="text-muted">${message}</small>
        </div>
    `;
            const formElement = document.querySelector('.convert-form');
            if (formElement) {
                formElement.insertAdjacentHTML('afterend', successHtml);
            } else {
                debugLog('Form element not found for success display');
            }
        }

        function clearForm() {
            debugLog('clearForm called');
            document.getElementById('fromValue').value = '';
            document.getElementById('toValue').value = '';
            document.getElementById('dynamicSelect').value = 'length';
            changeUnitType();
            hideAlerts();
        }

        // Event listeners yang diperbaiki
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('DOM Content Loaded');

            // Periksa apakah semua elemen ada
            const elements = {
                converterForm: document.getElementById('converterForm')
                , fromValue: document.getElementById('fromValue')
                , fromUnit: document.getElementById('fromUnit')
                , toUnit: document.getElementById('toUnit')
                , clearBtn: document.getElementById('clearBtn')
                , dynamicSelect: document.getElementById('dynamicSelect')
                , toggleAutoConvert: document.getElementById('toggleAutoConvert')
            };

            debugLog('Elements check:', elements);

            // Tandai bahwa ini adalah inisialisasi
            const initFlag = document.createElement('div');
            initFlag.id = 'isInitializing';
            initFlag.style.display = 'none';
            document.body.appendChild(initFlag);

            // Setup form submission handler yang diperbaiki
            if (elements.converterForm) {
                elements.converterForm.addEventListener('submit', function(e) {
                    debugLog('Form submit event triggered');
                    e.preventDefault(); // Selalu prevent default submit

                    const loadingElement = document.getElementById('loadingIndicator');
                    const isLoading = loadingElement && loadingElement.style.display !== 'none';
                    if (isLoading) {
                        debugLog('Conversion already in progress');
                        alert('Please wait for the current conversion to finish.');
                        return;
                    }

                    // Lakukan konversi manual saat tombol ditekan
                    const fromValue = document.getElementById('fromValue').value;
                    const fromUnit = document.getElementById('fromUnit').value;
                    const toUnit = document.getElementById('toUnit').value;

                    debugLog('Manual conversion triggered:', {
                        fromValue
                        , fromUnit
                        , toUnit
                    });

                    if (fromValue && fromUnit && toUnit) {
                        performConversion(fromValue, fromUnit, toUnit);
                    } else {
                        debugLog('Missing required fields');
                        showError('Please fill in all required fields.');
                    }
                });
                debugLog('Form submit listener attached');
            } else {
                debugLog('ERROR: Converter form not found!');
            }

            // Setup auto convert listeners
            if (elements.fromValue) {
                elements.fromValue.addEventListener('input', autoConvert);
                debugLog('fromValue input listener attached');
            }
            if (elements.fromUnit) {
                elements.fromUnit.addEventListener('change', autoConvert);
                debugLog('fromUnit change listener attached');
            }
            if (elements.toUnit) {
                elements.toUnit.addEventListener('change', autoConvert);
                debugLog('toUnit change listener attached');
            }
            if (elements.clearBtn) {
                elements.clearBtn.addEventListener('click', clearForm);
                debugLog('clearBtn click listener attached');
            }

            // Inisialisasi unit type
            changeUnitType();

            // Restore session data jika ada
            @if(session('fromUnit'))
            const sessionFromUnit = '{{ session('
            fromUnit ') }}';
            debugLog('Restoring session fromUnit:', sessionFromUnit);
            for (const [type, units] of Object.entries(unitTypes)) {
                if (units.hasOwnProperty(sessionFromUnit)) {
                    document.getElementById('dynamicSelect').value = type;
                    changeUnitType();
                    break;
                }
            }
            @endif

            // Hapus flag inisialisasi
            setTimeout(() => {
                const flag = document.getElementById('isInitializing');
                if (flag) flag.remove();
                debugLog('Initialization complete');
            }, 100);

            debugLog('All event listeners setup complete');
        });

    </script>

    <script>
        const historyKey = 'conversionHistory';

        /**
         * Simpan data konversi ke localStorage
         */
        function saveToHistory(fromValue, fromUnit, toValue, toUnit) {
            const history = JSON.parse(localStorage.getItem(historyKey)) || [];
            const entry = {
                id: Date.now()
                , fromValue
                , fromUnit
                , toValue
                , toUnit
            };
            history.unshift(entry);
            if (history.length > 20) history.pop(); // Batasi 20 entri
            localStorage.setItem(historyKey, JSON.stringify(history));
            renderHistory();
        }

        /**
         * Render daftar riwayat konversi
         */
        function renderHistory() {
            const container = document.getElementById('historyContainer');
            const history = JSON.parse(localStorage.getItem(historyKey)) || [];

            container.innerHTML = '';
            history.forEach(entry => {
                const item = document.createElement('div');
                item.className = "result-convert mb-3 position-relative pe-4 me-3";
                item.style.borderBottom = "1px solid black";
                item.innerHTML = `
                <button type="button" class="btn position-absolute top-0 end-0 p-0" title="Hapus history" onclick="deleteHistoryItem(${entry.id})">
                    <i class="bi bi-trash3"></i>
                </button>
                <h5>${entry.toValue} (${entry.toUnit})</h5>
                <h6>${entry.fromValue} (${entry.fromUnit})</h6>
            `;
                container.appendChild(item);
            });
        }

        function deleteHistoryItem(id) {
            const history = JSON.parse(localStorage.getItem(historyKey)) || [];
            const updated = history.filter(entry => entry.id !== id);
            localStorage.setItem(historyKey, JSON.stringify(updated));
            renderHistory();
        }

        function clearAllHistory() {
            localStorage.removeItem(historyKey);
            renderHistory();
        }

        // Inisialisasi history saat halaman dimuat
        document.addEventListener('DOMContentLoaded', renderHistory);

    </script>


</x-layout>
