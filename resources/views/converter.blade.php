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
                    <!-- Loading indicator -->
                    <div id="loadingIndicator" class="text-center mt-3" style="display: none;">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Converting...
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-custom btn-orange text-center mt-3">Convert</button>
                        <button type="button" id="clearBtn" class="btn btn-secondary text-center mt-3 ms-2">Clear</button>
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

            <!-- Unit Information -->
            <div class="mt-4">
                <h5>Supported Unit Types:</h5>
                <ul class="list-unstyled">
                    <li><strong>Length:</strong> {{ count($unitTypes['length']) }} units</li>
                    <li><strong>Area:</strong> {{ count($unitTypes['area']) }} units</li>
                    <li><strong>Volume:</strong> {{ count($unitTypes['volume']) }} units</li>
                    <li><strong>Mass/Weight:</strong> {{ count($unitTypes['mass']) }} units</li>
                    <li><strong>Speed:</strong> {{ count($unitTypes['speed']) }} units</li>
                    <li><strong>Time:</strong> {{ count($unitTypes['time']) }} units</li>
                    <li><strong>Angle:</strong> {{ count($unitTypes['angle']) }} units</li>
                    <li><strong>Pressure:</strong> {{ count($unitTypes['pressure']) }} units</li>
                    <li><strong>Energy:</strong> {{ count($unitTypes['energy']) }} units</li>
                    <li><strong>Data Storage:</strong> {{ count($unitTypes['data']) }} units</li>
                    <li><strong>Frequency:</strong> {{ count($unitTypes['frequency']) }} units</li>
                </ul>
            </div>
        </div>
    </div>
    <script>
        function resizeSelect(el) {
            var temp = document.createElement("span");
            temp.style.visibility = "hidden";
            temp.style.whiteSpace = "nowrap";
            temp.style.position = "absolute";
            temp.style.fontFamily = window.getComputedStyle(el).fontFamily;
            temp.style.fontSize = window.getComputedStyle(el).fontSize;
            temp.style.fontWeight = window.getComputedStyle(el).fontWeight;
            temp.innerHTML = el.options[el.selectedIndex].text;
            document.body.appendChild(temp);

            // Hitung lebar span, tambahkan padding + tombol dropdown indicator
            el.style.width = (temp.offsetWidth + 60) + "px"; // 50px untuk aman
            document.body.removeChild(temp);
        }

        // Jalankan saat pertama kali load
        resizeSelect(document.getElementById('dynamicSelect'));

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('dynamicSelect');
            document.getElementById('dynamicSelect').addEventListener('change', function() {
                resizeSelect(this);
            });
            const fromUnit = document.getElementById('fromUnit');
            const toUnit = document.getElementById('toUnit');

            function filterUnitsByType() {
                const selectedTypeId = typeSelect.value;

                filterOptions(fromUnit, selectedTypeId);
                filterOptions(toUnit, selectedTypeId);
            }

            function filterOptions(selectElement, selectedTypeId) {
                let hasSelected = false;
                Array.from(selectElement.options).forEach(option => {
                    const match = option.getAttribute('data-type') === selectedTypeId;
                    option.hidden = !match;
                    if (match && !hasSelected) {
                        option.selected = true;
                        hasSelected = true;
                    }
                });

                // Jika tidak ada yang cocok, kosongkan pilihan
                if (!hasSelected) {
                    selectElement.selectedIndex = -1;
                }
            }

            // Event listener saat user memilih tipe
            typeSelect.addEventListener('change', filterUnitsByType);

            // Jalankan sekali saat pertama load
            filterUnitsByType();
        });

    </script>
    <script>
        // Data unit types dari PHP
        const unitTypes = @json($unitTypes);
        let autoConvertTimeout = null;

        function changeUnitType() {
            const selectedType = document.getElementById('dynamicSelect').value;
            const fromUnit = document.getElementById('fromUnit');
            const toUnit = document.getElementById('toUnit');
            const convertTypeInput = document.getElementById('convertTypeInput');

            convertTypeInput.value = selectedType;

            // Clear dropdown secara manual (bukan innerHTML)
            while (fromUnit.options.length > 0) {
                fromUnit.remove(0);
            }
            while (toUnit.options.length > 0) {
                toUnit.remove(0);
            }

            const units = unitTypes[selectedType];

            if (units) {
                for (const [code, name] of Object.entries(units)) {
                    const option1 = new Option(`${name} (${code})`, code);
                    const option2 = new Option(`${name} (${code})`, code);

                    option1.setAttribute('data-type', selectedType);
                    option2.setAttribute('data-type', selectedType);


                    fromUnit.add(option1);
                    toUnit.add(option2);
                }
            }

            console.log('Dropdown fromUnit:', fromUnit.innerHTML);
            console.log('Dropdown toUnit:', toUnit.innerHTML);

            document.getElementById('toValue').value = '';
            hideAlerts();
        }


        function autoConvert() {
            // Clear previous timeout
            if (autoConvertTimeout) {
                clearTimeout(autoConvertTimeout);
            }

            // Set new timeout for debouncing
            autoConvertTimeout = setTimeout(() => {
                const fromValue = document.getElementById('fromValue').value;
                const fromUnit = document.getElementById('fromUnit').value;
                const toUnit = document.getElementById('toUnit').value;

                if (fromValue && fromUnit && toUnit && fromValue !== '') {
                    showLoading(true);
                    hideAlerts();

                    // Prepare form data
                    const formData = new FormData();
                    formData.append('fromValue', fromValue);
                    formData.append('fromUnit', fromUnit);
                    formData.append('toUnit', toUnit);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route("convert") }}', {
                            method: 'POST'
                            , headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            , }
                            , body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
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
                            showLoading(false);
                            showError('Network error occurred');
                            console.error('Error:', error);
                        });
                }
            }, 500); // 500ms delay for debouncing
        }

        function showLoading(show) {
            const loadingIndicator = document.getElementById('loadingIndicator');
            loadingIndicator.style.display = show ? 'block' : 'none';
        }

        function hideAlerts() {
            const errorAlert = document.getElementById('errorAlert');
            const resultAlert = document.getElementById('resultAlert');
            if (errorAlert) errorAlert.style.display = 'none';
            if (resultAlert) resultAlert.style.display = 'none';
        }

        // function showError(message) {
        //     hideAlerts();
        //     const errorHtml = `
        //         <div class="alert alert-danger mt-3" id="dynamicError">
        //             ${message}
        //             <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        //         </div>
        //     `;
        //     document.querySelector('.convert-form').insertAdjacentHTML('afterend', errorHtml);
        // }

        // function showSuccess(message) {
        //     hideAlerts();
        //     const successHtml = `
        //         <div class="alert alert-success mt-3" id="dynamicSuccess">
        //             <strong>Conversion Result:</strong><br>
        //             <small class="text-muted">${message}</small>
        //         </div>
        //     `;
        //     document.querySelector('.convert-form').insertAdjacentHTML('afterend', successHtml);
        // }

        function clearForm() {
            document.getElementById('fromValue').value = '';
            document.getElementById('toValue').value = '';
            document.getElementById('dynamicSelect').value = 'length';
            changeUnitType();
            hideAlerts();

            // Remove dynamic alerts
            const dynamicError = document.getElementById('dynamicError');
            const dynamicSuccess = document.getElementById('dynamicSuccess');
            if (dynamicError) dynamicError.remove();
            if (dynamicSuccess) dynamicSuccess.remove();
        }

        // Event listeners
        document.getElementById('fromValue').addEventListener('input', autoConvert);
        document.getElementById('fromUnit').addEventListener('change', autoConvert);
        document.getElementById('toUnit').addEventListener('change', autoConvert);
        document.getElementById('clearBtn').addEventListener('click', clearForm);

        // Prevent form submission if auto-convert is working
        document.getElementById('converterForm').addEventListener('submit', function(e) {
            if (document.getElementById('loadingIndicator').style.display !== 'none') {
                e.preventDefault();
                return false;
            }
        });

        // Initialize with current selection if page was refreshed with session data
        document.addEventListener('DOMContentLoaded', function() {
            changeUnitType();
            @if(session('fromUnit'))
            // Find the unit type for the session unit
            const sessionFromUnit = '{{ session('
            fromUnit ') }}';
            for (const [type, units] of Object.entries(unitTypes)) {
                if (units.hasOwnProperty(sessionFromUnit)) {
                    document.getElementById('dynamicSelect').value = type;
                    changeUnitType();
                    break;
                }
            }
            @endif
        });

    </script>
    <script>
        const historyKey = 'conversionHistory';

        function saveToHistory(fromValue, fromUnit, toValue, toUnit) {
            const history = JSON.parse(localStorage.getItem(historyKey)) || [];

            const entry = {
                id: Date.now()
                , fromValue
                , fromUnit
                , toValue
                , toUnit
            };

            history.unshift(entry); // tambahkan ke awal
            if (history.length > 20) history.pop(); // maksimal 20 entri
            localStorage.setItem(historyKey, JSON.stringify(history));
            renderHistory();
        }

        function renderHistory() {
            const container = document.getElementById('historyContainer');
            const history = JSON.parse(localStorage.getItem(historyKey)) || [];

            container.innerHTML = '';
            history.forEach(entry => {
                const item = document.createElement('div');
                item.className = "result-convert mb-3 position-relative pe-4 me-3";
                item.style.borderBottom = "1px solid black";
                item.innerHTML = `
                <button type="button" class="btn position-absolute top-0 end-0 p-0" aria-label="Delete" title="Hapus history" onclick="deleteHistoryItem(${entry.id})">
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

        function showSuccess(message) {
            hideAlerts();

            // Ambil data dari form
            const fromValue = document.getElementById('fromValue').value;
            const fromUnit = document.getElementById('fromUnit').value;
            const toUnit = document.getElementById('toUnit').value;
            const toValue = document.getElementById('toValue').value;

            // Simpan ke history
            saveToHistory(fromValue, fromUnit, toValue, toUnit);

            //     const successHtml = `
            //     <div class="alert alert-success mt-3" id="dynamicSuccess">
            //         <strong>Conversion Result:</strong><br>
            //         <small class="text-muted">${message}</small>
            //     </div>
            // `;
            document.querySelector('.convert-form').insertAdjacentHTML('afterend', successHtml);
        }

        // Inisialisasi history saat halaman dimuat
        document.addEventListener('DOMContentLoaded', renderHistory);

    </script>

</x-layout>
