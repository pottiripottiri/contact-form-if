<?php
/**
 * 　Contact Form If Class
 *
 * @package     Contact_Form_If
 */

/**
 * 　Contact Form If Class≠
 */
class WPCFIF {

	/**
	 * WPCF7IF インスタンス
	 *
	 * @var WPCFIF
	 */

	private static $current = null;

	/**
	 * WPCF7 プロパティ
	 *
	 * @var array
	 */
	private $properties = null;

	/**
	 * コンストラクタ　.
	 */
	private function __construct() {
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'contact_form_properties' ), 99, 2 );
		add_filter( 'wpcf7_validate', array( $this, 'validate_filter' ), 99, 2 );
	}

	/**
	 * インスタンス取得
	 * @return WPCFIF
	 */
	public static function get_instance() {
		if (is_null(self::$current)) {
			self::$current = new self();
		}
		return self::$current;
	}


	/**
	 * コンタクトフォームのプロパティを控えるためのフック処理
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
	 * コンタクトフォームのプロパティを返す
	 *
	 * @return array
	 */
	public function get_properties() {
		return $this->properties;
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
						case 'is_null':
							if ( is_null($target_value) || '' === $target_value ) {
								$require = true;
							}
							break;
						case 'not_null':
							if ( !is_null($target_value) && '' !== $target_value ) {
								$require = true;
							}
							break;							
						case 'equal':
							if ( (string) $args[2] === (string) $target_value ) {
								$require = true;
							}
							break;
						case 'not_equal':
							if ( (string) $args[2] !== (string) $target_value ) {
								$require = true;
							}
							break;
						case 'greater_than':
							if ( (string) $args[2] < (string) $target_value ) {
								$require = true;
							}
							break;
						case 'greater_equal':
							if ( (string) $args[2] <= (string) $target_value ) {
								$require = true;
							}
							break;
						case 'less_than':
								if ( (string) $args[2] > (string) $target_value ) {
									$require = true;
								}
								break;
						case 'less_equal':
							if ( (string) $args[2] >= (string) $target_value ) {
								$require = true;
							}
							break;
						case 'in':
							if ( in_array((string) $target_value, explode( ' ', (string) $args[2]) )) {
								$require = true;
							}
							break;
						case 'not_in':
							if ( !in_array((string) $target_value, explode( ' ', (string) $args[2]) )) {
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
	public function get_post_value( $tag ) {

		// TODO 現状TEXTとSELECTのみ対応
		switch ( $tag->basetype ) {
			case 'text':
				return trim( wp_unslash( strtr( (string) $this->filter_input_post($tag->name), "\n", ' ' ) ) );
			case 'select':
				if ( $tag->has_option( 'multiple' ) ) {
					return array_filter(
						(array) $this->filter_input_post($tag->name, FILTER_DEFAULT, FILTER_FORCE_ARRAY),
						function( $val ) {
							return '' !== $val;
						}
					);
				} else {
					return trim( wp_unslash( strtr( (string) $this->filter_input_post($tag->name), "\n", ' ' ) ) );
				}
				break;
		}

		return ;
	}

	/**
	 * Gets a specific external variable by name and optionally filters it.
	 * ユニットテストではPOSTパラメータが渡せないので、モックでPOSTデータ取得メソッドを入れ替えるためにあえてラッピングしたメソッド
	 * ※protectedなのはモックを作るのにprotected以上である必要があるからです
	 * @param string $name
	 * @param int $filter
	 * @param array|int $options
	 * @return mixed
	 */
	public function filter_input_post( $name, $filter  = FILTER_DEFAULT, $options = 0) {
        $value = filter_input( INPUT_POST, $name, $filter, $options );
		$value = apply_filters( 'wpcfif_filter_input_post', $name, $value );
		return $value;
    }
	
}
