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
	 *
	 * @return WPCFIF
	 */
	public static function get_instance() {
		if ( is_null( self::$current ) ) {
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
				$conditions = explode( ',', trim( $matches[2] ) );
				if ( is_array( $conditions ) && count( $conditions ) >= 3 ) {
					$require_if_settings[ $matches[1] ] = $conditions;
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

		if ( isset( $settings[ $tag->name ] ) && $this->is_required( $settings[ $tag->name ], $tags ) ) {
			
			$err = wpcf7_get_message( 'invalid_required' );
			if (isset($settings[ $tag->name ][3])) {
				$err = $settings[ $tag->name ][3];	
			}
			$own_value = $this->get_post_value( $tag, $tag );
			if (
				( is_string( $own_value ) && '' === $own_value ) ||
				( is_array( $own_value ) && empty( $own_value ) )
			) {
				return $err;
			}
		}

		return '';
	}

	/**
	 * 必須項目かどうか
	 *
	 * @param array $conditions 必須条件定義.
	 * @param array $tags array of WPCF7_FormTag.
	 * @return bool
	 */
	private function is_required( $conditions, $tags ) {

		$name    = (string) $conditions[0];
		$operand = (string) $conditions[1];
		$compare = (string) $conditions[2];

		$target = null;
		foreach ( $tags as $t ) {
			if ( $name === $t->name ) {
				$target = $t;
				break;
			}
		}
		if ( is_null( $target ) ) {
			return false;
		}

		$value = $this->get_post_value( $target );
		if ( is_string( $value ) ) {
			$value = (string) $value;
			switch ( $operand ) {
				case 'is_null':
					if ( is_null( $value ) || '' === $value ) {
						return true;
					}
					break;
				case 'not_null':
					if ( ! is_null( $value ) && '' !== $value ) {
						return true;
					}
					break;
				case 'equal':
					if ( $compare === $value ) {
						return true;
					}
					break;
				case 'not_equal':
					if ( $compare !== $value ) {
						return true;
					}
					break;
				case 'greater_than':
					if ( $compare < $value ) {
						return true;
					}
					break;
				case 'greater_equal':
					if ( $compare <= $value ) {
						return true;
					}
					break;
				case 'less_than':
					if ( $compare > $value ) {
						return true;
					}
					break;
				case 'less_equal':
					if ( $compare >= $value ) {
						return true;
					}
					break;
				case 'in':
					if ( in_array( $value, explode( ' ', $compare ) ) ) {
						return true;
					}
					break;
				case 'not_in':
					if ( ! in_array( $value, explode( ' ', $compare ) ) ) {
						return true;
					}
					break;
			}
		} elseif ( is_array( $value ) ) {
			switch ( $operand ) {
				case 'is_null':
					if ( 0 >= count( $value ) ) {
						return true;
					}
					break;
				case 'not_null':
					if ( 0 < count( $value ) ) {
						return true;
					}
					break;
				case 'equal':
					if ( 1 === count( $value ) && $compare === $value[0] ) {
						return true;
					}
					break;
				case 'not_equal':
					if ( 0 === count( $value ) || 1 < count( $value ) || ! in_array( $compare, $value ) ) {
						return true;
					}
					break;
				case 'greater_than':
					if ( is_string( $value ) && $compare < (string) $value ) {
						return true;
					}
					break;
				case 'in':
					$heystack = explode( ' ', $compare );
					foreach ( $value as $v ) {
						if ( in_array( $v, $heystack ) ) {
							return true;
						}
					}
					break;
				case 'not_in':
					$heystack = explode( ' ', $compare );
					$exist    = false;
					foreach ( $value as $v ) {
						if ( in_array( $v, $heystack ) ) {
							$exist = true;
							break;
						}
					}
					if ( ! $exist ) {
						return true;
					}
					break;
			}
		}

			return false;

	}

	/**
	 * POSTパラメータ取得
	 *
	 * @param WPCF7_FormTag $tag WPCF7_FormTag.
	 * @return string|array ←returnの型が固定されてないのであんまりよくない・・・(TODO)
	 */
	public function get_post_value( $tag ) {

		switch ( $tag->basetype ) {
			case 'text':
				return trim( wp_unslash( strtr( (string) $this->filter_input_post( $tag->name ), "\n", ' ' ) ) );
			case 'select':
				if ( $tag->has_option( 'multiple' ) ) {
					return array_filter(
						(array) $this->filter_input_post( $tag->name, FILTER_DEFAULT, FILTER_FORCE_ARRAY ),
						function( $val ) {
							return '' !== $val;
						}
					);
				} else {
					return trim( wp_unslash( strtr( (string) $this->filter_input_post( $tag->name ), "\n", ' ' ) ) );
				}
				break;
			case 'checkbox':
				return (array) $this->filter_input_post( $tag->name, FILTER_DEFAULT, FILTER_FORCE_ARRAY );
			case 'date':
				return trim( strtr( (string) $this->filter_input_post( $tag->name ), "\n", ' ' ) );
			case 'number':
				return trim( strtr( (string) $this->filter_input_post( $tag->name ), "\n", ' ' ) );
			// case 'acceptance':
			// return ! empty( $this->filter_input_post($tag->name) ) ? 1 : 0 ;
			// case 'captcha':
			// break;
			// case 'quiz':
			// break;

		}

		return;
	}

	/**
	 * Gets a specific external variable by name and optionally filters it.
	 * ユニットテストではPOSTパラメータが渡せないので、モックでPOSTデータ取得メソッドを入れ替えるためにあえてラッピングしたメソッド
	 * ※protectedなのはモックを作るのにprotected以上である必要があるからです
	 *
	 * @param string    $name
	 * @param int       $filter
	 * @param array|int $options
	 * @return mixed
	 */
	public function filter_input_post( $name, $filter = FILTER_DEFAULT, $options = 0 ) {
		$value = filter_input( INPUT_POST, $name, $filter, $options );
		$value = apply_filters( 'wpcfif_filter_input_post', $value, $name );
		return $value;
	}

}
