#Fonoteka RŠ - specifikacija aplikacije

##Faza 1: Prestrukturiranje baze podatkov

###Nova struktura podatkov

Nova struktura je relacijska in normalizirana. Omogoča povezave med založbami, izvajalci, albumi in skladbami. Po novem bodo mogoče tudi povezave:
 * izvajalec - izvajalec: omogoča predstavitev različnih relacij med izvajalci, npr. različne zapise imena istega izvajalca, uradna preimenovanja itd. 
 * skladba - izvajalec: tudi po več izvajalcev na skladbo, npr. featuringi.

Z novo strukturo je mogoče bolj natančno opisati vsebino na nosilcih in je bolj fleksibilna za bodoče razširitve in spremembe.

###Stari podatki in migracija

Stari podatki so v slabem stanju. Tipkarske napake, nepoenoteni zapisi ponavljajočih se imen, neizpolnjena polja. V postopku migracije na novo strukturo je potrebno popraviti čim več nepravilnosti, če je možno avtomatsko, sicer vsaj polavtomatsko - primer je “igra” za povezovanje vnosov izvajalcev, kjer smo do sedaj preimenovali, povezali in ločili 2016 izvajalcev od skupno 2537 spornih. Sledi še vsaj podobna “igra” za urejanje velikega števila nosilcev brez izpolnjenega podatka za album.

##Faza 2: Vmesnik za brskanje po bazi

###Iskanje

 * Osnova vmesnika je glavno iskalno polje, v katerega se vnese iskalni termin.
 * Pametno predlaganje iskalnega termina že med vnosom predlaga termine in vnaprej omejuje iskanje po enem specifičnem polju (recimo Metall dopolni v Metallica in išče samo po artistih). Predlogi so grupirani po tipih (izvajalci, albumi, ...)
 * Pritisk tipke enter, klik na gumb ob iskalnem polju ali izbira enega od pametnih predlogov sproži iskanje z vnešenim terminom.
 * Če ne izberemo pametnega predloga, je privzeto obnašanje, da so rezultati iskanja skladbe, ki imajo zadetek v kateremkoli polju.
 * S filtri (ali že s pomočjo pametnega predlaganja) lahko naknadno natančneje specificiramo, po katerih poljih želimo iskati.

###Prikaz rezultatov

 * Rezultati iskanja so skladbe in so prikazani v tabeli.
 * Tabelo je možno razvrščati po poljubnem stolpcu (izvajalec, leto, album, ...)
 * Posamezne stolpce je možno razvrščati in skrivati/prikazovati za boljši pregled nad rezultati.


##Faza 3: Vmesnik za urejanje glasbenih oprem

###Seznam glasbenih oprem

 * Opremljevalec privzeto vidi seznam svojih glasbenih oprem.
 * Na seznam lahko doda novo, prazno opremo.
 * Katerokoli od oprem lahko izbere za urejanje in se s tem premakne na pogled urejanja opreme.

###Urejanje opreme

Vmesnik oz. obrazec za urejanje je sestavljen iz naslednjih komponent:
 * Osnovni podatki o opremi (datum predvajanja, naslov, program, ...)
 * Seznam skladb na opremi (možno jih je dodajati, odstranjevati, spreminjati zaporedje, jim pripisovati komentarje).
   * Če ne gre drugače (digiteka), je možno zaenkrat v seznam dodati tudi prazno vrstico, kamor uporabnik ročno vnese skladbo, ki je ni v bazi.
 * Skupna dolžino opreme in presežek/pomanjkanje.
 * Gumbe za shranjevanje (ki je sicer tudi avtomatsko), tiskanje in zaključek opreme (s tem se jo objavi na spletno stran in doda v seznam za SAZAS, uporabnik pa se vrne na seznam glasbenih oprem).

###Izvažanje v XLS

Za potrebe javljanja predvajanih skladb SAZAS-u je možno avtomatsko generirati XLS datoteke z združenimi podatki o vseh glasbenih opremah za določen časovni razpon.

###Izvažanje na stran

Opreme je možno avtomatsko izvažati na radijsko spletno stran, pri čemer se avtomatsko vnesejo tako avtor kot čas objave.

##Faza 4: Vmesnik za vnašanje/urejanje izdaj

###Vnašanje
 * Fonotekar lahko vnese novo izdajo v podatkovno bazo.
 * Vmesnik za vnašanje je sestavljen iz dveh skupin polj - v prvi skupini so polja, ki so skupna za celoten album (založba, leto, ...), v drugi pa tista, ki so drugačna za vsako skladbo (naslov, trajanje, ...)
 * Pri ročnem vnosu vrednosti fonotekarju pomagajo pametni predlogi obstoječih vrednosti iz baze podatkov.
 * Poleg ročnega vnosa lahko fonotekar išče po odprti podatkovni bazi o glasbenih nosilcih Discogs.com. Kriterij za iskanje je bodisi izvajalec/ime albuma, bodisi unikatna koda (EAN/UPC/...). Če na tak način najde ustrezno izdajo, se podatki o njej avtomatsko napolnijo v obrazec.
 * Fonotekar lahko shrani ali prekliče spremembe.

###Urejanje/popravljanje obstoječih vnosov
 * Fonotekar lahko s pomočjo iskanja najde skladbe in odpre album katerekoli izmed njih za urejanje. Postopek urejanja je enak kot vnos novega albuma, opisan v prejšnji točki.

## Zgodovina sprememb:
1. Mato 2014-11-21 zadnja verzija na google docs
2. Borut 2015-01-14 prenesel v SPECS.md
 
