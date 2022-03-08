<?php

return [

    "index" => [
        "msg" => "welcome",
        "links" => [
            "/shema/rezervacija",
            "/shema/musterija",
            "/shema/gost",
            "/shema/uplata",
            "/shema/rezervacija_sobe",
            "/shema/tip_dodatka",
            "/shema/soba"
        ]
    ],

    "rezervacija_sobe" => [
        
        "link" => "/rezervacija_sobe",

        "params" => [
            "RezervacijaID : integer",
            "SobaID : integer",
            "GostID : integer"
        ],
        "exemple" => [
            "RezervacijaID" => 3,
            "SobaID" => 8,
            "GostID" => 4 
        ]
    
    ]

];