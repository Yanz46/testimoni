document.addEventListener('DOMContentLoaded', function() {
    // 1. Daftar Negara Lengkap
    const countries = [
        "Indonesia",
        "United States (US)", "United Kingdom (UK)", "Singapore", "Japan", "Canada", "Australia",
        "Germany", "France", "Netherlands", "Spain", "Italy", "Switzerland", "Sweden", "Norway",
        "South Korea", "Thailand", "India", "Philippines", "Malaysia", "Vietnam", "China", "Hong Kong",
        "Brazil", "Mexico", "Argentina", "Russia", "South Africa", "Egypt",
        "Finland", "Austria", "Belgium", "Portugal", "Greece", "Ireland", "Poland", "Denmark", "Czechia"
    ];

    const countrySelect = document.getElementById('country-select');
    const appSelect = document.getElementById('app-select');
    const waButton = document.getElementById('wa-cta-button');
    const phoneNumber = "6283190293490";
    
    // Fungsi untuk mengkodekan URL
    const encodeMessage = (message) => encodeURIComponent(message);

    // 2. Generate Opsi Negara ke Dropdown
    if (countrySelect) {
        // Urutkan daftar negara (kecuali yang paling atas, jika Anda ingin Indonesia selalu di puncak)
        const sortedCountries = countries.slice(1).sort();
        const finalCountries = [countries[0]].concat(sortedCountries);

        finalCountries.forEach(country => { 
            const option = document.createElement('option');
            option.value = country;
            option.textContent = country;
            countrySelect.appendChild(option);
        });
        
        // 3. Logika Perubahan Pilihan (Memeriksa Keduanya)
        const updateWaButton = function() {
            const selectedCountry = countrySelect.value;
            const selectedApp = appSelect.value;
            
            if (selectedCountry && selectedApp) {
                // Keduanya terpilih, aktifkan tombol
                waButton.disabled = false;
                waButton.style.opacity = '1';
                waButton.style.pointerEvents = 'auto';

                // Buat pesan WhatsApp dinamis
                const customMessage = `Halo ka, saya mau pesan nomor virtual negara *${selectedCountry}* untuk aplikasi *${selectedApp}*. Mohon info harga dan stoknya.`;
                const waLink = `https://wa.me/${phoneNumber}?text=${encodeMessage(customMessage)}`;
                
                // Update link tombol
                waButton.href = waLink;
            } else {
                // Salah satu atau keduanya belum terpilih, nonaktifkan tombol
                waButton.disabled = true;
                waButton.style.opacity = '0.5';
                waButton.style.pointerEvents = 'none';
                waButton.href = '#';
            }
        };

        // Pasang event listener ke kedua dropdown
        countrySelect.addEventListener('change', updateWaButton);
        appSelect.addEventListener('change', updateWaButton);
        
        // Panggil sekali saat dimuat untuk memastikan tombol nonaktif
        updateWaButton();
    }

    // 4. Fitur Smooth Scrolling (untuk link # di index.html)
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId.length > 1 && document.querySelector(targetId)) { 
                e.preventDefault();
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
