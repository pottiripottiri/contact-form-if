<?php
/**
 * Class SampleTest
 *
 * @package Contact_Form_If
 */

/**
 * Sample test case.
 */
class WPCFIFValidateGreaterTest extends WP_UnitTestCase {

	/**
	 * バリデーション結果
	 */
	protected $invalid_fields = null;

	/**
	 * テスト実行前処理
	 * テストケースクラスの最初のテストメソッドの実行前に一度だけ実行
	 */
	public function setUp()
    {
        // WPCF7_Submissionがシングルトンのため、
		// テストケースごとにValidateかけてもうまくいかない
		// ここで全テストケース分のValidateをかけて後で結果だけ見る

		$form = <<<EOM
[text eq-1][text eq-2][text eq-3][text eq-4][text eq-5]
[text greater-1][text greater-2][text greater-3][text greater-4][text greater-5]
[text less-1][text less-2][text less-3][text less-4][text less-5]
[text in-1][text in-2][text in-3][text in-4][text in-5]
[submit "送信"]`
EOM;

		$additional_settings = <<<EOM
requireif-eq-2: eq-1,equal,1
requireif-eq-3: eq-1,equal,1
requireif-eq-4: eq-1,equal,2
requireif-eq-5: eq-1,not_equal,2
requireif-greater-2: greater-1,greater_than,1
requireif-greater-3: greater-1,greater_than,2
requireif-greater-4: greater-1,greater_equal,1
requireif-greater-5: greater-1,greater_equal,2
requireif-less-2: less-1,less_than,2
requireif-less-3: less-1,less_than,1
requireif-less-4: less-1,less_equal,2
requireif-less-5: less-1,less_equal,1
requireif-in-2: in-1,in,1 3
requireif-in-3: in-1,in,1 2
requireif-in-4: in-1,not_in,1 2
requireif-in-5: in-1,not_in,2 3
EOM;

		remove_all_filters('wpcfif_filter_input_post', 99);
		add_filter( 'wpcfif_filter_input_post', function($name, $value) {
			return array(
				'eq-1' => 1,
				'eq-2' => '',
				'eq-3' => '1',
				'eq-4' => '',
				'eq-5' => '',
				'greater-1' => 2,
				'greater-2' => '',
				'greater-3' => '',
				'greater-4' => '',
				'greater-5' => '',
				'less-1' => 1,
				'less-2' => '',
				'less-3' => '',
				'less-4' => '',
				'less-5' => '',
				'in-1' => 3,
				'in-2' => '',
				'in-3' => '',
				'in-4' => '',
				'in-5' => '',
				)[$name];
		}, 99, 2 );

		$id = $this->factory->post->create(array(
			'post_type' => 'wpcf7_contact_form',
			'post_content' => $form,
			'meta_input' => array(
				'form' => $form,
				'additional_settings' => $additional_settings,
			),
		));

		$WPCF = WPCF7_ContactForm::get_instance(get_post($id));
		$Submission = WPCF7_Submission::get_instance($WPCF);
		if (is_null($Submission)) {
			$Submission = WPCF7_Submission::get_instance();
		}
		$this->invalid_fields = $Submission->get_invalid_fields();

		return;
    }

	/**
	 * テキスト項目のバリデーション（完全一致、不一致）.
	 */
	public function test_validate_text_equal() {

		$this->assertEquals(true, isset($this->invalid_fields['eq-2']));
		$this->assertEquals(false, isset($this->invalid_fields['eq-3']));
		$this->assertEquals(false, isset($this->invalid_fields['eq-4']));
		$this->assertEquals(true, isset($this->invalid_fields['eq-5']));

	}

	/**
	 * テキスト項目のバリデーション（>、>=）.
	 */
	public function test_validate_text_greater() {

		$this->assertEquals(true, isset($this->invalid_fields['greater-2']));
		$this->assertEquals(false, isset($this->invalid_fields['greater-3']));
		$this->assertEquals(true, isset($this->invalid_fields['greater-4']));
		$this->assertEquals(true, isset($this->invalid_fields['greater-5']));

	}

	/**
	 * テキスト項目のバリデーション（<、<=）.
	 */
	public function test_validate_text_less() {

		$this->assertEquals(true, isset($this->invalid_fields['less-2']));
		$this->assertEquals(false, isset($this->invalid_fields['less-3']));
		$this->assertEquals(true, isset($this->invalid_fields['less-4']));
		$this->assertEquals(true, isset($this->invalid_fields['less-5']));

	}

	/**
	 * テキスト項目のバリデーション（in 、not in）.
	 */
	public function test_validate_text_in() {

		$this->assertEquals(true, isset($this->invalid_fields['in-2']));
		$this->assertEquals(false, isset($this->invalid_fields['in-3']));
		$this->assertEquals(true, isset($this->invalid_fields['in-4']));
		$this->assertEquals(false, isset($this->invalid_fields['in-5']));

	}

}
