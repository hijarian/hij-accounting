#!/usr/bin/env php
<?php
main();

function main()
{
    $filename = 'Import3.csv';
    $dbname = './src/spending.db';

    $file = open_file($filename);

    $db = open_db($dbname);

    while ($record = read_record($file))
        persist(process_record($record), $db);

    fclose($file);
}

/**
 * @return PDO
 */
function open_db($dbname)
{
    return new PDO("sqlite:{$dbname}");
}

/**
 * @param array $data
 * @param PDO $db
 */
function persist($data, $db)
{
    $values = [
        ':date' => $data['date'],
        ':place' => $data['place'],
        ':name' => $data['name'],
        ':amount' => $data['amount'],
        ':unit' => '1',
        ':price' => $data['price'],
        ':discount' => 0,
        ':tags' => $data['type']
    ];

    $inserter = $db->prepare(
        'insert into spending (date, place, name, amount, unit, price, discount, tags) values (:date, :place, :name, :amount, :unit, :price, :discount, :tags)'
    );

    $inserted = $inserter->execute($values);
    if ($inserted === false)
        var_dump($inserter->errorInfo());
}

/**
 * @param array $record
 * @return array
 */
function process_record($record)
{
    if (is_invalid($record))
        return $record;

    $name = array_shift($record);
    $type = array_shift($record);
    $date = array_shift($record);
    $price = array_shift($record);
    $place = array_shift($record);
    $amount = array_shift($record);

    $type = mb_convert_case($type, MB_CASE_LOWER, "UTF-8");
    $price = intval(floatval(str_replace(',', '.', $price)) * 100); // truncate for real
    $amount = intval($amount) > 0 ? $amount : 1;

	// DEBUG SHIT
	printf("%s\t%s\t%s\t%s\t%s\t%s\n", $name, $type, $date, $price, $place, $amount);
 
    return compact('name', 'type', 'date', 'price', 'place', 'amount');
}

/**
 * @param array $record
 * @return bool
 */
function is_invalid($record)
{
    return !is_array($record) or count($record) < 4;
}

/**
 * @param $file
 * @return array
 */
function read_record($file)
{
    return fgetcsv($file, 1024, "\t");
}

/**
 * @return resource
 */
function open_file($filename)
{
    return fopen($filename, 'r');
}
