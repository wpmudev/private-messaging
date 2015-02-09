<?php
/**
 * @author:Hoang Ngo
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
include_once dirname(__DIR__) . '/messaging.php';
mmg();
if (!current_user_can('manage_options'))
    die('Who are you?');
switch_to_blog(13);
include mmg()->plugin_path . 'vendors/faker/autoload.php';

$users = get_users(array(
    'number' => 5
));
//generate conversation each users
$messages_count = 10;
//creating conversation scripts
$send_tos = $users;
$scripts = array();
foreach ($users as $user) {
    $data = array();
    foreach ($send_tos as $send_to) {
        if ($user->ID == $send_to->ID) {
            //next loop
            continue;
        }
        $faker = Faker\Factory::create();
        if (!isset($data[$send_to->ID])) {
            //create first message
            $data[$send_to->ID][] = array(
                'from' => $user->ID,
                'to' => $send_to->ID,
                'subject' => $faker->realText(rand(10, 100)),
                'content' => $faker->realText(rand(200, 600)),
            );
        }
        //generate 10 messages
        for ($i = 0; $i < $messages_count; $i++) {
            $prev = end($data[$send_to->ID]);
            $data[$send_to->ID][] = array(
                'from' => $prev['to'],
                'to' => $prev['from'],
                'subject' => $faker->realText(rand(10, 100)),
                'content' => $faker->realText(rand(200, 600))
            );
        }
    }
    $scripts[$user->ID] = $data;
}
//got the script, now import it
foreach ($scripts as $script) {
    //through each user script
    foreach ($script as $c) {
        //thourgh each coversation
        //create conversation
        //create new conservation
        $conservation = new MM_Conversation_Model();
        $conservation->save();
        $f = $c[0];
        //apply status of this conversation for sender and receive
        MM_Message_Status_Model::model()->status($conservation->id, MM_Message_Status_Model::STATUS_READ, $f['from']);
        //apply status for receive
        MM_Message_Status_Model::model()->status($conservation->id, MM_Message_Status_Model::STATUS_UNREAD, $f['to']);
        foreach ($c as $key => $m) {
            if ($key == 0) {
                $id = MM_Message_Model::send($m['to'], $conservation->id, array(
                    'subject' => $m['subject'],
                    'content' => $m['content'],
                    'send_to' => $m['to'],
                    'send_from' => $m['from']
                ));
                $conservation->update_index($id);
            } else {
                $last = $conservation->get_last_message();
                $id = MM_Message_Model::reply($m['to'], $last->id, $conservation->id, array(
                    'subject' => $m['subject'],
                    'content' => $m['content'],
                    'send_to' => $m['to'],
                    'send_from' => $m['from']
                ));
                $conservation->update_index($id);
            }
        };
    }
}
