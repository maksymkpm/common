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
	
	public static function ReturnIssueClasses() {
		return [
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
	}
}