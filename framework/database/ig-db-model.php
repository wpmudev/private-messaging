<?php

if ( ! class_exists( 'IG_DB_Model' ) ) {

	/**
	 * This class is use for inherit, provide a handy way to work with Database table.
	 * Support CRUD, validation and query
	 *
	 * @author: Hoang Ngo
	 * @package: Database
	 */
	class IG_DB_Model extends IG_Model {
		/**
		 * id of this modal
		 * @var int
		 */
		public $id;

		/**
		 * This is RedBean_ToolBox
		 * @var RedBean_ToolBox
		 */
		private $toolbox;

		/**
		 * Connect to database, for more information about redbean, check http://redbeanphp.com/
		 * @return RedBean_ToolBox
		 */
		private function  _connect() {
			if ( ! is_object( R::$toolbox ) ) {
				R::setup( 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, false );
				R::freeze( true );
				R::setStrictTyping( false );
				//$this->toolbox = RedBean_Setup::kickstart('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, false);
			}

			return R::$toolbox;
		}

		/**
		 * @return bool|IG_DB_Model
		 */
		public function save() {
			$this->before_save();
			if ( $this->exist ) {
				$saved = $this->perform_update();
			} else {
				$saved = $this->perform_insert();
			}

			if ( $saved ) {
				return $this->finish_save( $saved );
			}

			return false;
		}

		/**
		 * @return int|string
		 */
		private function perform_update() {
			$data = $this->export();
			unset( $data['id'] );
			$toolbox = $this->_connect();

			$bean = $toolbox->getRedBean()->load( $this->get_table(), $this->id );
			$bean->import( $data );
			$id = $toolbox->getRedBean()->store( $bean );

			return $id;
		}

		/**
		 * @return int|string
		 */
		private function perform_insert() {
			$data    = $this->export();
			$toolbox = $this->_connect();

			$bean = $toolbox->getRedBean()->dispense( $this->get_table() );
			$bean->import( $data );
			$id = $toolbox->getRedBean()->store( $bean );

			return $id;
		}

		/**
		 * @param $saved
		 *
		 * @return $this
		 */
		private function finish_save( $saved ) {
			//loaded the data
			$model = $this->_find( $saved );
			$this->after_save();
			$this->import( $model->export() );
			$this->exist = true;
			$this->id    = $model->id;

			return $this;
		}

		/**
		 * Addition actions before saving a model, eg: update date create
		 */
		protected function before_save() {

		}

		/**
		 * Addition actions after saving a model, eg another dependency of this model
		 */
		protected function after_save() {

		}

		/**
		 * This is a shorthand to call _find($id)
		 *
		 * @param $id
		 *
		 * @return mixed|void
		 */
		public static function find( $id ) {
			$class = get_called_class();
			$m     = new $class;

			return $m->_find( $id );
		}

		/**
		 * This function will search the model by id. This id is the ID of wp_posts
		 *
		 * @param $id
		 *
		 * @return mixed|void
		 */
		public function _find( $id ) {
			$toolbox = $this->_connect();
			$bean    = $toolbox->getRedBean()->load( $this->get_table(), $id );
			if ( $bean->id ) {
				$data  = $bean->export();
				$model = $this->fetch_model( $data );

				return $model;
			}

			return null;
		}

		/**
		 *
		 * @param string $sql
		 * @param array $data
		 *
		 * @return array
		 */
		public function _all_with_condition( $sql = ' 1 = 1 ', $data = array() ) {
			$toolbox = $this->_connect();
			//$beans = $toolbox->getRedBean()->find($this->get_table(), ' '.$sql.' ', $data);
			$beans  = R::find( $this->get_table(), ' ' . $sql . ' ', $data );
			$models = array();
			foreach ( $beans as $bean ) {
				$models[] = $this->fetch_model( $bean );
			}

			return $models;
		}

		/**
		 * @param string $sql
		 * @param array $data
		 *
		 * @return mixed
		 */
		public static function all_with_condition( $sql = '', $data = array() ) {
			$class = get_called_class();
			$m     = new $class;

			return $m->_all_with_condition( $sql, $data );
		}

		/**
		 * @param $data
		 *
		 * @return mixed
		 */
		private function fetch_model( $data ) {
			$class = get_called_class();
			$model = new $class;
			$model->import( $data );
			$model->exist = true;

			return $model;
		}

		/**
		 * Delete the model
		 */
		function delete() {
			$toolbox = $this->_connect();
			$bean    = $toolbox->getRedBean()->load( $this->get_table(), $this->id );
			$toolbox->getRedBean()->trash( $bean );

		}

		/**
		 * @return string
		 */
		function get_table() {
			global $wpdb;

			return $wpdb->prefix . $this->table;
		}
	}
}