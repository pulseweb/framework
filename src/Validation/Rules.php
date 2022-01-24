<?php

namespace Pulse\Validation;

use DateTime;
use Exception;
use ENV;
use Pulse\Model\DataBase;

class Rules
{
	public function required($str = null): bool
	{
		if (is_object($str)) return true;

		return is_array($str) ? !empty($str) : (trim($str) !== '');
	}

	public function min_length(string $str = null, string $val): bool
	{
		return (is_numeric($val) && $val <= mb_strlen($str));
	}

	public function max_length(string $str = null, string $val): bool
	{
		return (is_numeric($val) && $val >= mb_strlen($str));
	}

	public function min(string $str = null, string $val): bool
	{
		return (is_numeric($val) && is_numeric($str) && $val <= $str);
	}

	public function max(string $str = null, string $val): bool
	{
		return (is_numeric($val) && is_numeric($str) && $val >= $str);
	}


	// Format
	public function valid_email(string $str = null): bool
	{
		return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
	}

	public function valid_int(string $str = null): bool
	{
		$tmp = $str + 0;
		return (bool) is_integer($tmp);
	}
	public function valid_time(string $str = null): bool
	{
		$pattern = "/^([01][0-9]|2[0-3]):([0-5][0-9])$/";
		return (bool) preg_match($pattern, $str);
	}

	public function is_unique(string $str = null, string $field)
	{
		$db = new DataBase(ENV::DB_HOST, ENV::DB_NAME, ENV::DB_USER, ENV::DB_PASS);
		$field = explode('.', $field);

		$stmt = $db->table($field[0])->where($field[1] . " = ", $str)->run();

		return !$db->Fetch($stmt);
	}

	// Date
	public function valid_date(string $str = null): bool
	{
		$d = DateTime::createFromFormat('Y-m-d', $str);
		return (bool) $d && $d->format('Y-m-d') === $str;
	}

	public function date_compare(string $str = null, string $condition, string $date, string $operation_name = "", string $compared_with = ""): bool
	{
		$mainDate = strtotime($str); //the main date
		$SecondDate = strtotime($date);

		$operation_name = "greater than or equal";
		$len = strlen($condition);
		for ($i = 0; $i < $len; $i++) {
			switch ($condition[$i]) {
				case 'g':
				case 'G':
					if ($mainDate > $SecondDate) {
						return true;
					}
					break;

				case 'l':
				case 'L':
					if ($mainDate < $SecondDate) {
						return true;
					}
					break;

				case 'e':
				case 'E':
					if ($mainDate == $SecondDate) {
						return true;
					}
					break;

				default:
					throw new Exception("Undefined condition for `date_compare`");
					break;
			}
		}
		return false;
	}
	// url
	public function valid_url(string $str = null): bool
	{
		return (bool) filter_var($str, FILTER_VALIDATE_URL);
	}

	public function DateTime(string $str = null)
	{
		$d = DateTime::createFromFormat('Y-m-d H:i', $str);

		return $d && $d->format('Y-m-d H:i') == $str;
	}
}
