<?php

if (!function_exists('random_id')) {
	function random_id(?int $digits = 3)
	{
		$digits = 3;
		return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
	}
}

if (!function_exists('is_first_day')) {
	function is_first_day($date)
	{
		return date('d', strtotime($date)) == '01';
	}
}

if (!function_exists('num_to_letter')) {
	function num_to_letter($number)
	{
		$formatterEs = new NumberFormatter("es-ES", NumberFormatter::SPELLOUT);
		return $formatterEs->format($number);
	}
}

if (!function_exists('setDBInConnection')) {
	function setDBInConnection($connection, $database)
	{
		config([
			"database.connections.$connection.database" => $database,
		]);

		\DB::purge($connection);
	}
}

// if (!function_exists('setConfigEmpresa')) {
// 	function setConfigEmpresa(CliEmpresa $empresa)
// 	{
// 		config([
// 			"empresa.nombre"=>$empresa->nombre,
// 			"empresa.logo"=>$empresa->logo
// 		]);
// 	}
// }

if (!function_exists('copyDBConnection')) {
	function copyDBConnection($copyFromConnection, $newConnectionName)
	{
		config([
			"database.connections.$newConnectionName" => config("database.connections.$copyFromConnection"),
		]);
	}
}

if (!function_exists('createDatabase')) {
	function createDatabase(string $database)
	{
		return DB::statement("CREATE DATABASE IF NOT EXISTS $database");
	}
}

if (!function_exists('dbExists')) {
	function dbExists($database)
	{
		return DB::select("select schema_name from information_schema.schemata where schema_name = '$database'");
	}
}

