V 0.4.3.0 (22.7.2023.)
    - 'Osnovna licenca' ukinuta kao i sva funkcijonalnost vezana za nju. Dodavanje, brisanje licence terminalu. 
    - Brisanje licence na stranici 'Distributer terminal' brise licencu i u apiju

V 0.4.3.1 (28.7.2023.)
    - Zamenjena ikona 'Add to Blacklist' na stranici Terminali (Menager Licenci)
    - Napravljena funkcionalnost za brisanje korisnika (admin). Obrisanom korisniku se dodeljuje "Pozicija" - Obrisan i ostaje vidljiv na lisi korisnika bez mogućnosti editovanja. Vraćanje korisnika samo editom polja "pozicija_tipId" u bazi.

V 0.4.3.2 (11.8.2023.)
    - Dodat podatak o modelu i proizvodjaču na modalu HISTORY na stranicama: "licenca-terminali", "terminal", "tiketview"

V 0.4.3.3 (12.10.2023.)
    - Ispravljen bug kod dodavanja tiketa. Lista "Opis kvara" vis ne zavisi od tipa terminala. 

V 0.5.0 (23.12.2013.)
    - Nova tabela u bazi: distributer_user_indices
    - Dodat novi tip usera "Distributer" i napravljena funkcionalonost za Admina da može da dodaje takve korisnike. Novog korisnika vezuje za lokaciju tipa "Distributer" koja je povezana sa tabelom "licenca_distributer_tips".

V 0.5.1 (17.2.2024.)
    - Nova polja u tabelama "terminal_lokacijas", "terminal_lokacija_histories":  'distributerId'
        Stranice na kojima serviser vidi distributera:
            - Teminal -> "Terminal history" modal
            - Teminal -> "Novi tiket" modal
            - Tiket -> "Novi tiket" modal
            - Tiketview stranica

    - Nova tabela "distributer_lokacija_indices" veza izmedju tabela "licenca_distributer_tips" i "lokacijas"
    - Nova stranica "Distributer-lokacija" preko koje Menagerlicenci vezije distridutere za lokacije

    - Nova polja u tabeli "Lokacijas": 'mb', 'distributerId'
    - Nova polja u tabeli "licenca_distributer_tips": 'distributer_tr', 'distributer_banka', 'distributer_tel'

    - Nove stranice za ulogu "Distributer": Dashboard, Terminali, Lokacije, Licence
    - Promenjen naziv polja u tabeli "licenca_distributer_cenas": 'licenca_cena' u 'licenca_zeta_cena'
    - Dodato novo polje u tabelu "licenca_distributer_cenas": 'licenca_dist_cena'

    - Nova polja u tabeli "licenca_naplatas": 'datum_zaduzenja', 'dist_zaduzeno', 'dist_datum_zaduzenja', 'dist_razduzeno', 'dist_datum_razduzenja'
    
    - Kreiranje novog Usera dodata password rules (min(8)->letters()->numbers()->symbols())

    - Nova tabela "kurs_evras"

V 0.5.0.2  (17.2.2024.)
    - Ispravljen bug na stranici user.php prilikom poziva modala updateShowModal($id)

V 0.5.0.3  (18.2.2024.)
    - Ispravljen prikaz Terminal History na stranici Terminali za Menadžera licenci. Sada vidi naziv distributera kod koga je terminal.

V 0.5.0.5  (18.2.2024.)
    - Dodata funkcionalnost ya testUsera da moze da mu se menja Distributer

V 0.5.0.6 (19.2.2024.)
    - Update CSS i JS
    - Menager licenci ne vidi dugmice za dodavanja, brisanje i parametre licence
    - Menager licenci vidi sve terminale prebacene distributeru.

V 0.5.0.7 (25.2.2024.)
    - Hardkodovanom useru "Zeta test user" - tipa distributer dodat modal za promenu distributera na dashboard stranici
    - Menadger Licenci kada dodaje licencu distributeru unosi i preporucenu cenu za distributera
    - Menadžer Licenci na stranici zaduzenje-distributeri ne vidi dugme "ZADUŽI" ako je već zadužio distributera za taj mesec

