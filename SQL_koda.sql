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
(3, 'Orage', 'Ko pride zima se spremeni v debelo kepo puha', '2 leti', 'Samec', 'Pikčasto/tigrasto sivorjav', '3,8', 0, 1, '2023-06-09', '2023-05-09', 2, 3, 1),
(4, 'Garfield', 'Ta pravi', '3 leta', 'Samec', 'Tigrasto oranžen', '4,3', 0, 1, '2023-05-03', '2022-06-10', 2, 1, 1);

INSERT INTO Fotografija (ID_fotografija, potDoDatoteke, TK_zival) VALUES
(1, 'slike/poskus 3.jpg', 1),
(2, 'slike/20240705_192428.jpg', 2),
(3, 'slike/20240627_182602.jpg', 3),
(4, 'slike/20240131_144102.jpg', 4);

INSERT INTO Uporabnik (ime, priimek, email, geslo, TK_tip_uporabnika) 
VALUES (
    'Liza', 
    'Admin', 
    'info.sheltercompass@gmail.com', 
    '$2y$12$khQ/bV2FteS6mSq.LBrAwOOiqbe6l7R4h.t82k8bRy0NNHd.Dy3sG', -- To je haširano 'admin123'
    2
);
