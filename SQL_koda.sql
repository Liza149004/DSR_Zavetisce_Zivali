DROP DATABASE IF EXISTS Zavetisce_za_zivali;
CREATE DATABASE IF NOT EXISTS Zavetisce_za_zivali;

USE Zavetisce_za_zivali;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS Porocilo;
DROP TABLE IF EXISTS Povprasevanje;
DROP TABLE IF EXISTS Fotografija;
DROP TABLE IF EXISTS Uporabnik;
DROP TABLE IF EXISTS Zavetisce;
DROP TABLE IF EXISTS Zival;
DROP TABLE IF EXISTS Naslov;
DROP TABLE IF EXISTS Posta;
DROP TABLE IF EXISTS TipUporabnika;
DROP TABLE IF EXISTS Status;
DROP TABLE IF EXISTS Vrsta;

CREATE TABLE Posta (
    ID_posta INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    posta VARCHAR(4) NOT NULL,
    postnaSt INT NOT NULL
);

CREATE TABLE TipUporabnika (
    ID_tip_uporabnika INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(50) NOT NULL
);

CREATE TABLE Vrsta (
    ID_vrsta INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    imeVrste VARCHAR(50) NOT NULL
);

CREATE TABLE Status (
    ID_status INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    vrstaStatusa VARCHAR(100) NOT NULL
);

CREATE TABLE Naslov (
    ID_naslov INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ulica VARCHAR(45) NOT NULL,
    hisnaSt VARCHAR(4),
    TK_posta INT NOT NULL
);

CREATE TABLE Uporabnik (
    ID_uporabnik INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    priimek VARCHAR(450) NOT NULL,
    ime VARCHAR(450) NOT NULL,
    geslo VARCHAR(255),
    email VARCHAR(450) NOT NULL UNIQUE,
    TK_tip_uporabnika INT NOT NULL DEFAULT 1
);

CREATE TABLE Zival (
    ID_zival INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(450) NOT NULL,
    opis MEDIUMTEXT,
    starost VARCHAR(45),
    spol ENUM ('Samec', 'Samička') NOT NULL,
    barvaKozuha VARCHAR(100) NOT NULL,
    teza VARCHAR(45) NOT NULL,
    cepljen BOOLEAN,
    sterilizacija BOOLEAN,
    datumNajdbe DATE NOT NULL,
    datumRojstva DATE,
    TK_vrsta INT NOT NULL,
    TK_status INT NOT NULL,
    TK_zavetisce INT
);

CREATE TABLE Zavetisce (
    ID_zavetisce INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(450) NOT NULL,
    opis LONGTEXT,
    telefon VARCHAR(45),
    email VARCHAR(45),
    delovniCas MEDIUMTEXT,
	TK_naslov INT NOT NULL
);

CREATE TABLE Povprasevanje (
    ID_povprasevanje INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    datumOddaje DATE NOT NULL,
    statusPovprasevanja VARCHAR(20) DEFAULT 'v_cakanju',
    sporocilo TEXT,
    TK_uporabnik INT NOT NULL,
    TK_zival INT NOT NULL
);

CREATE TABLE Fotografija (
    ID_fotografija INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    potDoDatoteke VARCHAR(450) NOT NULL,
    TK_zival INT NOT NULL
);

CREATE TABLE Porocilo (
    ID_porocilo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tipPorocila ENUM('Mesečno', 'Letno', 'Dogodek') NOT NULL,
    datumGeneriranja DATE NOT NULL,
    datotekaPdf VARCHAR(450),
    TK_uporabnik INT NOT NULL,
    TK_zavetisce INT NOT NULL
);

SET FOREIGN_KEY_CHECKS=1;

