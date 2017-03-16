<?php
class Classification {
	public static function ReturnClassification($language = 'ru'): array {
		return [
			'classes' => self::ReturnClasses($language),
			'objects' => self::ReturnObjects($language),
			'subjects' => self::ReturnSubjects($language),
			'categories' => self::ReturnCategories($language),
		];
	}

	public static function ReturnClasses($language = 'ru'): array {
		return config::get('classification.classes.' .$language);
	}

	public static function ReturnObjects($language = 'ru'): array {
		return config::get('classification.objects.' .$language);
	}

	public static function ReturnSubjects($language = 'ru'): array {
		return config::get('classification.subjects.' .$language);
	}

	public static function ReturnCategories($language = 'ru'): array {
		return config::get('classification.categories.' .$language);
	}
}
