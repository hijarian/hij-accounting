#!/usr/bin/env php
<?php
main();

function main()
{
    $file = open_file();

    $db = open_db();

    while ($record = read_record($file))
        persist(process_record($record), $db);

    fclose($file);
}

/**
 * @return PDO
 */
function open_db()
{
    return new PDO('sqlite:./src/spending.db');
}

/**
 * @param array $data
 * @param PDO $db
 */
function persist($data, $db)
{
    $values = [
        ':date' => $data['date'],
        ':place' => '',
        ':name' => $data['name'],
        ':amount' => '1',
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

    $type = mb_convert_case($type, MB_CASE_LOWER, "UTF-8");
    $price = intval(floatval($price) * 100); // truncate for real

    return compact('name', 'type', 'date', 'price');
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
    return fgetcsv($file, 256, "\t");
}

/**
 * @return resource
 */
function open_file()
{
    return fopen('Import.csv', 'r');
}
