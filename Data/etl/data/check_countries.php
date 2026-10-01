<?php
header('Content-Type: text/plain; charset=utf-8');

$ch = curl_init('https://api-v2.oec.world/tesseract/data.jsonrecords?cube=trade_i_baci_a_92&drilldowns=HS6,Exporter+Country,Year&measures=Trade+Value,Quantity&Importer+Country=euche&HS4=20603');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response, true);
$records = $json['data'];

$countries = [];
for ($i = 0; $i < count($records); $i++) {
    $name = $records[$i]['Exporter Country'];
    if (!in_array($name, $countries)) {
        $countries[] = $name;
    }
}
sort($countries);

echo count($countries) . " verschiedene Laender:\n\n";
for ($i = 0; $i < count($countries); $i++) {
    echo "  " . ($i + 1) . ". " . $countries[$i] . "\n";
}