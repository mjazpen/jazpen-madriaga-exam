<?php 

require_once 'sync_youtube_channel.php';

// ONLY ACCEPT POST REQUESTS
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode([
        'status' => 'error',
        'message' => 'Only POST requests are allowed'
    ]);
}

// get all the request data
$data = json_decode(file_get_contents('php://input'), true);

// clean the data
array_map('htmlentities', $data);

try{

    if($data['all_channel']??false){
        echo json_encode(
            [
                'status' => 'success',
                'data' => [
                    'channels'=> get_all_channels()   
                ]
            ]
        );
        return;
    }
    else {

        echo json_encode(
            [
                'status' => 'success',
                'data' => [
                    'info' => load_channel($data['channel_id']),
                    'videos' => load_channel_videos($data['channel_id'], $data['page'])
                ]
            ]
        );
        return;
    }

}
catch(Exception $e){
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
