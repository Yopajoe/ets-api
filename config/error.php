<?php
/**
 *  Lista statusnih gresaka aplikacije
 * 
 * 
 */
return 
[
    // "middleware" greske 1xx
    100 => "Content-Type : multipart/form-data nije podrzan format zahteva.",
    101 => "Putanja URI-ja nakon kolekcije mora da sadrzi ID broj resursa.",
    102 => "Pogresan format query stringa u URL putanji",

    //baza podataka 2xx
    200 => "Interna greska sa bazom podataka.",
    201 => "Upit zahteva redove van dometa tabele {table}.",
    202 => "Nepostojaci red sa {key} = {value} u bazi podataka",
    203 => "{table} tabela ne koresporedira sa parametrima {arguments}",

    //model
    300 => "Nepostojace {proprety} svojstvo u modelu {class}",
    301 => "Vrednost {value} svojstava {proprety} ne ispunjava pravilo/a {validation} za model {class}",
    302 => "Atribut {attribute} nije FORIGN_KEY",

    //routing
    400 => "Ne postoji. Pogledajte dokumentaciju na linku '/shema'.",
    401 => "Kolekcije {collection1} i {collection2} nisu uzajemno povezane u putanji {path}",
    402 => "Vrednost {id} za id atribut za tabelu {current} nema nijednu vezu sa {next} tabelom",
    403 => "POST zahtev za putanju mora da sadrzi samo kolekciju '{table}'u kojoj se dodaje novi red ",
    404 => "POST zahtev ne sme da ukljucuje vrednost '{id}' za primarni kljuc kolekciju '{table}'",
    405 => "Nepostojace svojstvo '{property}' u kolekciji '{table}'",
    406 => "Morate da navedete validan id za entitet u kolekciji '{table}' u putanji",

    //php errors
    500 => "Internal Server Error",

    //shema
    600 => "Izvini, jos ne postoji dokumentacija za kolekciju {table}"
];