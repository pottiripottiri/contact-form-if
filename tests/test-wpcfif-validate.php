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
	public function setUp() {
		// WPCF7_Submissionがシングルトンのため、
		// テストケースごとにValidateかけてもうまくいかない
		// ここで全テストケース分のValidateをかけて後で結果だけ見る

		$form = <<<EOM
[text isnull-1][text isnull-2][text isnull-3][checkbox isnull-4 "1" "2" "3"][text isnull-5]
[text notnull-1][text notnull-2][text notnull-3][checkbox notnull-4 "1" "2" "3"][text notnull-5]
[text eq-1][text eq-2][text eq-3][text eq-4][text eq-5][checkbox eq-6 "1" "2" "3"][text eq-7][checkbox eq-8 "1" "2" "3"][text eq-9]
[text greater-1][text greater-2][text greater-3][text greater-4][text greater-5]
[text less-1][text less-2][text less-3][text less-4][text less-5]
[text in-1][text in-2][text in-3][text in-4][text in-5]
[checkbox in-6 "1" "2" "3" "4"][text in-7][text in-8][text in-9][text in-10]

[submit "送信"]`
EOM;

		$additional_settings = <<<EOM
requireif-isnull-2: isnull-1,is_null,
requireif-isnull-3: isnull-1,is_null,
requireif-isnull-5: isnull-4,is_null,
requireif-notnull-2: notnull-1,not_null,
requireif-notnull-3: notnull-1,not_null,
requireif-eq-2: eq-1,equal,1
requireif-eq-3: eq-1,equal,1
requireif-eq-4: eq-1,equal,2
requireif-eq-5: eq-1,not_equal,2
requireif-eq-7: eq-6,equal,1
requireif-eq-9: eq-8,not_equal,1
requireif-greater-2: greater-1,greater_than,1
requireif-greater-3: greater-1,greater_than,2
requireif-greater-4: greater-1,greater_equal,1
requireif-greater-5: greater-1,greater_equal,2
requireif-less-2: less-1,less_than,2
requireif-less-3: less-1,less_than,1
requireif-less-4: less-1,less_equal,2
requireif-less-5: less-1,less_equal,1
requireif-in-2: in-1,in,1 3
requireif-in-3: in-1,in,2 3
requireif-in-4: in-1,not_in,2 3
requireif-in-5: in-1,not_in,1 3
requireif-in-7: in-6,in,1 3
requireif-in-8: in-6,in,2 3
requireif-in-9: in-6,not_in,2 3
requireif-in-10: in-6,not_in,1 3
EOM;

		remove_all_filters( 'wpcfif_filter_input_post', 99 );
		add_filter(
			'wpcfif_filter_input_post',
			function( $value, $name ) {
				return array(
					'isnull-1'  => '',
					'isnull-2'  => '',
					'isnull-3'  => '1',
					'isnull-4'  => array(),
					'isnull-5'  => '',
					'notnull-1' => '1',
					'notnull-2' => '',
					'notnull-3' => '1',
					'notnull-4' => array( '1' ),
					'notnull-5' => '',
					'eq-4'      => '',
					'eq-1'      => 1,
					'eq-2'      => '',
					'eq-3'      => '1',
					'eq-4'      => '',
					'eq-5'      => '',
					'eq-6'      => array( '1' ),
					'eq-7'      => '',
					'eq-8'      => array( '2', '3' ),
					'eq-9'      => '',
					'greater-1' => 2,
					'greater-2' => '',
					'greater-3' => '',
					'greater-4' => '',
					'greater-5' => '',
					'less-1'    => 1,
					'less-2'    => '',
					'less-3'    => '',
					'less-4'    => '',
					'less-5'    => '',
					'in-1'      => 1,
					'in-2'      => '',
					'in-3'      => '',
					'in-4'      => '',
					'in-5'      => '',
					'in-6'      => array( 1, 4 ),
					'in-7'      => '',
					'in-8'      => '',
					'in-9'      => '',
					'in-10'     => '',
				)[ $name ];
			},
			99,
			2
		);

		$id = $this->factory->post->create(
			array(
				'post_type'    => 'wpcf7_contact_form',
				'post_content' => $form,
				'meta_input'   => array(
					'form'                => $form,
					'additional_settings' => $additional_settings,
				),
			)
		);

		$WPCF       = WPCF7_ContactForm::get_instance( get_post( $id ) );
		$Submission = WPCF7_Submission::get_instance( $WPCF );
		if ( is_null( $Submission ) ) {
			$Submission = WPCF7_Submission::get_instance();
		}
		$this->invalid_fields = $Submission->get_invalid_fields();

		return;
	}

	/**
	 * テキスト項目のバリデーション（IS NULL、NOT NULL）.
	 */
	public function test_validate_text_is_null() {

		$this->assertEquals( true, isset( $this->invalid_fields['isnull-2'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['isnull-3'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['notnull-2'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['notnull-3'] ) );

	}

	/**
	 * テキスト項目のバリデーション（完全一致、不一致）.
	 */
	public function test_validate_text_equal() {

		$this->assertEquals( true, isset( $this->invalid_fields['eq-2'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['eq-3'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['eq-4'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['eq-5'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['eq-7'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['eq-9'] ) );
	}

	/**
	 * テキスト項目のバリデーション（>、>=）.
	 */
	public function test_validate_text_greater() {

		$this->assertEquals( true, isset( $this->invalid_fields['greater-2'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['greater-3'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['greater-4'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['greater-5'] ) );

	}

	/**
	 * テキスト項目のバリデーション（<、<=）.
	 */
	public function test_validate_text_less() {

		$this->assertEquals( true, isset( $this->invalid_fields['less-2'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['less-3'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['less-4'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['less-5'] ) );

	}

	/**
	 * テキスト項目のバリデーション（in 、not in）.
	 */
	public function test_validate_text_in() {

		$this->assertEquals( true, isset( $this->invalid_fields['in-2'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['in-3'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['in-4'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['in-5'] ) );

		$this->assertEquals( true, isset( $this->invalid_fields['in-7'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['in-8'] ) );
		$this->assertEquals( true, isset( $this->invalid_fields['in-9'] ) );
		$this->assertEquals( false, isset( $this->invalid_fields['in-10'] ) );
	}

}
