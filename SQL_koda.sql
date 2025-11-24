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
    vrstaStatusa ENUM('Aktiven', 'Neaktiven', 'Arhiviran', 'Potrjen', 'Zavrnjena', 'V_obravnavi') NOT NULL
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
    geslo VARCHAR(255) NOT NULL,
    email VARCHAR(450) NOT NULL,
    datumRegistracije DATE NOT NULL,
    TK_tip_uporabnika INT NOT NULL,
    TK_naslov INT NOT NULL
);

CREATE TABLE Zival (
    ID_zival INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(450) NOT NULL,
    opis MEDIUMTEXT,
    starost VARCHAR(45),
    datumNajdbe DATE,
    datumRojstva DATE,
    TK_vrsta INT NOT NULL,
    TK_status INT NOT NULL,
    TK_zavetisce INT -- Dodan novi tuji ključ za Zavetisce
);

CREATE TABLE Zavetisce (
    ID_zavetisce INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(450) NOT NULL,
    opis LONGTEXT,
    telefon VARCHAR(45),
    email VARCHAR(45),
    delovniCas MEDIUMTEXT
    -- Odstranjen je TK_zival
);

CREATE TABLE Povprasevanje (
    ID_povprasevanje INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    datumOddaje DATE NOT NULL,
    statusPovprasevanja VARCHAR(4),
    obrazecPdf VARCHAR(45),
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
ALTER TABLE Uporabnik ADD CONSTRAINT Uporabnik_Naslov FOREIGN KEY(TK_naslov) REFERENCES Naslov(ID_naslov);
ALTER TABLE Zival ADD CONSTRAINT Zival_Vrsta FOREIGN KEY(TK_vrsta) REFERENCES Vrsta(ID_vrsta);
ALTER TABLE Zival ADD CONSTRAINT Zival_Status FOREIGN KEY(TK_status) REFERENCES Status(ID_status);
ALTER TABLE Zival ADD CONSTRAINT Zival_Zavetisce FOREIGN KEY(TK_zavetisce) REFERENCES Zavetisce(ID_zavetisce); -- Dodan novi tuji ključ v Zival
ALTER TABLE Povprasevanje ADD CONSTRAINT Povprasevanje_Uporabnik FOREIGN KEY(TK_uporabnik) REFERENCES Uporabnik(ID_uporabnik);
ALTER TABLE Povprasevanje ADD CONSTRAINT Povprasevanje_Zival FOREIGN KEY(TK_zival) REFERENCES Zival(ID_zival);
ALTER TABLE Fotografija ADD CONSTRAINT Fotografija_Zival FOREIGN KEY(TK_zival) REFERENCES Zival(ID_zival);
ALTER TABLE Porocilo ADD CONSTRAINT Porocilo_Uporabnik FOREIGN KEY(TK_uporabnik) REFERENCES Uporabnik(ID_uporabnik);
ALTER TABLE Porocilo ADD CONSTRAINT Porocilo_Zavetisce FOREIGN KEY(TK_zavetisce) REFERENCES Zavetisce(ID_zavetisce);