V 0.5.0.8 (25.2.2024.)
    - Ispravka par slovnih greski i prikay ikonice "Nenaplativa licenca" na nalogu Distributera

V 0.5.0.9 (26.2.2024.)
    - Ispravljen BUG za dodavanje postojece lokacije distributeru.

V 0.5.1.0 (26.2.2024.)
    - spravljen bug sa paginatorom na stranici dist-terminali
    - preimenovan blade i controler distributer.dist-terminal (bio distributer.distributer-terminal)

V 0.5.1.1 (27.2.2024.)
    - Doato polje email u tabelu lokacije. Vidljivo u modalu edit i info. Vidljivo i na stranici "Tiketview"
    - Dodata pretraga tiketa po opisu kvara
    - Dodat zarez kao delimiter za decimalne brojeve prilikom unosa cene licence za ulogu "Distributer"

V 0.5.1.2 (6.3.2024.)
    - Dodata provera email adrese kada se menja ili dodaje da setuje NULL ako je los format

V 0.5.1.3 (6.4.2024.)
    - Ispravljen bug sa editom lokacije koja ima dodatu email adresu. Stranice "Lokacije" i "Dist-Lokacije"


V 0.5.1.4 (23.4.2024.)
    - Dodata funkcionalnost da Admin i Menager licenci ne mogu da premestaju terminale koji imaju dodatu licencu.
    - Dodata dva nova plja u tabelu "licenca_naplatas": 'aktivna', 'nenaplativ'
    
    - Izbacena tabela 'licenca_distributer_terminals' iz baze

        UPDATE licenca_naplatas ls, ( SELECT id, nenaplativ FROM licenca_distributer_terminals ) ldt
        SET ls.nenaplativ = 1
        WHERE ls.licenca_dist_terminalId = ldt.id
        AND ldt.nenaplativ = 1;

    - Dodata nova polja u tabelu "licenca_parametar_terminals" : 'terminal_lokacijaId', 'distributerId', 'licenca_distributer_cenaId'
    - Refaktor prememestanja terminala u jednu funkciju u modelu TerminalLokacija
    - Dodat update broja terminala za distributera prilikom premestanja terminala

V 0.5.1.5 (2.6.2024.)
    - Dodat novi modal "Pregled licenci" na stranici 'Terminali' za uloge 'admin' i 'menadzer licenci'

V 0.5.2.1 (2.6.2024.)
    - Ispravljen bug u blade fajlovima 'Terminali' za uloge 'admin' i 'menadzer licenci'

V 1.0.0.0 (16.11.2024.)
    - Prvi komit u branch Servisna licenca na novom repostriju Terminal-App-Dev sa Asus Kompa- u novom okruzenju

V 1.0.0.2 (18.11.2024.)
    -Dodata funkcija BLACKLIST distributerima - branch Black-lista-distributer

V 1.0.0.1 (18.11.2024.)
    - Komit na branch "Servisna licenca" 
        - dodata tabela "licenca_servisnas" gde se loguju servisne licence
        - Dodato polje u tabelu "licence_za_terminals" - "licenca_poreklo"

V 1.0.0.3 (19.11.2024.)
V 1.0.0.4 (19.11.2024.)
V 1.0.0.5 (19.11.2024.)
    - Vendor folder included in git...
    - composer updated na php 8.1 (neuspesno)
    - novi composer updete sa dependecima na 8.1 (uspesno)

V 1.0.0.7 (26.12.2024.)
    - Dodata nova tabela u bazu "licenca_servisnas" loguje servisne licence
    - Dodato polje u tabelu "licence_za_terminals" >> "licenca_poreklo"->default(1) zastavica za poreklo licence
    - Zavresena Servisna licenca. 
    - Refaktorovane funkcije na stranici DistLicence a manipulacija parametima apstrahovana u model LicencaParametarTerminal
    - Deploj na barnch Servisna-licenca-sa-novim-composerom. 
    - Odradio greskom composer update pa ima 1K fajlova za depliy

