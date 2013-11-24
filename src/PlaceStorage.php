<?php
use datatypes\Place;

/** hijarian 23.11.13 0:32 */

class PlaceStorage
{
	/**
	 * @param PDO $db
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @param string $name Name
	 * @return Place Either already existing place with given name or new one
	 * @throws Exception
	 */
	public function getPlaceByName($name)
	{
		$finder = $this->db->prepare("select id, name from places where name = :name");
		$finder->bindParam(':name', $name);
		$found = $finder->execute();
		if ($found === false)
			throw new StorageOperationException("Не удалось выполнить поиск места под названием {$name}.", $finder->errorInfo());

		$result = $finder->fetch(PDO::FETCH_ASSOC);
		if ($result)
			return new Place($result['id'], $result['name']);

		$finder->closeCursor();
		$inserter = $this->db->prepare('insert into places (name) values (:name)');
		$inserted = $inserter->execute([':name' => $name]);
		if ($inserted === false)
			throw new StorageOperationException("Не удалось добавить новое место под названием {$name}.", $inserter->errorInfo());

		$inserter->closeCursor();
		$found = $finder->execute();
		if ($found === false)
			throw new StorageOperationException("Не удалось выполнить поиск места под названием {$name} после его регистрации в БД.", $finder->errorInfo());

		$result = $finder->fetch(PDO::FETCH_ASSOC);
		if (!$result)
			throw new StorageOperationException("Не удалось найти место под названием {$name} даже после его регистрации в БД (?!).", $finder->errorInfo());

		return new Place($result['id'], $result['name']);
	}
} 