<?php
    session_start();
    require 'db_connect.php';
    //filtre za spustne sezname
    $vrste_zivali = $pdo->query("SELECT ID_vrsta, imeVrste FROM Vrsta")->fetchAll();
    $statusi_zivali = $pdo->query("SELECT ID_status, vrstaStatusa FROM Status ORDER BY vrstaStatusa")->fetchAll();
?>
<!DOCTYPE html>
<html lang="sl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ShelterCompass - Poišči svojega popolnega spremljevalca</title>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    </head>
        <body>

            <?php include 'header.php'; ?>

            <main id="app">
                <section class="hero">
                    <h1>Poišči svojega popolnega spremljevalca!</h1>
                    <p>Iščite po imenu, barvi kožuha ali vrsti živali.</p>
                    
                    <div class="search-form">
                        <div class="search-bar">
                            <span class="material-icons">search</span>
                            <input type="text" v-model="searchQuery" placeholder="Npr. ime, barva kožuha ali vrsta...">
                        </div>
                    </div>
                </section>

                <section class="filters-container">
                    <div class="filters-header">
                        <span class="material-icons">tune</span>
                        <h3>Filtri</h3>
                        <button @click="resetFilters" class="reset-btn" v-if="hasFilters" style="cursor: pointer; border: none; background: none; color: #ff6b6b; font-weight: bold;">
                            Počisti vse
                        </button>
                    </div>
                    
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Vrsta</label>
                            <select v-model="filterVrsta">
                                <option value="">Vse vrste</option>
                                <?php foreach ($vrste_zivali as $v): ?>
                                    <option value="<?= htmlspecialchars($v['imeVrste']) ?>"><?= htmlspecialchars($v['imeVrste']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Spol</label>
                            <select v-model="filterSpol">
                                <option value="">Vsi spoli</option>
                                <option value="Samec">Samec</option>
                                <option value="Samička">Samička</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Starost</label>
                            <select v-model="filterStarost">
                                <option value="">Vse starosti</option>
                                <option value="under1">Pod 1 leto</option>
                                <option value="1-2">1 - 2 leti</option>
                                <option value="2-3">2 - 3 leta</option>
                                <option value="3-6">3 - 6 let</option>
                                <option value="6-10">6 - 10 let</option>
                                <option value="10plus">Nad 10 let</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Status</label>
                            <select v-model="filterStatus">
                                <option value="">Vsi statusi</option>
                                <?php foreach ($statusi_zivali as $st): ?>
                                    <option value="<?= htmlspecialchars($st['vrstaStatusa']) ?>"><?= htmlspecialchars($st['vrstaStatusa']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <p class="showing-info">Prikazanih {{ filteredAnimals.length }} živali</p>
                </section>
                    
                <section class="animal-gallery" v-if="!loading">
                    <a v-for="animal in filteredAnimals" :key="animal.ID_zival" :href="'profil_zivali.php?id=' + animal.ID_zival" class="animal-card-link">
                        <div class="animal-card">
                            <div class="image-placeholder" :style="{ backgroundImage: 'url(' + (animal.pot_do_slike || 'images/placeholder.jpg') + ')' }">
                                <span :class="['status-badge', getStatusClass(animal.status)]">{{ animal.status || 'V oskrbi' }}</span>
                            </div>
                            <div class="card-details">
                                <h4>{{ animal.ime_zivali }}</h4>
                                <p>{{ animal.vrsta }} • {{ animal.barvaKozuha }}</p>
                                <p class="meta">Starost: {{ animal.starost }} let</p>
                                <span class="animal-icon">{{ getStatusIcon(animal.status) }}</span>
                            </div>
                        </div>
                    </a>

                    <div v-if="filteredAnimals.length === 0" class="no-results" style="width: 100%; text-align: center; padding: 50px;">
                        <p>Žal ni zadetkov, ki bi ustrezali vašim kriterijem.</p>
                    </div>
                </section>

                <div v-else style="text-align: center; padding: 50px;">
                    Nalaganje živali...
                </div>
            </main>
                    
            <?php include 'footer.php'; ?>

            <script>
                const { createApp } = Vue;

                createApp({
                    data() {
                        return {
                            animals: [],
                            searchQuery: '',
                            filterVrsta: '',
                            filterSpol: '',
                            filterStatus: '',
                            filterStarost: '',
                            loading: true
                        }
                    },
                    computed: {
                        hasFilters() {
                            return this.searchQuery || this.filterVrsta || this.filterSpol || this.filterStatus || this.filterStarost;
                        },
                        filteredAnimals() {
                            return this.animals.filter(animal => {
                                // Iskanje po besedilu
                                const s = this.searchQuery.toLowerCase();
                                const matchesSearch = !s || 
                                                    animal.ime_zivali.toLowerCase().includes(s) || 
                                                    animal.barvaKozuha.toLowerCase().includes(s) || 
                                                    animal.vrsta.toLowerCase().includes(s);
                                
                                // Osnovni filtri
                                const matchesVrsta = !this.filterVrsta || animal.vrsta === this.filterVrsta;
                                const matchesSpol = !this.filterSpol || animal.spol === this.filterSpol;
                                const matchesStatus = !this.filterStatus || animal.status === this.filterStatus;

                                // Starostni rang
                                let matchesStarost = true;
                                if (this.filterStarost) {
                                    const age = parseFloat(animal.starost);
                                    switch (this.filterStarost) {
                                        case 'under1': matchesStarost = age < 1; break;
                                        case '1-2':    matchesStarost = age >= 1 && age <= 2; break;
                                        case '2-3':    matchesStarost = age > 2 && age <= 3; break;
                                        case '3-6':    matchesStarost = age > 3 && age <= 6; break;
                                        case '6-10':   matchesStarost = age > 6 && age <= 10; break;
                                        case '10plus': matchesStarost = age > 10; break;
                                    }
                                }

                                return matchesSearch && matchesVrsta && matchesSpol && matchesStatus && matchesStarost;
                            });
                        }
                    },
                    methods: {
                        resetFilters() {
                            this.searchQuery = '';
                            this.filterVrsta = '';
                            this.filterSpol = '';
                            this.filterStatus = '';
                            this.filterStarost = '';
                        },
                        getStatusClass(status) {
                            if (!status) return 'in-care';
                            const s = status.toLowerCase();
                            if (s === 'aktiven') return 'available';
                            if (s === 'posvojen') return 'adopted';
                            if (s === 'rezerviran') return 'reserved';
                            if (s === 'neaktiven') return 'inactive';
                            return 'in-care';
                        },
                        getStatusIcon(status) {
                            if (!status) return 'ℹ️';
                            const s = status.toLowerCase();
                            if (s === 'aktiven') return '🐾';
                            if (s === 'posvojen') return '🏡';
                            if (s === 'rezerviran') return '🔒';
                            if (s === 'neaktiven') return '🚫';
                            return 'ℹ️';
                        },
                        async fetchAnimals() {
                            try {
                                const response = await fetch('api_zivali.php');
                                const data = await response.json();
                                this.animals = data;
                            } catch (error) {
                                console.error("Napaka pri pridobivanju podatkov:", error);
                            } finally {
                                this.loading = false;
                            }
                        }
                    },
                    mounted() {
                        this.fetchAnimals();
                    }
                }).mount('#app');
            </script>
            <script src="script.js"></script>
        </body>
</html>