V 1.0.0.8 (26.12.2024.)
    - Promenjen cpanel.yml da bi deploj isao na dev adresu

V 1.0.0.9 (17.1.2025.)
    - Ispravljen bug na sevisnoj licenci, "Datum_prekoracenja" je bio pogresan u nizu za signature
    - Dodat "temp" parametar za sve servisne licence. Hardcoded u LicencaControler.php

V 1.0.1.2 (20.1.2025.)
    -Ispravljen bug sa parametrima kod produzenja licence za nalog distributera

V 1.0.1.3 (24.1.2025.)
    - Dodat Cron job koji brise servisne licence 3 dana posle isteka

V 1.0.1.4 (28.1.2025.)
    - Vracen serach na modal 'premesti terminal' za ulogu "distributer"

V 1.0.1.5 (30.1.2025.)
    - Menadzer licenci u pregledu terminala vidi "Servisne licence"

V 1.0.1.6 (4.2.2025.)
    - Dodat Error modal na stranici Dist-licenca za slucaj da se pokusa dodavanje licence na terminal za distributera koji nema dodeljene licence.

V 1.0.1.7 (10.2.2025.)
    - Dodata funkcija da se brise "Servisna licenca" kada se premesta terminal na stranici "Terminali"

V 1.0.1.8 (10.2.2025.)
    - Dodata funkcija da se brise "Servisna licenca" kada se premesta terminal na stranici "Lokacije"
    - Izdvojena logika brisanja servisnih licenci i parametara u clasu Ivan/SelectedTerminalInfo

V 1.0.1.9 (23.2.2025.)
    - Menadzeru licenci dodata opcija dodavanja novog terminala na stranci "Terminali". Terminale dodaje na fixnu lokaciju "Centralni servis" 
    
V 1.0.2.0 (27.2.2025.) @stanje-terminala branch
    - LARAVEL UPDATE 10.48.28 0 prelazak na noviu verziju 3k fajlova update - branch "Stanje-terminala-po-modelu"

V 1.0.2.5 (17.3.2025.) @stanje-terminala branch
    - Dodata funkcija za brisanje distributera
    - Dodata stranica "Terminali stanje" za Admina 

V 1.0.2.6 (26.3.2025.) @stanje-terminala branch
    - Dodata stranica "Licence grafika" za Menadzera licenci
    - Update dodavanje i produzetak licenci dodat update u polju "licenca_poreklo" za sva tri tipa licence
    - Izmenjen LicencaControler za API sada prikayur dva nova noda "tip" i "datum_trajne"

V 1.0.2.7 (29.3.2025.) @stanje-terminala branch
    - Grafici odvojeni u posebne fajlove
    - Dodat grafik za "Distributera" na pocetnoj stranici

v 1.0.3.0 (29.3.2025.) @Dodatna-oprema
    - Composer update PHP 8.2

V 1.0.3.1 (2.4.2025.)
    - ispravljen prikaz "privremene" licence na stranicama "terminali" i "Distributer-terminali"

V 1.0.3.2 (10.4.2025.) @stanje-terminala branch
    - Pomeren kraj isteka servisne licence sa wikenda na ponedeljak
    - Servisna licenca moze da se doda preko istekle trajne licence 
    - Dodata funkcionalnos za produzetak privremene licence 
 
V 1.0.3.2 (16.4.2025.) @stanje-terminala branch
    - Dodata opcija Export Excel za izvoz licenci koje su istekle ili uskoro isticu ya uloge Menadzer licenci i Distributer

V 1.0.3.4 (15.5.2025.) @main
    - Dodata opcija "Obrisi sve licence" ya Menadzera licenci

