<?php

class Issue {
	public function Get($issueId = 0) {
		return true;
	}

	public function Save($issueId = 0) {
		return true;
	}

	public function Update($issueId = 0) {
		return true;
	}

	public function Delete($issueId = 0) {
		return true;
	}

	public static function ReturnIssueClassifications($language = 'ru'): array {
		return [
			'classes' => self::ReturnIssueClasses($language),
			'objects' => self::ReturnIssueObjects($language),
			'subjects' => self::ReturnIssueSubjects($language),
			'categories' => self::ReturnIssueCategories($language),
		];
	}
	
	private static function ReturnIssueClasses($language = 'ru'): array {
		$classes = [
			'ru' =>
				[
					'1' => 'Взаимоотношения с семьей',
					'2' => 'Коллектив (работа, учеба)',
					'3' => 'Друзья и приятели',
					'4' => 'Сексуальные отношения',
					'5' => 'Зависимости и состояния',
					'6' => 'Комплексы',
					'7' => 'Другое',
				]
		];

		return $classes[$language];
	}
	
	private static function ReturnIssueObjects($language = 'ru'): array {
		$objects = [
			'ru' =>
				[
					'1' => 'Object1',
				]
		];

		return $objects[$language];
	}
	
	private static function ReturnIssueSubjects($language = 'ru'): array {
		$subjects = [
			'ru' =>
				[
					'1' => 'Subject1',
				]
		];

		return $subjects[$language];
	}
	
	private static function ReturnIssueCategories($language = 'ru'): array {
		$categories = [
			'ru' =>
				[
					'1' => 'Category1',
				]
		];

		return $categories[$language];
	}
}