ALTER TABLE Naslov ADD CONSTRAINT Naslov_Posta FOREIGN KEY(TK_posta) REFERENCES Posta(ID_posta);
ALTER TABLE Uporabnik ADD CONSTRAINT Uporabnik_TipUporabnika FOREIGN KEY(TK_tip_uporabnika) REFERENCES TipUporabnika(ID_tip_uporabnika);
ALTER TABLE Zavetisce ADD CONSTRAINT Zavetisce_Naslov FOREIGN KEY(TK_naslov) REFERENCES Naslov(ID_naslov);
ALTER TABLE Zival ADD CONSTRAINT Zival_Vrsta FOREIGN KEY(TK_vrsta) REFERENCES Vrsta(ID_vrsta);
ALTER TABLE Zival ADD CONSTRAINT Zival_Status FOREIGN KEY(TK_status) REFERENCES Status(ID_status);
ALTER TABLE Zival ADD CONSTRAINT Zival_Zavetisce FOREIGN KEY(TK_zavetisce) REFERENCES Zavetisce(ID_zavetisce); -- Dodan novi tuji ključ v Zival
ALTER TABLE Povprasevanje ADD CONSTRAINT Povprasevanje_Uporabnik FOREIGN KEY(TK_uporabnik) REFERENCES Uporabnik(ID_uporabnik);
ALTER TABLE Povprasevanje ADD CONSTRAINT Povprasevanje_Zival FOREIGN KEY(TK_zival) REFERENCES Zival(ID_zival);
ALTER TABLE Fotografija ADD CONSTRAINT Fotografija_Zival FOREIGN KEY(TK_zival) REFERENCES Zival(ID_zival);
ALTER TABLE Porocilo ADD CONSTRAINT Porocilo_Uporabnik FOREIGN KEY(TK_uporabnik) REFERENCES Uporabnik(ID_uporabnik);
ALTER TABLE Porocilo ADD CONSTRAINT Porocilo_Zavetisce FOREIGN KEY(TK_zavetisce) REFERENCES Zavetisce(ID_zavetisce);
ALTER TABLE Uporabnik ADD COLUMN two_fa_secret VARCHAR(32) NULL;

/*/////////////////////////////////////////////////////////////*/

INSERT INTO Posta (posta, postnaSt) VALUES 
('LJ', 1000); 

INSERT INTO Naslov (ulica, hisnaSt, TK_posta) VALUES 
('Cesta Pomoči', '12', 1);

INSERT INTO Zavetisce (ime, opis, telefon, email, delovniCas, TK_naslov) VALUES 
('Zavetišče ShelterCompass', 'Največje zavetišče v regiji, posvečeno reševanju in iskanju ljubečih domov za živali.', '+386 1 555 1234', 'info@sheltercompass.si', 'Ponedeljek - Petek: 9:00 - 16:00, Sobota: 9:00 - 12:00, Nedelja: Zaprto', 1);

INSERT INTO TipUporabnika (ID_tip_uporabnika, naziv) VALUES
(1, 'Obiskovalec'),
(2, 'Admin');

INSERT INTO Vrsta (imeVrste) VALUES 
('Pes'),
('Mačka'),
('Ostalo');

INSERT INTO Status (vrstaStatusa) VALUES 
('Aktiven'),
('Posvojen'),
('V oskrbi'),
('Rezerviran'),
('Neaktiven');

INSERT INTO Zival (ID_zival, ime, opis, starost, spol, barvaKozuha, teza, cepljen, sterilizacija, datumNajdbe, datumRojstva, TK_vrsta, TK_status, TK_zavetisce) VALUES
(1, 'Kimmy', 'Najboljša mačka na svetu!', '12 let', 'Samička', 'Želvovinasta', '4,2', 1, 1, '2011-09-02', '2011-07-01', 2, 5, 1),
(2, 'Nuage', 'Najboljši prijatelj objemom in spanju', '2 leti', 'Samec', 'Bel z lisami', '3,5', 0, 1, '2023-06-09', '2023-05-09', 2, 3, 1),
(3, 'Orage', 'Ko pride zima se spremeni v debelo kepo puha', '2 leti', 'Samec', 'Pikčasto/tigrasto sivo-rjav', '3,8', 0, 1, '2023-06-09', '2023-05-09', 2, 3, 1),
(4, 'Garfield', 'Ta pravi', '3 leta', 'Samec', 'Tigrasto oranžen', '4,3', 0, 1, '2023-05-03', '2022-06-10', 2, 1, 1),
(5, 'Blacky', 'Elegantna črna mačka, ki rada raziskuje okolico.', '1 leto', 'Samec', 'Črna', '4.5', 1, 1, '2024-02-10', '2021-05-15', 2, 1, 1),
(6, 'Kara', 'Zelo fotogenična in mirna muca.', '3 leta', 'Samička', 'Tigrasto siva', '3.9', 1, 1, '2024-03-20', '2022-04-10', 2, 1, 1),
(7, 'Milo', 'Mlad in igriv maček, ki obožuje igračke.', '1 leto', 'Samec', 'Oranžno-bel', '3.2', 1, 0, '2025-01-05', '2024-05-20', 2, 1, 1),
(8, 'Moustache', 'Gospod z rdečo pentljo in izjemnim karakterjem.', '3 leta', 'Samec', 'Tigrast', '4.8', 1, 1, '2023-11-12', '2020-08-01', 2, 1, 1),
(9, 'Olaf', 'Zvest zlati prinašalec, ki se vedno smeji.', '10 let', 'Samec', 'Zlata', '28.0', 1, 1, '2024-06-15', '2016-12-01', 1, 1, 1),
(10, 'Betty', 'Ljubka in radovedna. Alos, "dog"', '5 mesecev', 'Samička', 'Bela s črnimi lisami', '25.0', 1, 1, '2024-08-20', '2023-01-10', 3, 1, 1),
(11, 'Bone', 'Majhen raziskovalec, ki je vedno poln energije.', '2 meseca', 'Samec', 'Rjavo-bel', '10.5', 1, 0, '2025-02-01', '2024-07-15', 1, 1, 1),
(12, 'Luna', 'Nežna duša, ki išče miren dom.', '13 let', 'Samička', 'Črno-bela', '12.0', 1, 1, '2024-10-05', '2023-04-12', 1, 1, 1),
(13, 'Raf', 'Velik ljubitelj nogometa, ki nikoli ne spusti svoje žoge.', '11 leta', 'Samec', 'Črn z belo liso', '22.0', 1, 1, '2024-05-10', '2015-04-15', 1, 1, 1),
(14, 'Tačko', 'Mlad raziskovalec, ki najraje počiva na toplem betonu.', '4 mesece', 'Samec', 'Tigrasto siv', '2.5', 0, 0, '2025-11-20', '2025-09-10', 2, 1, 1),
(15, 'Nona', 'Starejša in mirna gospa, ki uživa v tišini in dobrem obroku.', '10 let', 'Samička', 'Tigrasta', '3.8', 1, 1, '2025-05-15', '2015-06-01', 2, 1, 1);

