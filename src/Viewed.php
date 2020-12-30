<?php

namespace Thichweb;

use Thichweb\Session;

/**
 * ### LỚP XỬ LÝ CÁC SẢN PHẨM ĐÃ XEM
 */
class Viewed
{
	static private $name = 'viewWS';

	static function count() {
		return Session::has(self::$name) ? count(Session::get(self::$name)) : 0;
	}

	static function has($id) {
		if (!Session::has(self::$name)) {
			Session::put(self::$name, []);
		}

		foreach (Session::get(self::$name) as $k => $i) {
			# code...
			if (Session::get(self::$name.".{$k}") == $id) {
				return true;
			}
		}

		return false;
	}

	static function add($id) {

		if (!self::has($id)) {
			Session::push(self::$name, $id);
		}
	}

	static function list(): array {
		return Session::get(self::$name) ?: [];
	}

	static function remove($id) {
		if (self::has($id)) {
			foreach (Session::get(self::$name) as $k => $i) {
				# code...
				if (Session::get(self::$name.".{$k}") == $id) {
					Session::forget(self::$name.".{$k}");
					return true;
				}
			}
		}
	}

	static function clear() {
		Session::forget(self::$name);
	}
}
