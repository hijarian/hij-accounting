<?php
use datatypes\MeasureUnit;

/** hijarian 23.11.13 13:50 */

class MeasureUnitStorage
{
	/** @var PDO */
	private $db;

	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @param string $name Name
	 * @return MeasureUnit
	 * @throws StorageOperationException
	 */
	public function getUnitByName($name)
	{
		$finder = $this->db->prepare("select id, name from units where name = :name");
		$finder->bindParam(':name', $name);
		$found = $finder->execute();
		if ($found === false)
			throw new StorageOperationException("Не удалось выполнить поиск единицы измерения под названием {$name}.", $finder->errorInfo());

		$result = $finder->fetch(PDO::FETCH_ASSOC);
		if (!$result)
			throw new StorageOperationException("Не удалось найти единицу измерения под названием {$name}", $finder->errorInfo());

		return new MeasureUnit($result['id'], $result['name']);
	}

	public function getList()
	{
		$result = $this->db->query("select id, name from units");
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getById($id)
	{
		$finder = $this->db->prepare("select id, name from units where id=:id");
		$counted = $finder->execute([':id' => $id]);
		if ($counted === false)
			throw new StorageOperationException("Не удалось выполнить команду получения единицы измерения с кодом {$id}", $finder->errorInfo());

		$result = $finder->fetch(PDO::FETCH_ASSOC);
		if (!$result)
			throw new StorageOperationException("Не удалось получить единицу измерения с кодом {$id}", $finder->errorInfo());

		return new MeasureUnit($result['id'], $result['name']);
	}
} 