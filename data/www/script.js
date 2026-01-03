/* ShelterCompass - Centralna JavaScript datoteka
  Upravlja Dark Mode, Slider slik in vse modalne okne (User & Admin) */

document.addEventListener('DOMContentLoaded', () => {
    //DARK MODE
    const toggleButton = document.getElementById('darkModeToggle');
    const body = document.body;

    const applyDarkMode = (isDark) => {
        if (isDark) {
            body.classList.add('dark-mode');
            if (toggleButton) toggleButton.textContent = 'light_mode';
        } else {
            body.classList.remove('dark-mode');
            if (toggleButton) toggleButton.textContent = 'dark_mode';
        }
    };
    //preveri ob nalaganju
    const savedMode = localStorage.getItem('darkMode') === 'enabled';
    applyDarkMode(savedMode);

    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            const isDark = body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            // Nastavimo piškotek še za PHP (Admin panel)
            document.cookie = "darkMode=" + (isDark ? 'enabled' : 'disabled') + ";path=/";
            toggleButton.textContent = isDark ? 'light_mode' : 'dark_mode';
        });
    }

    //UNIVERZALNA LOGIKA ZA MODALE/POP UP OKNA
    //splošna funkcija za zapiranje vseh modalov
    window.closeModal = () => {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(m => m.style.display = 'none');
    };
    window.zapriModal = window.closeModal;
    //zapiranje s tipko Esc ali klikom zunaj modala
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) window.closeModal();
    });
    window.addEventListener('keydown', (e) => {
        if (e.key === "Escape") window.closeModal();
    });
    //A: Modal za oddajo pvpraševanja (Uporabnik)
    window.openModal = () => {
        const modal = document.getElementById('inquiryModal');
        if (modal) modal.style.display = 'block';
    };
    //B: Modal za sprejemanje in termine (Admin index)
    let trenutniPovpId = null;
    window.odpriModal = (id) => {
        trenutniPovpId = id;
        const modal = document.getElementById('terminModal');
        const input = document.getElementById('inputTermin');
        if (modal) {
            modal.style.display = 'block';
            if (input) {
                let jutri = new Date();
                jutri.setDate(jutri.getDate() + 1);
                jutri.setHours(10, 0, 0, 0);
                input.value = jutri.toISOString().substring(0, 16);
            }
        }
    };

    window.potrdiSprejem = () => {
        const termin = document.getElementById('inputTermin').value;
        if (termin && trenutniPovpId) {
            window.location.href = "admin_index.php?akcija=sprejmi&id_povp=" + trenutniPovpId + "&termin=" + encodeURIComponent(termin);
        } else {
            alert("Prosim, izberite datum in uro!");
        }
    };
    //C: Modal za urejanje živali (Admin seznam)
    window.openEditModal = (zival) => {
        const modal = document.getElementById('editModal');
        if (!modal) return;
        //napolnimo polja s podatki
        document.getElementById('edit_id').value = zival.ID_zival;
        document.getElementById('edit_ime').value = zival.ime;
        document.getElementById('modalTitle').innerText = "Urejanje: " + zival.ime;
        document.getElementById('edit_opis').value = zival.opis;
        document.getElementById('edit_starost').value = zival.starost;
        document.getElementById('edit_teza').value = zival.teza;
        document.getElementById('edit_vrsta').value = zival.TK_vrsta;
        document.getElementById('edit_status').value = zival.TK_status;
        
        modal.style.display = 'block';
    };

    //SLIDER SLIK
    const imageTrack = document.getElementById('imageTrack');
    const currentIdxDisplay = document.getElementById('currentIdx');
    
    if (imageTrack) {
        let currentIdx = 0;
        const totalImages = imageTrack.children.length;

        window.moveSlide = (direction) => {
            currentIdx += direction;
            if (currentIdx >= totalImages) currentIdx = 0;
            else if (currentIdx < 0) currentIdx = totalImages - 1;

            imageTrack.style.transform = `translateX(${currentIdx * -100}%)`;
            if (currentIdxDisplay) currentIdxDisplay.textContent = currentIdx + 1;
        };
    }
});