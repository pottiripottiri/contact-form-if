<?php
/**
 * Class SampleTest
 *
 * @package Contact_Form_If
 */

/**
 * Sample test case.
 */
class WPCFIFTest extends WP_UnitTestCase {

	/**
	 * WPCFのプロパティ取得処理をフックでできること.
	 * フックのインターフェイスが変わってないことだけチェック.
	 */
	public function test_hook_analyze_properties() {

		$form = <<<EOM
		[text test-1]
		[text test-2]
		[text test-3]
		[submit "送信"]
EOM;

		$additional_settings = <<<EOM
requireif-test-2: test-1,equal,1
requireif-test-3: test-1,equal,2
dummy: true
EOM;

		$id     = $this->factory->post->create(
			array(
				'post_type'    => 'wpcf7_contact_form',
				'post_content' => $form,
				'meta_input'   => array(
					'form'                => $form,
					'additional_settings' => $additional_settings,
				),
			)
		);
		$WPCF   = WPCF7_ContactForm::get_instance( get_post( $id ) );
		$WPCFIF = WPCFIF::get_instance();
		$this->assertEquals( $additional_settings, $WPCFIF->get_properties()['additional_settings'] );
	}

	/**
	 * WPCFのタグ情報を解析して、該当するポストデータが取得できること.
	 */
	public function test_get_post_value_from_wpcftag() {

		// POSTの値を取るのにモックを使ってみたけれどわかりにい
		// また、フックを経由するとこの方法だと効かないので（無茶なことをすればできそうな気もするが・・・）、
		// 自前のPOST値取得のラッピングメソッドにフックの口を設けて、
		// それを使ってPOST値にダミー値を差し込めるようにする。

		// $Mock_WPCFIF = $this->createPartialMock(WPCFIF::class, array('filter_input_post'));
		// $Mock_WPCFIF->method('filter_input_post')->willReturn(1);
		// $tag = new WPCF7_FormTag(array(
		// 'type' => 'text',
		// 'basetype' => 'text',
		// 'name' => 'test-1'
		// ));
		// $this->assertEquals('1', $Mock_WPCFIF->get_post_value($tag));

		remove_all_filters( 'wpcfif_filter_input_post', 99 );
		add_filter(
			'wpcfif_filter_input_post',
			function( $value, $name ) {
				return array(
					'test-1'   => 1,
					'select-1' => 1,
					'multi-2'  => array( 1, 2 ),
					'check-1'  => array( 1, 2 ),
					'date-1'   => '2021-01-01',
					'number-1' => 1,
				)[ $name ];
			},
			99,
			2
		);

		$WPCFIF = WPCFIF::get_instance();
		$this->assertEquals(
			'1',
			$WPCFIF->get_post_value(
				new WPCF7_FormTag(
					array(
						'type'     => 'text',
						'basetype' => 'text',
						'name'     => 'test-1',
					)
				)
			)
		);

		$WPCFIF = WPCFIF::get_instance();
		$this->assertEquals(
			'1',
			$WPCFIF->get_post_value(
				new WPCF7_FormTag(
					array(
						'type'     => 'select',
						'basetype' => 'select',
						'name'     => 'select-1',
					)
				)
			)
		);

		$this->assertEquals(
			array( 1, 2 ),
			$WPCFIF->get_post_value(
				new WPCF7_FormTag(
					array(
						'type'     => 'checkbox',
						'basetype' => 'checkbox',
						'name'     => 'check-1',
					)
				)
			)
		);
		$this->assertEquals(
			'2021-01-01',
			$WPCFIF->get_post_value(
				new WPCF7_FormTag(
					array(
						'type'     => 'date',
						'basetype' => 'date',
						'name'     => 'date-1',
					)
				)
			)
		);
		$this->assertEquals(
			'1',
			$WPCFIF->get_post_value(
				new WPCF7_FormTag(
					array(
						'type'     => 'number',
						'basetype' => 'number',
						'name'     => 'number-1',
					)
				)
			)
		);
	}
}