INSERT INTO Fotografija (ID_fotografija, potDoDatoteke, TK_zival) VALUES
(1, 'slike/Kimmy_1.jpg', 1),
(2, 'slike/Nuage_1.jpg', 2),
(3, 'slike/Orage_1.jpg', 3),
(4, 'slike/Garfield_1.jpg', 4),
(5, 'slike/Kimmy_2.jpg', 1),
(6, 'slike/Nuage_2.jpg', 2),
(7, 'slike/Orage_2.jpg', 3),
(8, 'slike/Garfield_2.jpg', 4),
(9, 'slike/Orage_3.jpg', 3),
(10, 'slike/Nuage_3.jpg', 2),
(11, 'slike/Blacky_1.jpg', 5),
(12, 'slike/Blacky_2.jpg', 5),
(13, 'slike/Blacky_3.jpg', 5),
(14, 'slike/Kara_1.jpg', 6),
(15, 'slike/Kara_2.jpg', 6),
(16, 'slike/Kara_3.jpg', 6),
(17, 'slike/Milo_1.jpg', 7),
(18, 'slike/Milo_2.jpg', 7),
(19, 'slike/Milo_3.jpg', 7),
(20, 'slike/Moustache_1.jpg', 8),
(21, 'slike/Moustache_2.jpg', 8),
(22, 'slike/Moustache_3.jpg', 8),
(23, 'slike/Olaf_1.jpg', 9),
(24, 'slike/Olaf_2.jpg', 9),
(25, 'slike/Olaf_3.jpg', 9),
(26, 'slike/Betty_1.jpg', 10),
(27, 'slike/Bone_1.jpg', 11),
(28, 'slike/Bone_2.jpg', 11),
(29, 'slike/Luna_1.jpg', 12),
(30, 'slike/Raf_1.jpg', 13),
(31, 'slike/Raf_2.jpg', 13),
(32, 'slike/Raf_3.jpg', 13),
(33, 'slike/Raf_4.jpg', 13),
(34, 'slike/Tačko_1.jpg', 14),
(35, 'slike/Tačko_2.jpg', 14),
(36, 'slike/Tačko_3.jpg', 14),
(37, 'slike/Tačko_4.jpg', 14),
(38, 'slike/Tačko_5.jpg', 14),
(39, 'slike/Nona_1.jpg', 15);

INSERT INTO Uporabnik (ime, priimek, email, geslo, TK_tip_uporabnika) 
VALUES (
    'Liza', 
    'Admin', 
    'info.sheltercompass@gmail.com', 
    '$2y$12$khQ/bV2FteS6mSq.LBrAwOOiqbe6l7R4h.t82k8bRy0NNHd.Dy3sG',
    2
);
