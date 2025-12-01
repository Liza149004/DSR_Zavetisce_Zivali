<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title">ShelterCompass</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body>

    <header>
        <div class="logo">
            <span class="material-icons" style="color: var(--green-dark);">favorite</span>
            <span class="logo-text">ShelterCompass</span>
        </div>
        <span class="material-icons mode-toggle">dark_mode</span>
    </header>

    <!-- Glavna Vsebina: Tukaj se bo spreminjala med Galerijo in Profilom -->
    <div id="main-content">
        <!-- Vsebina se bo vstavila tukaj z JavaScriptom -->
    </div>

    <!-- Noga (Footer) - Ta ostane statična -->
    <footer>
        <div class="footer-content">
            <div class="contact-info">
                <h2>Obiščite Nas</h2>
                <p>123 Shelter Lane</p>
                <p>Springfield, ST 12345</p>
                <p>Telefon: (555) 123-4567</p>
                <p>E-mail: info@sheltercompass.org</p>
            </div>
            
            <div class="location-map">
                <h2>Naša Lokacija</h2>
                <div class="map-placeholder">
                    <div class="map-controls">
                        <button>+</button>
                        <button>−</button>
                    </div>
                    <div class="map-footer-text">
                        Prijavi težavo | &copy; OpenStreetMap contributors
                    </div>
                </div>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; <span id="current-year"></span> ShelterCompass. Vse pravice pridržane.</p>
        </div>
    </footer>

    <script>
        // --- Podatki o Živalih (nadomešča PHP array) ---
        const animals = [
            { id: 1, name: 'Max', species: 'Dog', breed: 'Golden Retriever', gender: 'Samec', age: '3 years old', status: 'Available', image: 'https://placehold.co/900x675/B2EBF2/006064?text=Max+the+Dog', weight: '30 kg', color: 'Golden', description: 'Max je prijazna duša, ki obožuje dolge sprehode in prinašanje. Idealna družina so tisti, ki radi preživljajo čas na prostem. Zelo prijazen do otrok.', health: ['Cepljen', 'Kastriran'] },
            { id: 2, name: 'Luna', species: 'Cat', breed: 'Siamese', gender: 'Samica', age: 'Odrasla', status: 'Available', image: 'https://placehold.co/900x675/FFCDD2/C62828?text=Luna+the+Cat', weight: '4.2 kg', color: 'Cream & Brown', description: 'Luna je elegantna, a precej neodvisna. Rada opazuje svet z višine in potrebuje miren dom brez majhnih otrok.', health: ['Cepljena', 'Sterilizirana'] },
            { id: 3, name: 'Rocky', species: 'Dog', breed: 'German Shepherd', gender: 'Samec', age: 'Senior', status: 'Adopted', image: 'https://placehold.co/900x675/B39DDB/4527A0?text=Rocky+the+Dog', weight: '35 kg', color: 'Black & Tan', description: 'Rocky je bil pred kratkim posvojen in uživa v svojem novem domu!', health: ['Cepljen', 'Kastriran'] },
            { id: 4, name: 'Whiskers', species: 'Cat', breed: 'Tabby', gender: 'Samec', age: '1 year old', status: 'Available', image: 'https://placehold.co/900x675/B2EBF2/006064?text=Whiskers+the+Cat', weight: '3.5 kg', color: 'Orange Tabby', description: 'Whiskers je igriv maček Tabby, ki obožuje igrače in plezanje. Je neodvisen, a uživa tudi v crkljanju v naročju. Idealen je za prve lastnike mačk.', health: ['Cepljen', 'Steriliziran'] },
            { id: 5, name: 'Charlie', species: 'Dog', breed: 'Bulldog', gender: 'Samec', age: 'Odrasel', status: 'In Care', image: 'https://placehold.co/900x675/FFECB3/FF6F00?text=Charlie+the+Dog', weight: '25 kg', color: 'Brindle', description: 'Charlie je trenutno v oskrbi in zdravljenju. Počasi bo pripravljen za posvojitev.', health: ['V Zdravljenju'] },
        ];

        const mainContent = document.getElementById('main-content');
        const pageTitle = document.getElementById('page-title');

        // Nastavi trenutno leto v nogi
        document.getElementById('current-year').textContent = new Date().getFullYear();

        // Prikliče profil živali
        function renderAnimalProfile(animal) {
            pageTitle.textContent = `ShelterCompass - Profil ${animal.name}`;
            
            const statusClass = animal.status.toLowerCase().replace(' ', '-');
            const iconEmoji = animal.species === 'Dog' ? '🐕' : '🐈';
            const genderIcon = animal.gender === 'Samec' ? 'male' : 'female';
            const healthBadges = animal.health.map(h => `
                <span class="flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                    <span class="material-icons text-lg mr-1">${h === 'V Zdravljenju' ? 'healing' : 'check_circle'}</span>
                    ${h}
                </span>
            `).join('');

            mainContent.innerHTML = `
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Povezava Nazaj na Galerijo -->
                    <div class="mb-6">
                        <a href="#" onclick="showGallery(); return false;" class="flex items-center text-shelter-green-dark hover:text-shelter-green-dark/80 font-semibold transition duration-150">
                            <span class="material-icons mr-2">arrow_back</span>
                            Nazaj na Galerijo
                        </a>
                    </div>

                    <!-- Glavna Vsebina: Dve Koloni (2/3 & 1/3) -->
                    <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                        
                        <!-- LEVA KOLONA: Podrobnosti o Živali (2/3) -->
                        <div class="lg:col-span-2">
                            <!-- Glavna Slika -->
                            <div class="w-full aspect-[4/3] bg-gray-200 rounded-xl overflow-hidden shadow-lg mb-6">
                                <img src="${animal.image}" 
                                     alt="Glavna slika živali ${animal.name}"
                                     class="w-full h-full object-cover">
                            </div>

                            <!-- Ime in Status -->
                            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center">
                                        <h1 class="text-4xl font-extrabold text-gray-900 mr-3">${animal.name}</h1>
                                        <span class="text-2xl" role="img">${iconEmoji}</span>
                                    </div>
                                    <span class="px-4 py-2 text-sm font-bold text-white bg-${statusClass} rounded-full shadow-md">
                                        ${animal.status}
                                    </span>
                                </div>
                                <p class="text-lg text-gray-500 font-medium">${animal.breed}</p>

                                <!-- Mreža s Statistiko (Stats Grid) -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                                    <!-- Stat 1: Starost -->
                                    <div class="p-4 bg-shelter-green-light rounded-lg text-center shadow-sm flex flex-col items-center justify-center">
                                        <span class="material-icons text-xl text-shelter-green-dark">calendar_today</span>
                                        <p class="text-xl font-bold mt-1 text-gray-900">${animal.age}</p>
                                        <p class="text-xs font-semibold uppercase text-gray-500 mt-0.5">Starost</p>
                                    </div>
                                    <!-- Stat 2: Spol -->
                                    <div class="p-4 bg-shelter-green-light rounded-lg text-center shadow-sm flex flex-col items-center justify-center">
                                        <span class="material-icons text-xl text-shelter-green-dark">${genderIcon}</span>
                                        <p class="text-xl font-bold mt-1 text-gray-900">${animal.gender}</p>
                                        <p class="text-xs font-semibold uppercase text-gray-500 mt-0.5">Spol</p>
                                    </div>
                                    <!-- Stat 3: Teža -->
                                    <div class="p-4 bg-shelter-green-light rounded-lg text-center shadow-sm flex flex-col items-center justify-center">
                                        <span class="material-icons text-xl text-shelter-green-dark">fitness_center</span>
                                        <p class="text-xl font-bold mt-1 text-gray-900">${animal.weight}</p>
                                        <p class="text-xs font-semibold uppercase text-gray-500 mt-0.5">Teža</p>
                                    </div>
                                    <!-- Stat 4: Barva -->
                                    <div class="p-4 bg-shelter-green-light rounded-lg text-center shadow-sm flex flex-col items-center justify-center">
                                        <span class="material-icons text-xl text-shelter-green-dark">palette</span>
                                        <p class="text-xl font-bold mt-1 text-gray-900">${animal.color}</p>
                                        <p class="text-xs font-semibold uppercase text-gray-500 mt-0.5">Barva</p>
                                    </div>
                                </div>

                                <!-- Zdravstveni Status -->
                                <h3 class="text-xl font-bold text-gray-900 mt-8 mb-4 border-b pb-2">Zdravstveni Status</h3>
                                <div class="flex flex-wrap gap-3">
                                    ${healthBadges}
                                </div>

                                <!-- O Živali -->
                                <h3 class="text-xl font-bold text-gray-900 mt-8 mb-4 border-b pb-2">O ${animal.name}u</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    ${animal.description}
                                </p>
                            </div>
                        </div>

                        <!-- DESNA KOLONA: Sidebar (1/3) -->
                        <div class="lg:col-span-1 mt-8 lg:mt-0 space-y-6">

                            <!-- Kartica: Deli Profil -->
                            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 text-center">Deli Profil</h3>
                                <div class="flex justify-center mb-4">
                                    <div class="w-40 h-40 bg-gray-100 border border-gray-300 flex items-center justify-center rounded-lg">
                                        <span class="material-icons text-4xl text-gray-400">qr_code_2</span>
                                    </div>
                                </div>
                                <p class="text-sm text-center text-gray-500">Skeniraj za ogled tega profila</p>
                            </div>

                            <!-- Kartica: Povpraševanje -->
                            <div class="bg-shelter-green-light p-6 rounded-xl shadow-lg border border-green-100">
                                <h3 class="text-lg font-bold text-gray-900 mb-3">Zanima me ${animal.name}?</h3>
                                <p class="text-sm text-gray-600 mb-5">
                                    Oddaj povpraševanje in naša ekipa ti bo odgovorila v 24 urah.
                                </p>
                                <button onclick="showAdoptionForm('${animal.name}')"
                                    class="w-full py-3 px-4 bg-shelter-green-dark text-white font-bold rounded-lg shadow-md hover:bg-shelter-green-dark/90 transition duration-300 transform hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-shelter-green-dark/50"
                                    ${animal.status !== 'Available' ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}>
                                    Oddaj Povpraševanje
                                </button>
                                ${animal.status !== 'Available' ? '<p class="text-center text-sm text-red-500 mt-2">Žival trenutno ni na voljo za posvojitev.</p>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Prikliče galerijo živali
        function renderGallery() {
            pageTitle.textContent = 'ShelterCompass - Poišči Svojega Popolnega Spremljevalca';
            
            // 1. Sekcija Hero / Iskanje
            let html = `
                <section class="hero">
                    <h1>Poišči Svojega Popolnega Spremljevalca</h1>
                    <p class="tagline">Vsaka žival si zasluži ljubeč dom. Prebrskajte po naših živalih in pomagajte spremeniti njihova življenja.</p>
                    
                    <div class="search-bar">
                        <span class="material-icons">search</span>
                        <input type="text" placeholder="Išči po imenu ali pasmi...">
                    </div>
                </section>
                
                <!-- 2. Sekcija Filtri -->
                <section class="filters-container">
                    <div class="filters-header">
                        <span class="material-icons">tune</span>
                        <h3>Filtri</h3>
                    </div>
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Tip</label>
                            <select><option>Vsi Tipi</option></select>
                        </div>
                        <div class="filter-group">
                            <label>Spol</label>
                            <select><option>Vsi Spoli</option></select>
                        </div>
                        <div class="filter-group">
                            <label>Starost</label>
                            <select><option>Vse Starosti</option></select>
                        </div>
                        <div class="filter-group">
                            <label>Status</label>
                            <select><option>Vsi Statusi</option></select>
                        </div>
                    </div>
                    <p class="showing-info">Prikazanih ${animals.length} živali</p>
                </section>
                
                <!-- 3. Sekcija Galerija Živali -->
                <section class="animal-gallery">
            `;

            // 4. Generiranje kartic iz JS polja
            animals.forEach(animal => {
                const status_class = animal.status.toLowerCase().replace(' ', '-');
                const icon_emoji = animal.status === 'Adopted' ? '🏡' : animal.species === 'Dog' ? '🐕' : '🐈';
                
                html += `
                    <div class='animal-card' onclick="showProfile(${animal.id})">
                        <div class='image-placeholder' style='background-image: url("${animal.image}")'>
                            <span class='status-badge ${status_class}'>${animal.status}</span>
                        </div>
                        <div class='card-details'>
                            <h4>${animal.name}</h4>
                            <p>${animal.breed}</p>
                            <p class='meta'>${animal.gender} • ${animal.age}</p>
                            <span class='animal-icon'>${icon_emoji}</span>
                        </div>
                    </div>
                `;
            });

            html += `</section>`;
            mainContent.innerHTML = html;
        }

        // --- Funkcije za preklapljanje pogleda in dogodke ---

        // Pokaži profil na podlagi ID-ja
        function showProfile(id) {
            const animal = animals.find(a => a.id === id);
            if (animal) {
                renderAnimalProfile(animal);
                // Skrij Nogo (Footer) samo v mobilnem pogledu, če želimo osredotočanje na vsebino profila
                document.querySelector('footer').style.display = 'block'; 
                window.scrollTo(0, 0); // Pomik na vrh strani
            }
        }

        // Vrni se v galerijo
        function showGallery() {
            renderGallery();
            document.querySelector('footer').style.display = 'block'; 
            window.scrollTo(0, 0); 
        }

        // Simulacija modalnega okna za povpraševanje
        function showAdoptionForm(animalName) {
            const container = document.createElement('div');
            container.innerHTML = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center p-4 z-50">
                    <div class="bg-white p-6 rounded-lg shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-100">
                        <h3 class="text-xl font-bold mb-4 text-shelter-green-dark">Povpraševanje za ${animalName}</h3>
                        <p class="text-gray-600 mb-4">Vaše zanimanje je zabeleženo. Ekipa vas bo kontaktirala glede nadaljnjih korakov.</p>
                        <input type="email" placeholder="Vaš E-mail" class="w-full p-2 border border-gray-300 rounded-md mb-4 focus:ring-shelter-green-dark focus:border-shelter-green-dark" />
                        <button onclick="closeModal(this.parentNode.parentNode)" class="w-full py-2 bg-shelter-green-dark text-white font-semibold rounded-lg hover:bg-shelter-green-dark/90 transition duration-200">
                            Pošlji in Zapri
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(container);
        }

        function closeModal(modalElement) {
            modalElement.remove();
        }

        // Začetek aplikacije
        window.onload = showGallery;
    </script>

</body>
</html>