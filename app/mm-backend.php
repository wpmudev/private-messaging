<?php

/**
 * Author: Hoang Ngo
 */
class MM_Backend {
	public function __construct() {
		new MMessage_Backend_Controller();
		add_action( 'wp_ajax_mm_create_message_page', array( &$this, 'create_page' ) );
		add_filter( 'user_has_cap', array( &$this, 'update_cap' ), 10, 4 );
		add_filter( 'ajax_query_attachments_args', array( &$this, 'restrict_user' ) );
		add_action( 'wp_ajax_mmg_message_edit', array( &$this, 'edit_user_message' ) );
		add_action( 'wp_ajax_mm_delete_user_message', array( &$this, 'delete_user_message' ) );
		add_action( 'wp_ajax_mm_delete_user_conversation', array( &$this, 'delete_user_conversation' ) );
		add_action( 'wp_ajax_mm_lock_conversation', array( &$this, 'lock_conversation' ) );
		add_action( 'wp_ajax_mm_inject_message', array( &$this, 'mm_inject_message' ) );
	}

	function mm_inject_message() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		$conversation_id = mmg()->post( 'conversation_id' );
		$conversation    = MM_Conversation_Model::model()->find( $conversation_id );
		if ( is_object( $conversation ) ) {
			$model = new MM_Message_Model();
			$model->import( mmg()->post( 'MM_Message_Model' ) );
			$model->conversation_id = $conversation->id;
			$model->send_from       = get_current_user_id();
			//send to = 1 for by pass the validation
			$model->send_to = 1;
			if ( $model->validate() ) {
				$model->save();
				$conversation->update_index( $model->id );
				wp_send_json( array(
					'status' => 'success'
				) );
			} else {
				wp_send_json( array(
					'status' => 'fail',
					'errors' => $model->get_errors()
				) );
			}
		}
	}

	function lock_conversation() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		$model = MM_Conversation_Model::model()->find( mmg()->post( 'id' ) );
		if ( ! is_object( $model ) ) {
			return '';
		}
		$type  = mmg()->post( 'type' );
		$users = explode( ',', $model->user_index );
		$users = array_filter( $users );
		if ( $type == 'lock' ) {
			$model->status = MM_Conversation_Model::LOCK;
			$model->save();
			wp_send_json( array(
				'text' => '<i class="fa fa-unlock"></i> ' . __( 'Unlock', mmg()->domain ),
				'type' => 'unlock'
			) );
		} else {
			$model->status = MM_Conversation_Model::UNLOCK;
			$model->save();
			wp_send_json( array(
				'text' => '<i class="fa fa-lock"></i> ' . __( 'Lock', mmg()->domain ),
				'type' => 'lock'
			) );
		}
	}

	function delete_user_message() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}
		$message_id   = mmg()->post( 'id' );
		$model        = MM_Message_Model::model()->find( $message_id );
		$conversation = MM_Conversation_Model::model()->find( $model->conversation_id );
		if ( is_object( $model ) ) {
			$model->delete();
			$conversation->update_index( $message_id, true );
		}
	}

	function delete_user_conversation() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}
		$cid   = mmg()->post( 'id' );
		$model = MM_Conversation_Model::model()->find( $cid );
		if ( is_object( $model ) ) {
			//delete all messages
			$messages = MM_Message_Model::model()->find_by_attributes( array(
				'conversation_id' => $cid
			) );
			foreach ( $messages as $message ) {
				$message->delete();
			}
			$model->delete();
		}
	}

	function edit_user_message() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}
		$message_id = mmg()->post( 'data[id]' );
		$model      = MM_Message_Model::model()->find( $message_id );
		if ( is_object( $model ) ) {
			$subject = mmg()->post( 'data[subject]' );
			$content = mmg()->post( 'data[content]' );

			$model->subject = trim( $subject );
			$model->content = trim( wp_unslash( $content ) );
			if ( $model->validate() ) {
				$model->save();
				wp_send_json( array(
					'status' => 1,
					'model'  => $model->export()
				) );
			} else {
				wp_send_json( array(
					'status' => 0,
					'errors' => implode( '<br/>', $model->get_errors() )
				) );
			}
		}
	}

	function restrict_user( $args ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		return $args;
	}

	function update_cap( $allcaps, $caps, $args, $user ) {
		if ( in_array( 'upload_files', $caps ) ) {
			if ( ! isset( $allcaps['upload_files'] ) ) {
				$flag = false;
				if ( mmg()->post( 'action' ) == 'query-attachments' ) {
					///just query media belong to someone
					$flag = true;
				} elseif ( mmg()->post( 'action' ) == 'upload-attachment' ) {
					//case upload a file, we only allow when upload via je uploader
					if ( mmg()->post( 'igu_uploading' ) == 1 ) {
						$flag = true;
					}
				}
				if ( $flag == true ) {
					//check
					// var_dump($_POST);die;
					$allowed = mmg()->setting()->allow_attachment;
					if ( ! is_array( $allowed ) ) {
						$allowed = array();
					}
					$allowed = array_filter( $allowed );
					foreach ( $user->roles as $role ) {
						if ( in_array( $role, $allowed ) ) {
							$allcaps['upload_files'] = true;
							break;
						}
					}
				}
			}
		}

		//die;
		return $allcaps;
	}

	function create_page() {
		if ( isset( $_POST['m_type'] ) ) {
			$model = new MM_Setting_Model();
			$model->load();
			switch ( $_POST['m_type'] ) {
				case 'inbox':
					$new_id = wp_insert_post( apply_filters( 'mm_create_inbox_page', array(
						'post_title'     => "Inbox",
						'post_content'   => '[message_inbox]',
						'post_status'    => 'publish',
						'post_type'      => 'page',
						'ping_status'    => 'closed',
						'comment_status' => 'closed'
					) ) );

					$model->inbox_page = $new_id;
					$model->save();
					//update
					echo $new_id;
					break;
			}
		}
		exit;
	}
}