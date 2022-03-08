<?php
require '../vendor/autoload.php';

use App\Models\Gost;
use App\Models\Rezervacija;
use App\App;
use App\Views\BaseView;

//$app = new App();
// $gost = new Gost(1);
// $gost->Ime = "Djole";
// $gost->update();

// $otherclass = preg_replace_callback(
//     '/(.)_([a-z])/',
//      function ($matches){
//         return $matches[1].strtoupper($matches[2]);
//      },
//      "asdavvv_asdasd");
// echo $otherclass;
// $gost = new Rezervacija(3);
// echo var_dump($gost->getFromOtherTable("rezervacija_sobe","SobaID",10));


//$var = '{"links":[{"rel":"next","href":"\/rezervacija?limit=5&page=1","type":"GET"},{"rel":"self","href":"\/rezervacija?limit=5&page=0","type":"GET"},{"rel":"soba","href":"\/rezervacija\/{id}\/soba","type":"GET"},{"rel":"gost","href":"\/rezervacija\/{id}\/gost","type":"GET"},{"rel":"self","href":"\/rezervacija","type":"POST","shema":"\/shema\/rezervacija"},{"rel":"rezervacija_sobe","href":"\/rezervacija_sobe","type":"POST","shema":"\/shema\/rezervacija_sobe"}],"0":{"RezervacijaID":2,"MusterijaID":1,"DatumRezervisanja":"2021-02-02","VremeRezervisanja":"10:00:00","PocetakRezervacije":"2021-07-05 11:00:00","KrajRezervacije":"2021-07-12 11:00:00","RokZaUplatu":"2021-06-05","SumaZaUplatu":"1000000.00","IzvrsenaUplata":"Ne","Komentar":""},"1":{"RezervacijaID":3,"MusterijaID":3,"DatumRezervisanja":"2020-12-11","VremeRezervisanja":"19:49:26","PocetakRezervacije":"2021-08-11 11:00:00","KrajRezervacije":"2021-08-21 11:00:00","RokZaUplatu":"2021-07-11","SumaZaUplatu":"1500000.00","IzvrsenaUplata":"Da","Komentar":""},"2":{"RezervacijaID":4,"MusterijaID":2,"DatumRezervisanja":"2021-05-21","VremeRezervisanja":"19:51:58","PocetakRezervacije":"2021-07-01 19:30:00","KrajRezervacije":"2021-07-07 19:30:00","RokZaUplatu":"2021-06-07","SumaZaUplatu":"800000.00","IzvrsenaUplata":"Ne","Komentar":""},"3":{"RezervacijaID":6,"MusterijaID":3,"DatumRezervisanja":"2021-02-02","VremeRezervisanja":"10:00:00","PocetakRezervacije":"2021-07-05 11:00:00","KrajRezervacije":"2021-07-12 11:00:00","RokZaUplatu":"2021-06-05","SumaZaUplatu":"1000000.00","IzvrsenaUplata":"Ne","Komentar":""},"page":0,"limit":5}';
//$length = strlen($var);
//echo var_dump($length);
//$view = new BaseView($var);
//echo var_dump($view->findLastBracket($var));
//preg_match_all('/"[0-9]":\{/',$var,$match,PREG_OFFSET_CAPTURE);
//preg_replace('/'.$match[0][0].'/','[{',$var,1)
//$end_of_item = $view->findLastBracket($last_item);
// $end_of_item = $view->findLastBracket($last_item) + $start_of_item;
// $var=substr_replace($var,']',$end_of_item+1,0);
// echo var_dump($var);
// echo var_dump($end_of_item);
