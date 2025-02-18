<?php

/*
 * This file is part of the leviframework to projetc git-hub-api.
 *
 * (c) Levi Costa <levi.costa1@gmail.com>
 *
 * For non-commercial use
 * 
 */
 

 /*
  * Set PHP heavily typed
  */
declare(strict_types=1);

namespace App\Model;

use FaaPz\PDO\Database as Database;

class Connection
{

	/**
     * @var DataBase
     *
     */
	public $db;

	/**
     * @var string
     *
     */
	private $connection;

	/**
     * @var string
     *
     */
	private $user;

	/**
     * @var string
     *
     */
	private $database;

	/**
     * @var password
     *
     */
	private $password;

	/**
     * @var host
     *
     */
	private $host;
	
	public function __construct()
	{
		$this->conDB();
	}

	private function conDB()
	{
		$this->user = getenv('APP_DB_USER') != '' ? getenv('APP_DB_USER') : 'spempres_git';
		$this->database = getenv('APP_DB_DATABASE') != '' ? getenv('APP_DB_DATABASE') : 'spempres_git';
		$this->password = getenv('APP_DB_PASSWORD') != '' ? getenv('APP_DB_PASSWORD'): 'elnata2012';
		$this->host = getenv('APP_DB_HOST') != '' ? getenv('APP_DB_HOST') : 'spemprestimo.com.br';

		$this->connection = 'mysql:host='.$this->host.';dbname='.$this->database.';charset=utf8';
		$this->db = new Database($this->connection, $this->user, $this->password);
		return $this->db;
	}

}