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
                        <select name="convertType" id="dynamicSelect" class="form-select form-select-sm" onchange="resizeSelect(this)">
                            <option value="1">Length</option>
                            <option value="2">Weight</option>
                            <option value="3">Temperature</option>
                        </select>
                    </span>
                </div>

                <!-- Form untuk konversi -->
                <form method="POST" action="#" class="convert-form mt-4">
                    @csrf
                    <div class="mb-3">
                        <label for="fromValue" class="form-label">From :</label>
                        <div class="input-group flex-nowrap">
                            <input type="number" name="fromValue" id="fromValue" class="form-control" placeholder="Masukkan Nilai" required>
                            <select name="fromUnit" id="fromUnit" class="form-select">
                                <option value="1" data-type="1">Meter (m)</option>
                                <option value="2" data-type="1">Kilometer (km)</option>
                                <option value="3" data-type="2">Kilogram (kg)</option>
                                <option value="4" data-type="2">Gram (g)</option>
                                <option value="5" data-type="3">Celsius (°C)</option>
                                <option value="6" data-type="3">Fahrenheit (°F)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="toUnit" class="form-label">To :</label>
                        <div class="input-group">
                            <input type="text" name="toValue" id="toValue" class="form-control" placeholder="Hasil Konversi" readonly>
                            <select name="toUnit" id="toUnit" class="form-select">
                                <option value="1" data-type="1">Meter (m)</option>
                                <option value="2" data-type="1">Kilometer (km)</option>
                                <option value="3" data-type="2">Kilogram (kg)</option>
                                <option value="4" data-type="2">Gram (g)</option>
                                <option value="5" data-type="3">Celsius (°C)</option>
                                <option value="6" data-type="3">Fahrenheit (°F)</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-custom btn-orange text-center mt-3">Convert</button>
                    </div>
                </form>

                <!-- Menampilkan hasil konversi jika ada -->
                @if(session('outputValue'))
                <div class="mt-3 text-center">
                    <strong>Hasil Konversi: {{ session('outputValue') }}</strong>
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
</x-layout>
