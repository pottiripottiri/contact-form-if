<?php
/**
 * 　Contact Form If Class
 *
 * @package     Contact_Form_If
 */
class WPCFIF {

	/**
	 * WPCF7 インスタンス
	 *
	 * @var WPCF7_ContactForm
	 */
	private $contact_form = null;

	/**
	 * WPCF7 プロパティ
	 *
	 * @var array
	 */
	private $properties = null;

	/**
	 * WPCF7 タグ
	 *
	 * @var array
	 */
	private $tags = null;

	/**
	 * WPCF7 その他設定
	 *
	 * @var array
	 */
	private $additional_settings = null;

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'contact_form_properties' ), 99, 2 );
		add_filter( 'wpcf7_validate_text', array( $this, 'text_validation_filter' ), 99, 2 );
		add_filter( 'wpcf7_validate_email', array( $this, 'text_validation_filter' ), 99, 2 );
		add_filter( 'wpcf7_validate_url', array( $this, 'text_validation_filter' ), 99, 2 );
		add_filter( 'wpcf7_validate_tel', array( $this, 'text_validation_filter' ), 99, 2 );
	}

	/**
	 * コンタクトフォームのインスタンスなどを控えるためのフック処理
	 *
	 * @param array             $properties properties of WPCF7.
	 * @param WPCF7_ContactForm $contact_form instance of WPCF7.
	 * @return array
	 */
	public function contact_form_properties( $properties, $contact_form ) {
		$this->properties   = $properties;
		$this->contact_form = $contact_form;
		return $properties;
	}

	/**
	 * テキストファイルのバリデーションのフック処理
	 *
	 * @param WPCF7_Validation $result instance of WPCF7_Validation.
	 * @param WPCF7_FormTag    $tag instancr of WPCF7_FormTag.
	 * @return WPCF7_Validation
	 */
	public function text_validation_filter( $result, $tag ) {

		if ( is_null( $this->_tags ) ) {
			$this->tags = $this->contact_form->scan_form_tags(
				array(
					'feature' => '! file-uploading',
				)
			);
		}

		if ( is_null( $this->additional_settings ) ) {
			$this->additional_settings = array();
			$settings                  = explode( "\n", $this->properties['additional_settings'] );

			foreach ( $settings as $setting ) {
				if ( preg_match( '/^requireif-([a-zA-Z0-9_-]+)[\t ]*:(.*)$/', $setting, $matches ) ) {
					$args = explode( ',', trim( $matches[2] ) );
					if ( is_array( $args ) && count( $args ) === 3 ) {
						$this->additional_settings[ $matches[1] ] = $args;
					}
				}
			}
		}

		$require = false;
		foreach ( $this->additional_settings as $name => $args ) {
			if ( $name !== $tag->name ) {
				continue;
			}

			// TODO現状TEXTのみ対応.
			foreach ( $this->tags as $t ) {
				if ( $args[0] === $t->name ) {
					$target_value = $this->get_post_value_text( $t->name );
					if ( (string) $args[2] === (string) $target_value ) {
						$require = true;
					}
					break;
				}
			}
		}

		if ( $require ) {
			$own_value = $this->get_post_value_text( $tag->name );
			if ( '' === $own_value ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			}
		}

		return $result;
	}

	/**
	 * POSTパラメータ取得（text)
	 *
	 * @param string $name POST parameter name.
	 * @return string
	 */
	private function get_post_value_text( $name ) {

		$value = '';
		// @codingStandardsIgnoreStart
		if ( isset( $_POST[ $name ] ) ) {
			$value = trim( wp_unslash( strtr( (string) $_POST[ $name ], "\n", ' ' ) ) );
		}
		// @codingStandardsIgnoreEnd

		return $value;
	}
}