V 1.0.3.8 (21.5.2025.) @Statistika-licenci-prodaja
    - Statistika licenci za menagment dodate stranice:
        managment-distributeri (lista distributera)
            -- komponenta "komponente.sort-button" - sortiranje sa eventom
        managment-distributer-licence (grafik licenci prema datumu pocetka sa prikazom pojedinacnih na klik)
            -- komponenta "managment.pocetak-licence-grafik"  - grafik 
            -- komponenta "managment.prikaz-izabranih-licenci"  - Tabela licenci posle klika na grafik        

 V 1.0.3.9 (24.5.2025.) @Statistika-licenci-prodaja  
    - Dodati check boxovi (toogle) za prikay kategorija na grafiku

V 1.0.3.7 (21.5.2025.) @main
    - Ispravljen bug na stranici "ZaduzenjeDistributerMesec" gde je zaduzenje bilo 0

V 1.0.3.7.2 (20.6.2025.) @main
    - Dodtata tabela `licenca_sign_logs` logovanje potpisa u clasi "CryptoSign"

V 1.0.3.9 () @Statistika-licenci-prodaja  
    - Izmena u bazi "licenca_naplatas" dodata polja: 'nova_licenca', 'terminal_sn', 'licenca_naziv'
    - Refaktor koda na svoim stranicama za statistiku licenci za menagment

V 1.0.3.11 (23.6.2025.) @main merged @Statistika-licenci-prodaja

V 1.0.4 (1.7.2025.) @mapa-terminala-distibutera
    - Izmenjene stranice "Lokacije" (za Admina i Menadgera licenci) i stranica "distributer-tremina" dodata funkcija ya dodavanje koordinata
    - Za Admina dodata stranica "managment-distributer-mapa" na kojoj se na goole mapi prikazuju terminali

V 1.0.4.1 (20.7.2025.) @mapa-terminala-distibutera
    - Dodata mapa svih distributera sa pinovima koji pokayuju brog novih licenci prema kriterijumu iz filtera

V 1.0.4.3.1 (21.7.2025.) @mapa-terminala-distibutera
    - Promenjen izgled pinova (nijanse crvene boje) sa brojevima od 1 do 15+ sivi pinovi ya 0 i zuti za nove distributere sa 0 licenci

V 1.0.4.4  (21.7.2025.) @ Termina-kutija-komentar-lokacija-duplikat 
    - Promenjen Cron job za brisanje servisnih licenci... sada brise dan posle isteka prekoracenja...
    - Dodati komentari na terminal za Menagra licenci

V 1.0.4.5  (21.7.2025.) @ Termina-kutija-komentar-lokacija-duplikat 
    - komentari na terminalu zavrseni za sve uloge. 
        Menager licenci (add i edit) stranice: Terminali i distributer-terminali
        Admin, serviser, kol centar ( view ) stranice: Terminali i ticket-view

V 1.0.4.6  (25.7.2025.) @ Termina-kutija-komentar-lokacija-duplikat
    - Dodata funkcija Adminu da može da uključi ili isključi komentare na terminalu za Distributera
    

//TODO Mora update baze na serveru pa merge request na git-u. Pa deply na main...
        posle toga moze da se obrise polje "licenca_dit_terminalId" u tabeli "licenca_naplatas" na serveru


    // licenca koja postoji u tabeli 'licenca_naplatas' a ne postoji u tabeli 'licence_za_terminals'
    SELECT * FROM licenca_naplatas ln WHERE (ln.terminal_lokacijaId, ln.distributerId, ln.licenca_distributer_cenaId) NOT IN (SELECT lzt.terminal_lokacijaId, lzt.distributerId, lzt.licenca_distributer_cenaId FROM licence_za_terminals lzt) AND ln.aktivna = 1 ORDER BY `distributerId` ASC 


    // INFO o TerminalLokacija IDju
    SELECT * FROM terminal_lokacijas tl WHERE tl.terminalId = (SELECT id FROM terminals WHERE sn LIKE '0500422040186607'); 
