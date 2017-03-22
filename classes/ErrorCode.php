<?php
class ErrorCode {
	public static function Get($errorCode, $language = 'en'): string {
		if (config::get('error.' . $errorCode . '.' . $language)) {
			return config::get('error.' . $errorCode .'.' . $language);
		}
		
		return config::get('error.default.' . $language);
	}
}
