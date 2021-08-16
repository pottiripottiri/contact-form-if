<?php
/**
 * 　Contact Form If Class
 *
 * @package     Contact_Form_If
 */

/**
 * 　Contact Form If Class
 */
class WPCFIF {

	/**
	 * WPCF7 プロパティ
	 *
	 * @var array
	 */
	private $properties = null;

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'contact_form_properties' ), 99, 2 );
		add_filter( 'wpcf7_validate', array( $this, 'validate_filter' ), 99, 2 );
	}

	/**
	 * コンタクトフォームのインスタンスなどを控えるためのフック処理
	 *
	 * @param array             $properties properties of WPCF7.
	 * @param WPCF7_ContactForm $contact_form instance of WPCF7.
	 * @return array
	 */
	public function contact_form_properties( $properties, $contact_form ) {
		$this->properties = $properties;
		return $properties;
	}

	/**
	 * バリデーションのフック処理
	 *
	 * @param WPCF7_Validation $result instance of WPCF7_Validation.
	 * @param array            $tags array of WPCF7_FormTag.
	 * @return WPCF7_Validation
	 */
	public function validate_filter( $result, $tags ) {

		$require_if_settings = array();
		$additional_settings = explode( "\n", $this->properties['additional_settings'] );

		foreach ( $additional_settings as $setting ) {
			if ( preg_match( '/^requireif-([a-zA-Z0-9_-]+)[\t ]*:(.*)$/', $setting, $matches ) ) {
				$args = explode( ',', trim( $matches[2] ) );
				if ( is_array( $args ) && count( $args ) === 3 ) {
					$require_if_settings[ $matches[1] ] = $args;
				}
			}
		}

		foreach ( $tags as $tag ) {
			$err = $this->not_empty( $tag, $tags, $require_if_settings );
			if ( '' !== $err ) {
				$result->invalidate( $tag, $err );
			}
		}

		return $result;
	}

	/**
	 * 必須チェック
	 *
	 * @param WPCF7_FormTag $tag WPCF7_FormTag.
	 * @param array         $tags array of WPCF7_FormTag.
	 * @param array         $settings array of WPCFIF settings.
	 * @return string　　　Error message.
	 */
	private function not_empty( $tag, $tags, $settings ) {

		$err = '';

		$require = false;
		foreach ( $settings as $name => $args ) {
			if ( $name !== $tag->name ) {
				continue;
			}

			foreach ( $tags as $t ) {
				if ( $args[0] === $t->name ) {
					$target_value = $this->get_post_value( $t );
					switch ( $args[1] ) {
						case 'eq':
							if ( (string) $args[2] === (string) $target_value ) {
								$require = true;
							}
							break;
					}
				}
			}
		}

		if ( $require ) {
			$own_value = $this->get_post_value( $tag, $tag );
			if (
				( is_string( $own_value ) && '' === $own_value ) ||
				( is_array( $own_value ) && empty( $own_value ) )
			) {
				$err = wpcf7_get_message( 'invalid_required' );
			}
		}

		return $err;
	}

	/**
	 * POSTパラメータ取得
	 *
	 * @param WPCF7_FormTag $tag WPCF7_FormTag.
	 * @return string|array ←returnの型が固定されてないのであんまりよくない・・・(TODO)
	 */
	private function get_post_value( $tag ) {

		// @codingStandardsIgnoreStart
		$post_data = $_POST;
		// @codingStandardsIgnoreEnd
		// TODO 現状TEXTとSELECTのみ対応

		switch ( $tag->basetype ) {
			case 'text':
				if ( isset( $post_data[ $tag->name ] ) ) {
					return trim( wp_unslash( strtr( (string) $post_data[ $tag->name ], "\n", ' ' ) ) );
				}
				break;
			case 'select':
				if ( $tag->has_option( 'multiple' ) ) {
					return array_filter(
						(array) $post_data[ $tag->name ],
						function( $val ) {
							return '' !== $val;
						}
					);
				} else {
					return isset( $post_data[ $tag->name ] ) ? (string) $post_data[ $tag->name ] : '';
				}
				break;
		}

		return '';
	}
}
